<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\Dependent;
use App\Models\Order;
use App\Models\OrderPrice;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportUniodontoCsv extends Command
{
    protected $signature = 'uniodonto:import
        {--file= : Caminho do CSV (ex: storage/app/uniodonto.csv)}
        {--delimiter= : Delimitador do CSV (se vazio, tenta detectar)}
        {--dry-run : Não grava no banco}
        {--limit-assoc= : Limita a quantidade de Cod. Assoc. processados}
        {--only-assoc= : Processa apenas um Cod. Assoc específico}
        {--encoding= : Força encoding de leitura (ex: ISO-8859-1, Windows-1252, UTF-8)}
    ';

    protected $description = 'Importa planilha CSV da Uniodonto criando/atualizando clients, dependents, orders, order_prices e order_aditionals_dependents com logs e rollback por família';

    // Constantes do seu cenário
    private const DEFAULT_GROUP_ID = 1;
    private const DEFAULT_PRODUCT_ID = 4;
    private const DEFAULT_SELLER_ID = 1;
    private const DEFAULT_CHARGE_TYPE = 'EDP';
    private const DEFAULT_CHARGE_DATE = 33;
    private const DEFAULT_ACCESSION = 0.00;
    private const DEFAULT_ACCESSION_PAYMENT = 'Não cobrada';
    private const DEFAULT_REVIEW_STATUS = 'PENDENTE';

    private const ORDER_PRICE_VALUE = 49.90;
    private const ADITIONAL_ID = 1;
    private const ADITIONAL_VALUE = 23.90;

    // Cache em memória para acelerar lookup por nome titular normalizado
    /** @var array<string, int> */
    private array $clientIdByTitularName = [];
    private bool $dryRun = false;
    private int $fakeId = -1;

    public function handle(): int
    {
        $file = (string) $this->option('file');
        $delimiterOpt = $this->option('delimiter');
        $dryRun = (bool) $this->option('dry-run');
        $limitAssoc = $this->option('limit-assoc') ? (int) $this->option('limit-assoc') : null;
        $onlyAssoc = $this->option('only-assoc') ? (int) $this->option('only-assoc') : null;
        $forcedEncoding = $this->option('encoding') ? (string) $this->option('encoding') : null;

        $this->dryRun = $dryRun;

        if (!$file) {
            $this->error('Você precisa passar --file=CAMINHO_DO_CSV');
            return self::FAILURE;
        }

        if (!file_exists($file)) {
            $this->error("Arquivo não encontrado: {$file}");
            return self::FAILURE;
        }

        if ($dryRun) {
            $this->warn('MODO DRY-RUN ATIVO: não gravará no banco.');
        }

        $this->info("Iniciando importação Uniodonto: {$file}");

        // 1) Ler todas as linhas do CSV em memória (para conseguirmos: primeiro titulares, depois dependentes por Nome Titular)
        [$header, $rows, $delimiter] = $this->readCsvToArray($file, $delimiterOpt, $forcedEncoding);

        if (empty($rows)) {
            $this->warn('Nenhuma linha encontrada no CSV.');
            return self::SUCCESS;
        }

        $this->info('Delimitador utilizado: ' . $this->printableDelimiter($delimiter));
        $this->info('Total de linhas (incluindo titulares e dependentes): ' . count($rows));

        // 2) Agrupar por Cod. Assoc (família) para transacionar e fazer rollback por família
        $groups = $this->groupByAssocCode($rows);

        if ($onlyAssoc !== null) {
            $groups = array_filter($groups, fn($k) => (int)$k === $onlyAssoc, ARRAY_FILTER_USE_KEY);
        }

        if ($limitAssoc !== null) {
            $groups = array_slice($groups, 0, $limitAssoc, true);
        }

        $this->info('Famílias (Cod. Assoc.) a processar: ' . count($groups));

        $bar = $this->output->createProgressBar(count($groups));
        $bar->start();

        foreach ($groups as $assocCode => $familyRows) {
            try {
                if ($dryRun) {
                    $this->processFamily($assocCode, $familyRows, true);
                } else {
                    DB::transaction(function () use ($assocCode, $familyRows) {
                        $this->processFamily($assocCode, $familyRows, false);
                    });
                }
            } catch (\Throwable $e) {
                // rollback automático no transaction
                $this->logImport('error', 'family_failed_rollback', "FALHA NA FAMÍLIA {$assocCode} - DESFAZENDO OPERAÇÃO: {$e->getMessage()}", [
                    'assoc_code' => (int)$assocCode,
                    'exception' => get_class($e),
                    'trace' => $e->getTraceAsString(),
                ]);

                $this->error("Família {$assocCode} falhou e foi desfeita. Motivo: {$e->getMessage()}");
            } finally {
                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine(2);

        $this->info('Importação finalizada.');
        return self::SUCCESS;
    }

    /**
     * Processa uma família (Cod. Assoc.) inteira dentro de uma transação.
     * Regras:
     * 1) Upsert titular -> client
     * 2) Upsert dependentes -> dependents (client_id do titular)
     * 3) Upsert order do titular
     * 4) Upsert order_price do order
     * 5) Upsert order_aditionals_dependents de todos dependentes (aditional fixo)
     * 6) Upsert uniodonto_memberships para titular e para dependentes
     */
    private function processFamily(int|string $assocCode, array $familyRows, bool $dryRun): void
    {
        // 1) Identificar linha(s) de titular nessa família.
        // A planilha pode ter mais de um TITULAR por assoc (não deveria), então seguimos:
        // - Prioriza Grau Parentesco == TITULAR
        // - Se houver mais de um, processa todos (com suas próprias “subfamílias” por Nome Titular)
        $titularRows = array_values(array_filter($familyRows, function ($r) {
            $gp = $this->normalizeRelationshipRaw($r['Grau Parentesco'] ?? '');
            return $gp === 'TITULAR';
        }));

        if (empty($titularRows)) {
            // Se não tiver TITULAR, não dá para linkar dependentes com segurança
            $this->logImport('warning', 'no_titular_found', "Família {$assocCode} sem TITULAR - pulando", [
                'assoc_code' => (int)$assocCode,
            ]);
            return;
        }

        foreach ($titularRows as $titularRow) {
            $titularNameRaw = (string) ($titularRow['Nome Titular'] ?? '');
            $titularKey = $this->normalizeTitularNameKey($titularNameRaw);

            // 2) Upsert do client (titular)
            $client = $this->upsertClientFromRow($titularRow, $dryRun);

            if (!$client) {
                throw new \RuntimeException("Não foi possível criar/atualizar CLIENT do titular '{$titularNameRaw}'");
            }

            $this->clientIdByTitularName[$titularKey] = $client->id;

            // 3) Encontrar dependentes dessa família cujo Nome Titular == Nome do titular atual (normalizado)
            $dependentRows = array_values(array_filter($familyRows, function ($r) use ($titularKey) {
                $gp = $this->normalizeRelationshipRaw($r['Grau Parentesco'] ?? '');
                if ($gp === 'TITULAR') return false;

                $rowTitularKey = $this->normalizeTitularNameKey((string)($r['Nome Titular'] ?? ''));
                return $rowTitularKey === $titularKey;
            }));

            // 4) Upsert dependents
            $dependents = [];
            foreach ($dependentRows as $depRow) {
                $dep = $this->upsertDependentFromRow($client->id, $depRow, $dryRun);
                if (!$dep) {
                    $cpfDep = $this->onlyDigits((string)($depRow['CPF'] ?? ''));
                    throw new \RuntimeException("Não foi possível criar/atualizar DEPENDENT CPF {$cpfDep} do titular '{$client->name}'");
                }
                $dependents[] = $dep;
            }

            // 5) Upsert order do client
            $order = $this->upsertOrderForClient($client, $titularRow, $dryRun);

            if (!$order) {
                throw new \RuntimeException("Não foi possível criar/atualizar ORDER do client '{$client->name}'");
            }

            // 6) Upsert order_prices
            $orderPrice = $this->upsertOrderPrice($order, $dryRun);

            if (!$orderPrice) {
                throw new \RuntimeException("ORDER PRICE do ORDER {$order->id} não foi criado/atualizado, desfazendo operação");
            }

            // 7) Upsert order_aditionals_dependents para cada dependente (aditional fixo)
            $this->syncOrderAditionalDependents($order->id, $dependents, $dryRun);


            // 8) Upsert uniodonto_memberships (titular e dependentes)
            $this->upsertMembershipFromRow($client, null, $titularRow, $dryRun);

            foreach ($dependentRows as $idx => $depRow) {
                $depModel = $dependents[$idx] ?? null;
                if ($depModel) {
                    $this->upsertMembershipFromRow(null, $depModel, $depRow, $dryRun);
                }
            }
        }
    }

    // ============================
    // Upserts principais
    // ============================

    private function upsertClientFromRow(array $row, bool $dryRun): ?Client
    {
        $cpf = $this->onlyDigits((string)($row['CPF'] ?? ''));
        if (strlen($cpf) !== 11) {
            $this->logImport('error', 'client_invalid_cpf', "CPF inválido para CLIENT: '{$cpf}'", [
                'assoc_code' => $this->safeAssoc($row),
                'cpf' => $cpf,
                'row' => $row,
            ]);
            return null;
        }

        $name = $this->cleanString((string)($row['Nome Usuario'] ?? ''));
        $momName = $this->cleanString((string)($row['Nome Mae'] ?? ''));
        $birth = $this->parseDateBr((string)($row['Data Nascimento'] ?? ''));
        $gender = $this->mapGender((string)($row['Sexo'] ?? ''));
        $marital = $this->mapMaritalStatus((string)($row['Estado Civil'] ?? ''));
        $emailRaw = $this->cleanString((string)($row['Email'] ?? ''));
        $email = $emailRaw !== '' ? mb_strtolower($emailRaw) : 'naoinformado@juntosbeneficios.com.br';

        $zipcode = $this->onlyDigits((string)($row['CEP'] ?? ''));
        $address = $this->cleanString((string)($row['Logradouro'] ?? ''));
        $number = $this->cleanString((string)($row['Numero'] ?? ''));
        $neighborhood = $this->cleanString((string)($row['Bairro'] ?? ''));
        $city = $this->cleanString((string)($row['Cidade'] ?? ''));
        $state = strtoupper($this->cleanString((string)($row['UF'] ?? '')));

        // phone: preferência celular, senão telefone
        $dddCel = $this->onlyDigits((string)($row['DDD Celular'] ?? ''));
        $cel = $this->onlyDigits((string)($row['Celular'] ?? ''));
        $dddTel = $this->onlyDigits((string)($row['DDD Telefone'] ?? ''));
        $tel = $this->onlyDigits((string)($row['Telefone'] ?? ''));

        $phone = '';
        if ($cel !== '') {
            $phone = ($dddCel !== '' ? $dddCel : '') . $cel;
        } elseif ($tel !== '') {
            $phone = ($dddTel !== '' ? $dddTel : '') . $tel;
        }

        // Campos obrigatórios: garantir não vazio
        if ($name === '') $name = 'Não informado';
        if ($momName === '') $momName = 'Não informado';
        if (!$birth) $birth = Carbon::create(1900, 1, 1);
        if ($gender === '') $gender = 'feminino';
        if ($marital === '') $marital = 'solteiro';
        if ($zipcode === '') $zipcode = '00000000';
        if ($address === '') $address = 'Não informado';
        if ($number === '') $number = 'S/N';
        if ($neighborhood === '') $neighborhood = 'Não informado';
        if ($city === '') $city = 'Não informado';
        if ($state === '' || strlen($state) !== 2) $state = 'SP';

        $data = [
            'group_id' => self::DEFAULT_GROUP_ID,
            'name' => $name,
            'mom_name' => $momName,
            'date_birth' => $birth->format('Y-m-d'),
            'cpf' => $cpf,
            'gender' => $gender,
            'marital_status' => $marital,
            'phone' => $phone !== '' ? $phone : null,
            'email' => $email,
            'zipcode' => $zipcode,
            'address' => $address,
            'number' => $number,
            'complement' => $this->cleanString((string)($row['Complemento'] ?? '')) ?: null,
            'neighborhood' => $neighborhood,
            'city' => $city,
            'state' => $state,
            'status' => 1,
            'obs' => null,
        ];

        $existing = Client::where('cpf', $cpf)->first();

        if ($dryRun) {
            $this->logImport('info', 'client_upserted_dryrun', ($existing ? "Client {$name} atualizado (dry-run)" : "Client {$name} criado (dry-run)"), [
                'assoc_code' => $this->safeAssoc($row),
                'cpf' => $cpf,
                'client_id' => $existing?->id,
            ]);
            if ($existing) return $existing;

            $fake = new Client($data);
            $fake->id = $this->fakeId(); // ID fake só para simular
            return $fake;
        }

        if ($existing) {
            // não sobrescrever com vazio
            $data = $this->mergeWithoutBlank($existing->toArray(), $data);

            $existing->fill($data);
            $existing->save();

            $this->logImport('info', 'client_updated', "Client {$existing->name} (CPF {$cpf}) atualizado", [
                'assoc_code' => $this->safeAssoc($row),
                'cpf' => $cpf,
                'client_id' => $existing->id,
            ]);
            return $existing;
        }

        $client = Client::create($data);

        $this->logImport('info', 'client_created', "Client {$client->name} (CPF {$cpf}) cadastrado", [
            'assoc_code' => $this->safeAssoc($row),
            'cpf' => $cpf,
            'client_id' => $client->id,
        ]);

        return $client;
    }

    private function upsertDependentFromRow(int $clientId, array $row, bool $dryRun): ?Dependent
    {
        $cpf = $this->onlyDigits((string)($row['CPF'] ?? ''));
        if (strlen($cpf) !== 11) {
            $this->logImport('error', 'dependent_invalid_cpf', "CPF inválido para DEPENDENT: '{$cpf}'", [
                'assoc_code' => $this->safeAssoc($row),
                'cpf' => $cpf,
                'row' => $row,
            ]);
            return null;
        }

        $name = $this->cleanString((string)($row['Nome Usuario'] ?? ''));
        $momName = $this->cleanString((string)($row['Nome Mae'] ?? ''));
        $birth = $this->parseDateBr((string)($row['Data Nascimento'] ?? ''));
        $marital = $this->mapMaritalStatus((string)($row['Estado Civil'] ?? ''));
        $relationship = $this->mapRelationship((string)($row['Grau Parentesco'] ?? ''));

        if ($name === '') $name = 'Não informado';
        if ($momName === '') $momName = 'Não informado';
        if (!$birth) $birth = Carbon::create(1900, 1, 1);
        if ($marital === '') $marital = 'solteiro';
        if ($relationship === '') $relationship = 'nao_informado';

        $data = [
            'client_id' => $clientId,
            'name' => $name,
            'mom_name' => $momName,
            'date_birth' => $birth->format('Y-m-d'),
            'cpf' => $cpf,
            'rg' => null,
            'marital_status' => $marital,
            'relationship' => $relationship,
        ];

        $existing = Dependent::where('cpf', $cpf)->first();

        if ($dryRun) {
            $this->logImport('info', 'dependent_upserted_dryrun', ($existing ? "Dependent {$name} atualizado (dry-run)" : "Dependent {$name} criado (dry-run)"), [
                'assoc_code' => $this->safeAssoc($row),
                'cpf' => $cpf,
                'client_id' => $clientId,
                'dependent_id' => $existing?->id,
            ]);
            return $existing ?: (new Dependent($data));
        }

        if ($existing) {
            $data = $this->mergeWithoutBlank($existing->toArray(), $data);

            // garante vínculo com o titular correto
            $data['client_id'] = $clientId;

            $existing->fill($data);
            $existing->save();

            $this->logImport('info', 'dependent_updated', "Dependent {$existing->name} (CPF {$cpf}) vinculado ao Client {$clientId} atualizado", [
                'assoc_code' => $this->safeAssoc($row),
                'cpf' => $cpf,
                'client_id' => $clientId,
                'dependent_id' => $existing->id,
            ]);
            return $existing;
        }

        $dep = Dependent::create($data);

        $this->logImport('info', 'dependent_created', "Dependent {$dep->name} (CPF {$cpf}) vinculado ao Client {$clientId} cadastrado", [
            'assoc_code' => $this->safeAssoc($row),
            'cpf' => $cpf,
            'client_id' => $clientId,
            'dependent_id' => $dep->id,
        ]);

        return $dep;
    }

    private function upsertOrderForClient(Client $client, array $titularRow, bool $dryRun): ?Order
    {
        $canceledAt = $this->parseDateBr((string)($titularRow['Data Exclusao'] ?? ''));
        $status = $canceledAt ? 'cancelado' : 'ativo';

        $data = [
            'client_id' => $client->id,
            'product_id' => self::DEFAULT_PRODUCT_ID,
            'group_id' => self::DEFAULT_GROUP_ID,
            'seller_id' => self::DEFAULT_SELLER_ID,
            'charge_type' => self::DEFAULT_CHARGE_TYPE,
            'status' => $status,
            'charge_date' => self::DEFAULT_CHARGE_DATE,
            'accession' => self::DEFAULT_ACCESSION,
            'accession_payment' => self::DEFAULT_ACCESSION_PAYMENT,
            'review_status' => self::DEFAULT_REVIEW_STATUS,
            'admin_viewed_at' => null,
            'admin_viewed_by' => null,
            'reviewed_at' => null,
            'reviewed_by' => null,
            'review_notes' => null,
            'canceled_at' => $canceledAt ? $canceledAt->format('Y-m-d H:i:s') : null,
        ];

        $existing = Order::where('client_id', $client->id)
            ->where('product_id', self::DEFAULT_PRODUCT_ID)
            ->first();

        if ($dryRun) {
            $this->logImport('info', 'order_upserted_dryrun', ($existing ? "Order do Client {$client->id} atualizado (dry-run)" : "Order do Client {$client->id} criado (dry-run)"), [
                'assoc_code' => $this->safeAssoc($titularRow),
                'client_id' => $client->id,
                'order_id' => $existing?->id,
            ]);
            return $existing ?: (new Order($data));
        }

        if ($existing) {
            $existing->fill($data);
            $existing->save();

            $this->logImport('info', 'order_updated', "Order do Client {$client->id} atualizado (Order {$existing->id})", [
                'assoc_code' => $this->safeAssoc($titularRow),
                'client_id' => $client->id,
                'order_id' => $existing->id,
            ]);
            return $existing;
        }

        $order = Order::create($data);

        $this->logImport('info', 'order_created', "Order do Client {$client->id} criado (Order {$order->id})", [
            'assoc_code' => $this->safeAssoc($titularRow),
            'client_id' => $client->id,
            'order_id' => $order->id,
        ]);

        return $order;
    }

    private function upsertOrderPrice(Order $order, bool $dryRun): ?OrderPrice
    {
        $data = [
            'order_id' => $order->id,
            'product_id' => self::DEFAULT_PRODUCT_ID,
            'product_value' => self::ORDER_PRICE_VALUE,
        ];

        $existing = OrderPrice::where('order_id', $order->id)
            ->where('product_id', self::DEFAULT_PRODUCT_ID)
            ->first();

        if ($dryRun) {
            $this->logImport('info', 'order_price_upserted_dryrun', ($existing ? "OrderPrice do Order {$order->id} atualizado (dry-run)" : "OrderPrice do Order {$order->id} criado (dry-run)"), [
                'order_id' => $order->id,
            ]);
            return $existing ?: (new OrderPrice($data));
        }

        if ($existing) {
            $existing->fill($data);
            $existing->save();

            $this->logImport('info', 'order_price_updated', "OrderPrice do Order {$order->id} atualizado", [
                'order_id' => $order->id,
            ]);
            return $existing;
        }

        $op = OrderPrice::create($data);

        $this->logImport('info', 'order_price_created', "OrderPrice do Order {$order->id} criado", [
            'order_id' => $order->id,
        ]);

        return $op;
    }

    private function upsertOrderAditionalDependent(int $orderId, int $dependentId, bool $dryRun): bool
    {
        if (!$dryRun) {
                $existsAditional = DB::table('aditionals')->where('id', self::ADITIONAL_ID)->exists();
                if (!$existsAditional) {
                    throw new \RuntimeException("Aditional id " . self::ADITIONAL_ID . " não existe na tabela aditionals. Crie o registro antes de importar.");
                }
            }

        // Atenção: você informou que atualmente não vai usar order_dependents
        // e vai inserir direto em order_aditionals_dependents
        $where = [
            'order_id' => $orderId,
            'dependent_id' => $dependentId,
            'aditional_id' => self::ADITIONAL_ID,
        ];

        $update = [
            'value' => self::ADITIONAL_VALUE,
            'updated_at' => now(),
        ];

        if ($dryRun) {
            $this->logImport('info', 'order_aditional_dependent_upserted_dryrun', "OrderAditionalDependent do Order {$orderId} Dependent {$dependentId} criado/atualizado (dry-run)", [
                'order_id' => $orderId,
                'dependent_id' => $dependentId,
                'aditional_id' => self::ADITIONAL_ID,
            ]);
            return true;
        }

        $exists = DB::table('order_aditionals_dependents')->where($where)->exists();

        if ($exists) {
            DB::table('order_aditionals_dependents')->where($where)->update($update);

            $this->logImport('info', 'order_aditional_dependent_updated', "OrderAditionalDependent do Order {$orderId} Dependent {$dependentId} atualizado", [
                'order_id' => $orderId,
                'dependent_id' => $dependentId,
                'aditional_id' => self::ADITIONAL_ID,
            ]);
            return true;
        }

        DB::table('order_aditionals_dependents')->insert(array_merge($where, [
            'value' => self::ADITIONAL_VALUE,
            'created_at' => now(),
            'updated_at' => now(),
        ]));

        $this->logImport('info', 'order_aditional_dependent_created', "OrderAditionalDependent do Order {$orderId} Dependent {$dependentId} criado", [
            'order_id' => $orderId,
            'dependent_id' => $dependentId,
            'aditional_id' => self::ADITIONAL_ID,
        ]);

        return true;
    }

    private function upsertMembershipFromRow(?Client $client, ?Dependent $dependent, array $row, bool $dryRun): void
    {
        $assocCode = (int) $this->onlyDigits((string)($row['Cod. Assoc.'] ?? '0'));
        $userCode = (int) $this->onlyDigits((string)($row['Cod. Usuario'] ?? '0'));
        $cardCode = $this->cleanString((string)($row['Cod. CartAo'] ?? ''));
        $planCode = (int) $this->onlyDigits((string)($row['Cod. Plano'] ?? '0'));
        $planName = $this->cleanString((string)($row['Plano'] ?? '')) ?: null;
        $relationship = $this->mapRelationship((string)($row['Grau Parentesco'] ?? ''));

        if ($cardCode === '') {
            $this->logImport('warning', 'membership_missing_card', 'Registro sem Cod. CartAo - membership não criado/atualizado', [
                'assoc_code' => $assocCode,
                'row' => $row,
            ]);
            return;
        }

        $ownerType = null;
        $ownerId = null;

        if ($client) {
            $ownerType = Client::class;
            $ownerId = $client->id;
        } elseif ($dependent) {
            $ownerType = Dependent::class;
            $ownerId = $dependent->id;
        } else {
            $this->logImport('warning', 'membership_missing_owner', 'Membership sem owner (nem client nem dependent) - pulando', [
                'assoc_code' => $assocCode,
                'card_code' => $cardCode,
            ]);
            return;
        }

        $payload = [
            'assoc_code' => $assocCode,
            'user_code' => $userCode,
            'card_code' => $cardCode,
            'plan_code' => $planCode,
            'plan_name' => $planName,
            'owner_type' => $ownerType,
            'owner_id' => $ownerId,
            'relationship' => $relationship,
            'updated_at' => now(),
        ];

        if ($dryRun) {
            $this->logImport('info', 'membership_upserted_dryrun', "Membership {$cardCode} criado/atualizado (dry-run)", [
                'assoc_code' => $assocCode,
                'card_code' => $cardCode,
                'owner_type' => $ownerType,
                'owner_id' => $ownerId,
            ]);
            return;
        }

        $exists = DB::table('uniodonto_memberships')->where('card_code', $cardCode)->exists();

        if ($exists) {
            DB::table('uniodonto_memberships')->where('card_code', $cardCode)->update($payload);

            $this->logImport('info', 'membership_updated', "Membership {$cardCode} atualizado", [
                'assoc_code' => $assocCode,
                'card_code' => $cardCode,
                'owner_type' => $ownerType,
                'owner_id' => $ownerId,
            ]);
            return;
        }

        DB::table('uniodonto_memberships')->insert(array_merge($payload, [
            'created_at' => now(),
        ]));

        $this->logImport('info', 'membership_created', "Membership {$cardCode} criado", [
            'assoc_code' => $assocCode,
            'card_code' => $cardCode,
            'owner_type' => $ownerType,
            'owner_id' => $ownerId,
        ]);
    }

    // ============================
    // CSV Reader robusto
    // ============================

    /**
     * Retorna: [headerMap, rows, delimiter]
     * rows: array de linhas associativas com as chaves exatamente como no header.
     */
    private function readCsvToArray(string $path, ?string $delimiterOpt, ?string $forcedEncoding): array
    {
        $handle = fopen($path, 'rb');
        if (!$handle) {
            throw new \RuntimeException("Não foi possível abrir o arquivo: {$path}");
        }

        // lê algumas linhas para detectar delimiter e encoding
        $sampleLines = [];
        $maxSample = 8;

        while (!feof($handle) && count($sampleLines) < $maxSample) {
            $line = fgets($handle);
            if ($line === false) break;
            $sampleLines[] = $line;
        }

        // volta pro começo
        rewind($handle);

        $delimiter = $delimiterOpt ? $this->decodeDelimiter($delimiterOpt) : $this->detectDelimiter($sampleLines);
        $encoding = $forcedEncoding ?: $this->detectEncoding($sampleLines);

        // header
        $rawHeaderLine = fgets($handle);
        if ($rawHeaderLine === false) {
            fclose($handle);
            return [[], [], $delimiter];
        }

        $headerLine = $this->toUtf8($rawHeaderLine, $encoding);
        $header = $this->parseCsvLine($headerLine, $delimiter);

        // Normalizar nomes de header para exatamente o que vamos usar
        $header = array_map(fn($h) => $this->cleanHeader((string)$h), $header);

        // montar rows
        $rows = [];

        while (!feof($handle)) {
            $line = fgets($handle);
            if ($line === false) break;

            $line = $this->toUtf8($line, $encoding);

            // ignora linhas vazias
            if (trim($line) === '') continue;

            $fields = $this->parseCsvLine($line, $delimiter);

            // Se vier coluna a mais ou a menos, tenta ajustar
            if (count($fields) < count($header)) {
                // completa com vazio
                $fields = array_pad($fields, count($header), '');
            } elseif (count($fields) > count($header)) {
                // corta excesso
                $fields = array_slice($fields, 0, count($header));
            }

            $row = array_combine($header, $fields);

            // limpeza de colchetes nos campos que vêm como [123]
            $row = $this->cleanRowBrackets($row);

            $rows[] = $row;
        }

        fclose($handle);

        // Algumas planilhas usam "DDD" repetido; a sua estrutura tem:
        // DDD Telefone / Telefone / DDD Celular / Celular
        // Se o header vier ambiguo, a gente tenta remapear
        $rows = $this->normalizeAmbiguousHeaders($header, $rows);

        return [$header, $rows, $delimiter];
    }

    private function parseCsvLine(string $line, string $delimiter): array
    {
        // str_getcsv lida melhor com aspas e delimitador
        return str_getcsv($line, $delimiter);
    }

    private function detectDelimiter(array $sampleLines): string
    {
        $candidates = [',', ';', "\t", '|'];
        $best = ';';
        $bestCount = 0;

        foreach ($candidates as $cand) {
            $counts = [];
            foreach ($sampleLines as $line) {
                $line = trim($line);
                if ($line === '') continue;
                $counts[] = count(str_getcsv($line, $cand));
            }
            $avg = $counts ? array_sum($counts) / count($counts) : 0;
            if ($avg > $bestCount) {
                $bestCount = $avg;
                $best = $cand;
            }
        }

        return $best;
    }

    private function detectEncoding(array $sampleLines): string
    {
        $joined = implode('', $sampleLines);
        $enc = mb_detect_encoding($joined, ['UTF-8', 'Windows-1252', 'ISO-8859-1'], true);
        return $enc ?: 'UTF-8';
    }

    private function toUtf8(string $text, string $encoding): string
    {
        // remove BOM
        $text = preg_replace('/^\xEF\xBB\xBF/', '', $text);

        if (strtoupper($encoding) === 'UTF-8') {
            return $text;
        }

        $converted = @mb_convert_encoding($text, 'UTF-8', $encoding);
        return $converted !== false ? $converted : $text;
    }

    private function decodeDelimiter(string $d): string
    {
        // permite passar "--delimiter=\t"
        if ($d === '\t') return "\t";
        return $d;
    }

    private function printableDelimiter(string $d): string
    {
        return match ($d) {
            "\t" => '\t',
            default => $d,
        };
    }

    private function cleanHeader(string $h): string
    {
        $h = trim($h);
        $h = preg_replace('/\s+/', ' ', $h);

        // Ajustes comuns de header
        $map = [
            'Cod. Assoc.' => 'Cod. Assoc.',
            'Cod. Usuario' => 'Cod. Usuario',
            'Cod. CartAo' => 'Cod. CartAo',
            'Cod. Cartão' => 'Cod. CartAo',
            'Cod. Cartao' => 'Cod. CartAo',
            'Cod. Plano' => 'Cod. Plano',
            'Nome Titular' => 'Nome Titular',
            'Nome Usuario' => 'Nome Usuario',
            'Data Nascimento' => 'Data Nascimento',
            'Data Inclusao' => 'Data Inclusao',
            'Data Inclusão' => 'Data Inclusao',
            'Data Exclusao' => 'Data Exclusao',
            'Data Exclusão' => 'Data Exclusao',
            'Nome Mae' => 'Nome Mae',
            'Nome Mãe' => 'Nome Mae',
            'Estado Civil' => 'Estado Civil',
            'Sexo' => 'Sexo',
            'Logradouro' => 'Logradouro',
            'Numero' => 'Numero',
            'Número' => 'Numero',
            'Complemento' => 'Complemento',
            'Bairro' => 'Bairro',
            'Cidade' => 'Cidade',
            'UF' => 'UF',
            'CEP' => 'CEP',
            'Telefone' => 'Telefone',
            'Celular' => 'Celular',
            'Grau Parentesco' => 'Grau Parentesco',
            'Email' => 'Email',
        ];

        return $map[$h] ?? $h;
    }

    private function normalizeAmbiguousHeaders(array $header, array $rows): array
    {
        // A sua planilha tem: DDD, Telefone, DDD, Celular
        // Se vier como "DDD" repetido, a array_combine pode ter sobrescrito.
        // Aqui tentamos corrigir criando chaves específicas:
        // "DDD Telefone" e "DDD Celular".
        // Como você colou o header já com "DDD Telefone" e "DDD Celular" na descrição,
        // isso geralmente não vai ser necessário. Mas fica de proteção.

        // se existir "DDD" e não existir "DDD Telefone"
        if (in_array('DDD', $header, true) && !in_array('DDD Telefone', $header, true)) {
            // Tentativa: se a coluna "DDD" aparece duas vezes, str_getcsv retorna duas posições,
            // mas o array_combine teria perdido uma. Como aqui já combinamos, não dá pra recuperar.
            // Então esta função só é útil se o header já veio distinto.
            // Mantida aqui como proteção futura.
        }

        // Garantir chaves de DDD Telefone e DDD Celular existirem (mesmo vazias)
        foreach ($rows as &$r) {
            if (!array_key_exists('DDD Telefone', $r)) $r['DDD Telefone'] = $r['DDD Telefone'] ?? '';
            if (!array_key_exists('DDD Celular', $r)) $r['DDD Celular'] = $r['DDD Celular'] ?? '';
        }
        unset($r);

        return $rows;
    }

    private function cleanRowBrackets(array $row): array
    {
        foreach ($row as $k => $v) {
            if (!is_string($v)) continue;
            $vv = trim($v);

            // remove colchetes [123]
            if (str_starts_with($vv, '[') && str_ends_with($vv, ']')) {
                $vv = trim($vv, '[]');
            }

            // remove espaços duplos
            $vv = preg_replace('/\s+/', ' ', $vv);

            $row[$k] = $vv;
        }
        return $row;
    }

    private function groupByAssocCode(array $rows): array
    {
        $out = [];
        foreach ($rows as $r) {
            $assoc = (int) $this->onlyDigits((string)($r['Cod. Assoc.'] ?? '0'));
            if ($assoc <= 0) $assoc = 0;
            $out[$assoc][] = $r;
        }
        ksort($out);
        return $out;
    }

    // ============================
    // Mapeamentos e helpers
    // ============================

    private function normalizeTitularNameKey(string $name): string
    {
        $name = $this->cleanString($name);
        $name = mb_strtoupper($name);
        $name = preg_replace('/\s+/', ' ', $name);
        return trim($name);
    }

    private function cleanString(string $s): string
    {
        $s = trim($s);
        $s = str_replace("\u{00A0}", ' ', $s); // NBSP
        $s = preg_replace('/\s+/', ' ', $s);
        return trim($s);
    }

    private function onlyDigits(string $s): string
    {
        return preg_replace('/\D+/', '', $s) ?? '';
    }

    private function parseDateBr(string $date): ?Carbon
    {
        $date = $this->cleanString($date);
        if ($date === '') return null;

        // dd/mm/yyyy
        try {
            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $date)) {
                return Carbon::createFromFormat('d/m/Y', $date)->startOfDay();
            }

            // yyyy-mm-dd
            if (preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $date)) {
                return Carbon::createFromFormat('Y-m-d', $date)->startOfDay();
            }

            // fallback parse
            return Carbon::parse($date)->startOfDay();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function mapGender(string $sexo): string
    {
        $sexo = strtoupper($this->cleanString($sexo));
        return match ($sexo) {
            'F' => 'feminino',
            'M' => 'masculino',
            default => '',
        };
    }

    private function mapMaritalStatus(string $status): string
    {
        $status = strtoupper($this->cleanString($status));

        return match ($status) {
            'SOLTEIRO' => 'solteiro',
            'CASADO' => 'casado',
            'DIVORCIADO' => 'divorciado',
            'VIUVO', 'VIÚVO' => 'viuvo',
            'AMASIADO', 'CONVIVENTE' => 'uniao_estavel',
            default => '',
        };
    }

    private function normalizeRelationshipRaw(string $gp): string
    {
        return strtoupper($this->cleanString($gp));
    }

    private function mapRelationship(string $gp): string
    {
        $gp = strtoupper($this->cleanString($gp));

        return match ($gp) {
            'CONJUGE', 'CÔNJUGE', 'CONVIVENTE' => 'conjuge',
            'CUNHADO(A)', 'CUNHADO' => 'cunhado',
            'ENTEADO(A)', 'ENTEADO' => 'enteado',
            'FILHA' => 'filha',
            'FILHO' => 'filho',
            'GENRO' => 'genro',
            'IRMÃO(A)', 'IRMAO(A)', 'IRMÃO', 'IRMAO' => 'irmao',
            'MÂE', 'MAE', 'PAI' => 'mae-pai',
            'NETO(A)', 'NETO' => 'neto',
            'NORA' => 'nora',
            'PADRASTO' => 'padrasto',
            'SOBRINHO(A)', 'SOBRINHO' => 'sobrinho',
            'SOGRO(A)', 'SOGRO' => 'sogro',
            'TIO(A)', 'TIO' => 'tio',
            'TITULAR' => 'titular',
            default => strtolower($gp) !== '' ? strtolower($gp) : '',
        };
    }

    private function mergeWithoutBlank(array $old, array $new): array
    {
        // Não sobrescreve valor antigo com string vazia.
        // Para campos null, se o novo vier null também, mantém.
        foreach ($new as $k => $v) {
            if (is_string($v)) {
                if (trim($v) === '' && isset($old[$k]) && trim((string)$old[$k]) !== '') {
                    $new[$k] = $old[$k];
                }
            }
        }
        return $new;
    }

    private function safeAssoc(array $row): ?int
    {
        $assoc = $this->onlyDigits((string)($row['Cod. Assoc.'] ?? ''));
        return $assoc !== '' ? (int)$assoc : null;
    }

    private function logImport(string $level, string $action, string $message, array $ctx = []): void
    {
        // Console
        if ($level === 'error') {
            $this->error($message);
        } elseif ($level === 'warning') {
            $this->warn($message);
        } else {
            $this->line($message);
        }

        // Laravel log
        Log::{$level}("[UNIODONTO_IMPORT] {$action} - {$message}", $ctx);

        // DB log
        $assoc = $ctx['assoc_code'] ?? null;
        $cpf = $ctx['cpf'] ?? null;
        $clientId = $ctx['client_id'] ?? null;
        $depId = $ctx['dependent_id'] ?? null;
        $orderId = $ctx['order_id'] ?? null;
        $cardCode = $ctx['card_code'] ?? null;

        DB::table('uniodonto_import_logs')->insert([
            'level' => $level,
            'action' => $action,
            'message' => mb_substr($message, 0, 500),
            'assoc_code' => $assoc ? (int)$assoc : null,
            'card_code' => $cardCode ? (string)$cardCode : null,
            'cpf' => $cpf ? (string)$cpf : null,
            'client_id' => $clientId ? (int)$clientId : null,
            'dependent_id' => $depId ? (int)$depId : null,
            'order_id' => $orderId ? (int)$orderId : null,
            'context' => json_encode($ctx),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function fakeId(): int
    {
        return $this->fakeId--;
    }

    private function syncOrderAditionalDependents(int $orderId, array $dependents, bool $dryRun): void
{
    if ($dryRun) {
        $this->logImport('info', 'order_aditionals_sync_dryrun', "Sync adicionais dependentes do Order {$orderId} (dry-run) - limpar e recriar", [
            'order_id' => $orderId,
            'dependents_count' => count($dependents),
        ]);
        return;
    }

    // Apaga tudo do pedido e recria conforme CSV (fonte da verdade)
    DB::table('order_aditionals_dependents')->where('order_id', $orderId)->delete();

    $this->logImport('info', 'order_aditionals_deleted', "Relações de adicionais do Order {$orderId} removidas para recriação", [
        'order_id' => $orderId,
    ]);

    $now = now();

    $rows = [];
    foreach ($dependents as $dep) {
        $rows[] = [
            'order_id' => $orderId,
            'dependent_id' => $dep->id,
            'aditional_id' => self::ADITIONAL_ID,
            'value' => self::ADITIONAL_VALUE,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    if (!empty($rows)) {
            DB::table('order_aditionals_dependents')->insert($rows);
        }

        $this->logImport('info', 'order_aditionals_created', "Relações de adicionais do Order {$orderId} recriadas (" . count($rows) . ")", [
            'order_id' => $orderId,
            'dependents_count' => count($rows),
        ]);
    }
}