<?php

namespace App\Console\Commands;

use App\Models\Client;
use App\Models\Dependent;
use App\Models\Order;
use App\Models\OrderPrice;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportAssociadosCsv extends Command
{
    protected $signature = 'planilha:import-associados
        {--file= : Caminho do CSV (ex: storage/app/associados.csv)}
        {--delimiter= : Delimitador (opcional: ; , \t)}
        {--dry-run : Não grava no banco}
        {--limit-titulares= : Limita quantidade de titulares processados}
        {--only-titular= : Processa apenas um CPF titular específico}
        {--encoding= : Força encoding (UTF-8, Windows-1252, ISO-8859-1)}';

    protected $description = 'Importa planilha (titular/dependente) criando/atualizando clients, dependents e orders com base nas regras definidas';

    private bool $dryRun = false;
    private int $fakeId = -1;

    private const DEFAULT_GROUP_ID = 1;
    private const DEFAULT_SELLER_ID = 1;

    // Regras do produto (code -> id)
    // id 2 - code 409
    // id 3 - code 410
    // id 4 - code 411
    private const PRODUCT_MAP = [
        409 => 2,
        410 => 3,
        411 => 4,
    ];

    // Dependente não cobrado
    private const NOT_CHARGED_ADITIONAL_ID = 3;

    // Fonte do relatório
    private const ISSUE_SOURCE = 'planilha_associados';

    public function handle(): int
    {
        $file = (string)$this->option('file');
        $delimiterOpt = $this->option('delimiter');
        $this->dryRun = (bool)$this->option('dry-run');
        $limitTitulares = $this->option('limit-titulares') ? (int)$this->option('limit-titulares') : null;
        $onlyTitular = $this->option('only-titular') ? $this->onlyDigits((string)$this->option('only-titular')) : null;
        $forcedEncoding = $this->option('encoding') ? (string)$this->option('encoding') : null;

        if (!$file) {
            $this->error('Você precisa passar --file=');
            return self::FAILURE;
        }
        if (!file_exists($file)) {
            $this->error("Arquivo não encontrado: {$file}");
            return self::FAILURE;
        }

        if ($this->dryRun) {
            $this->warn('MODO DRY-RUN: não gravará no banco.');
        }

        [$header, $rows, $delimiter] = $this->readCsvToArray($file, $delimiterOpt, $forcedEncoding);

        if (empty($rows)) {
            $this->warn('Nenhuma linha encontrada.');
            return self::SUCCESS;
        }

        // Agrupar por CPF do titular:
        // - linha TITULAR: CPF/CNPJ Titular vazio => key = CPF
        // - linha DEPENDENTE: CPF/CNPJ Titular preenchido => key = CPF/CNPJ Titular
        $groups = [];
        foreach ($rows as $row) {
            $cpf = $this->onlyDigits((string)($row['CPF'] ?? ''));
            $titularCpf = $this->onlyDigits((string)($row['CPF/CNPJ Titular'] ?? ''));

            $groupKey = $titularCpf !== '' ? $titularCpf : $cpf;
            if ($groupKey === '') {
                $this->addIssue('CLIENTE', $row, 'estava faltando CPF e CPF/CNPJ Titular');
                continue;
            }

            if ($onlyTitular && $groupKey !== $onlyTitular) {
                continue;
            }

            $groups[$groupKey][] = $row;
        }

        ksort($groups);

        if ($limitTitulares) {
            $groups = array_slice($groups, 0, $limitTitulares, true);
        }

        $this->info("Titulares a processar: " . count($groups));
        $bar = $this->output->createProgressBar(count($groups));
        $bar->start();

        foreach ($groups as $titularCpf => $familyRows) {
            try {
                if ($this->dryRun) {
                    $this->processTitularGroup($titularCpf, $familyRows, true);
                } else {
                    DB::transaction(function () use ($titularCpf, $familyRows) {
                        $this->processTitularGroup($titularCpf, $familyRows, false);
                    });
                }
            } catch (\Throwable $e) {
                $this->error("FALHA titular {$titularCpf}: {$e->getMessage()} (rollback aplicado)");
            } finally {
                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('Import finalizado.');
        return self::SUCCESS;
    }

    private function processTitularGroup(string $titularCpf, array $familyRows, bool $dryRun): void
    {
        // 1) identificar linha do titular (CPF == titularCpf e CPF/CNPJ Titular vazio)
        $titularRow = null;
        foreach ($familyRows as $r) {
            $cpf = $this->onlyDigits((string)($r['CPF'] ?? ''));
            $cpfTit = $this->onlyDigits((string)($r['CPF/CNPJ Titular'] ?? ''));
            if ($cpf === $titularCpf && $cpfTit === '') {
                $titularRow = $r;
                break;
            }
        }

        // fallback: pega a primeira linha do grupo (quando planilha vier "estranha")
        if (!$titularRow) {
            $titularRow = $familyRows[0] ?? null;
        }

        if (!$titularRow) {
            $this->addIssue('CLIENTE', ['CPF' => $titularCpf], 'não foi possível identificar linha do titular');
            throw new \RuntimeException("Não foi possível identificar linha do titular");
        }

        // 2) validar campos mínimos do titular (CLIENTE)
        $missing = $this->validateClientRow($titularRow);
        if (!empty($missing)) {
            $this->addIssue('CLIENTE', $titularRow, 'estava faltando ' . implode(', ', $missing));
            throw new \RuntimeException("Titular com dados insuficientes: " . implode(', ', $missing));
        }

        // 3) criar/atualizar client
        $client = $this->upsertClientFromRow($titularRow, $dryRun);
        if (!$client || !$client->id) {
            $this->addIssue('CLIENTE', $titularRow, 'falha ao criar/atualizar no sistema');
            throw new \RuntimeException("Falha ao criar/atualizar client do titular {$titularCpf}");
        }

        // 4) dependentes: linhas onde CPF/CNPJ Titular == titularCpf
        $dependentsWithRow = [];
        foreach ($familyRows as $r) {
            $cpfTit = $this->onlyDigits((string)($r['CPF/CNPJ Titular'] ?? ''));
            if ($cpfTit === '' || $cpfTit !== $titularCpf) {
                continue;
            }

            $missingDep = $this->validateDependentRow($r);
            if (!empty($missingDep)) {
                $this->addIssue('DEPENDENTE', $r, 'estava faltando ' . implode(', ', $missingDep));
                continue; // não aborta o titular por dependente faltante
            }

            $dep = $this->upsertDependentFromRow((int)$client->id, $r, $dryRun);
            if ($dep && $dep->id) {
                $dependentsWithRow[] = [$dep, $r];
            } else {
                $this->addIssue('DEPENDENTE', $r, 'falha ao criar/atualizar no sistema');
            }
        }

        // 5) criar/atualizar order do titular
        $missingOrder = $this->validateOrderRow($titularRow);
        if (!empty($missingOrder)) {
            $this->addIssue('ORDER', $titularRow, 'estava faltando ' . implode(', ', $missingOrder));
            throw new \RuntimeException("Pedido do titular sem dados: " . implode(', ', $missingOrder));
        }

        $order = $this->upsertOrderFromTitularRow($client, $titularRow, $dryRun);
        if (!$order || !$order->id) {
            $this->addIssue('ORDER', $titularRow, 'falha ao criar/atualizar pedido no sistema');
            throw new \RuntimeException("Falha ao criar/atualizar order do client {$client->id}");
        }

        // 6) order_prices (valor do produto SEMPRE da planilha)
        $this->upsertOrderPriceFromTitularRow($order, $titularRow, $dryRun);

        // 7) adicional do titular (mesma coluna Adicional; se preenchido, vai para order_aditionals)
        $this->syncTitularAditionals($order->id, $titularRow, $dryRun);

        // 8) dependentes (sempre criamos registro em order_aditionals_dependents)
        // - se Adicional vazio => aditional_id=3 value=0
        // - se Adicional preenchido => aditional_id=Adicional value=Valor (da linha do dependente)
        $this->syncAditionalsDependents($order->id, $dependentsWithRow, $dryRun);
    }

    // =========================================================
    // UPSERTS
    // =========================================================

    private function upsertClientFromRow(array $row, bool $dryRun): ?Client
    {
        $cpf = $this->onlyDigits((string)($row['CPF'] ?? ''));
        if (strlen($cpf) !== 11) {
            $this->addIssue('CLIENTE', $row, 'CPF inválido');
            throw new \RuntimeException("CPF inválido para client: {$cpf}");
        }

        $email = trim((string)($row['Email'] ?? ''));
        if ($email === '') $email = 'naoinformado@juntosbeneficios.com.br';

        $gender = $this->mapGender((string)($row['Gênero'] ?? $row['Genero'] ?? ''));
        $marital = $this->mapMaritalStatus((string)($row['Estado Civil'] ?? ''));

        $birth = $this->parseDateBr((string)($row['Data de Nascimento'] ?? ''));
        if (!$birth) {
            $this->addIssue('CLIENTE', $row, 'data de nascimento inválida');
            throw new \RuntimeException("Data de nascimento inválida para client CPF {$cpf}");
        }

        $phone = $this->onlyDigits((string)($row['Telefone'] ?? ''));
        $cep = $this->onlyDigits((string)($row['CEP'] ?? ''));

        $state = strtoupper($this->cleanString((string)($row['Estado'] ?? 'SP'))) ?: 'SP';
        if (strlen($state) !== 2) $state = 'SP';

        $data = [
            'group_id' => self::DEFAULT_GROUP_ID,
            'name' => $this->cleanString((string)($row['Nome'] ?? '')) ?: 'Não informado',
            'mom_name' => $this->cleanString((string)($row['Nome da Mãe'] ?? $row['Nome da Mae'] ?? '')) ?: 'Não informado',
            'date_birth' => $birth->format('Y-m-d'),
            'cpf' => $cpf,
            'gender' => $gender ?: 'feminino',
            'marital_status' => $marital ?: 'solteiro',
            'phone' => $phone !== '' ? $phone : null, // pode ser null
            'email' => mb_strtolower($email),
            'zipcode' => $cep !== '' ? $cep : '00000000',
            'address' => $this->cleanString((string)($row['Logradouro'] ?? '')) ?: 'Não informado',
            'number' => $this->cleanString((string)($row['Número'] ?? $row['Numero'] ?? '')) ?: 'S/N',
            'complement' => $this->cleanString((string)($row['Complemento'] ?? '')) ?: null,
            'neighborhood' => $this->cleanString((string)($row['Bairro'] ?? '')) ?: 'Não informado',
            'city' => $this->cleanString((string)($row['Cidade'] ?? '')) ?: 'Não informado',
            'state' => $state,
            'status' => 1,
        ];

        $existing = Client::where('cpf', $cpf)->first();

        if ($dryRun) {
            if ($existing) {
                $this->line("Client {$existing->name} atualizado (dry-run)");
                return $existing;
            }
            $fake = new Client($data);
            $fake->id = $this->fakeId();
            $this->line("Client {$data['name']} criado (dry-run)");
            return $fake;
        }

        if ($existing) {
            $data = $this->mergeWithoutBlank($existing->toArray(), $data);
            $existing->fill($data)->save();
            $this->line("Client {$existing->name} atualizado");
            return $existing;
        }

        $client = Client::create($data);
        $this->line("Client {$client->name} criado");
        return $client;
    }

    private function upsertDependentFromRow(int $clientId, array $row, bool $dryRun): ?Dependent
    {
        $cpf = $this->onlyDigits((string)($row['CPF'] ?? ''));
        if (strlen($cpf) !== 11) {
            $this->addIssue('DEPENDENTE', $row, 'CPF inválido');
            throw new \RuntimeException("CPF inválido para dependente: {$cpf}");
        }

        $birth = $this->parseDateBr((string)($row['Data de Nascimento'] ?? ''));
        if (!$birth) {
            $this->addIssue('DEPENDENTE', $row, 'data de nascimento inválida');
            throw new \RuntimeException("Data de nascimento inválida para dependente CPF {$cpf}");
        }

        $marital = $this->mapMaritalStatus((string)($row['Estado Civil'] ?? '')) ?: 'solteiro';
        $relationship = $this->mapRelationship((string)($row['Grau parentesco'] ?? $row['Grau Parentesco'] ?? ''));

        $data = [
            'client_id' => $clientId,
            'name' => $this->cleanString((string)($row['Nome'] ?? '')) ?: 'Não informado',
            'mom_name' => $this->cleanString((string)($row['Nome da Mãe'] ?? $row['Nome da Mae'] ?? '')) ?: 'Não informado',
            'date_birth' => $birth->format('Y-m-d'),
            'cpf' => $cpf,
            'marital_status' => $marital,
            'relationship' => $relationship ?: 'nao_informado',
        ];

        $existing = Dependent::where('cpf', $cpf)->first();

        if ($dryRun) {
            if ($existing) {
                $this->line("Dependent {$existing->name} atualizado (dry-run)");
                return $existing;
            }
            $fake = new Dependent($data);
            $fake->id = $this->fakeId();
            $this->line("Dependent {$data['name']} criado (dry-run)");
            return $fake;
        }

        if ($existing) {
            $data = $this->mergeWithoutBlank($existing->toArray(), $data);
            $data['client_id'] = $clientId;
            $existing->fill($data)->save();
            $this->line("Dependent {$existing->name} atualizado");
            return $existing;
        }

        $dep = Dependent::create($data);
        $this->line("Dependent {$dep->name} criado");
        return $dep;
    }

    private function upsertOrderFromTitularRow(Client $client, array $row, bool $dryRun): ?Order
    {
        $productCode = (int)$this->onlyDigits((string)($row['Produto'] ?? ''));
        $productId = $this->mapProductCodeToId($productCode);

        if (!$productId) {
            $this->addIssue('ORDER', $row, "produto inválido ({$productCode})");
            throw new \RuntimeException("Produto inválido para titular CPF {$client->cpf}");
        }

        $diaVenc = (int)$this->onlyDigits((string)($row['Dia Vencimento'] ?? ''));
        $chargeDate = $diaVenc > 0 ? $diaVenc : 33;

        $forma = strtoupper(trim((string)($row['Forma de Pagamento'] ?? '')));
        // Regra: forma de pagamento define charge_type para separar EDP x não EDP
        $chargeType = $this->mapOrderChargeType($forma) ?: 'EDP';

        $data = [
            'client_id' => $client->id,
            'product_id' => $productId,
            'group_id' => self::DEFAULT_GROUP_ID,
            'seller_id' => self::DEFAULT_SELLER_ID,
            'charge_type' => $chargeType, // BOLETO/CARTAO/EDP
            'status' => 'ativo',
            'charge_date' => $chargeDate,
            'accession' => 0,
            'accession_payment' => 'Não cobrada',
            'review_status' => 'PENDENTE',
            'admin_viewed_at' => null,
            'admin_viewed_by' => null,
            'reviewed_at' => null,
            'reviewed_by' => null,
            'review_notes' => null,
            'canceled_at' => null,
        ];

        // Regra: 1 pedido por client_id + product_id
        $existing = Order::where('client_id', $client->id)
            ->where('product_id', $productId)
            ->orderBy('created_at')
            ->first();

        if ($dryRun) {
            if ($existing) {
                $this->line("Order do client {$client->id} atualizado (dry-run)");
                return $existing;
            }
            $fake = new Order($data);
            $fake->id = $this->fakeId();
            $this->line("Order do client {$client->id} criado (dry-run)");
            return $fake;
        }

        if ($existing) {
            $existing->fill($data)->save();
            $this->line("Order do client {$client->id} atualizado (Order {$existing->id})");
            return $existing;
        }

        $order = Order::create($data);
        $this->line("Order do client {$client->id} criado (Order {$order->id})");
        return $order;
    }

    private function upsertOrderPriceFromTitularRow(Order $order, array $row, bool $dryRun): void
    {
        $productValue = $this->toDecimal((string)($row['Valor'] ?? ''));

        if ($productValue <= 0) {
            $this->addIssue('ORDER', $row, 'estava faltando PREÇO do produto (Valor)');
            throw new \RuntimeException("Titular sem valor do produto para order {$order->id}");
        }

        $existing = OrderPrice::where('order_id', $order->id)->where('product_id', $order->product_id)->first();

        if ($dryRun) {
            $this->line(($existing ? "OrderPrice atualizado" : "OrderPrice criado") . " (dry-run) Order {$order->id}");
            return;
        }

        if ($existing) {
            $existing->product_value = $productValue;
            $existing->save();
            $this->line("OrderPrice atualizado Order {$order->id}");
            return;
        }

        OrderPrice::create([
            'order_id' => $order->id,
            'product_id' => $order->product_id,
            'product_value' => $productValue,
        ]);

        $this->line("OrderPrice criado Order {$order->id}");
    }

    // =========================================================
    // SYNC ADICIONAIS (TITULAR e DEPENDENTES)
    // =========================================================

    private function syncTitularAditionals(int $orderId, array $titularRow, bool $dryRun): void
    {
        // Segurança: só roda em linha de titular (CPF/CNPJ Titular vazio)
        $cpfTitularRef = $this->onlyDigits((string)($titularRow['CPF/CNPJ Titular'] ?? ''));
        if ($cpfTitularRef !== '') return;

        $rawAd = trim((string)($titularRow['Adicional'] ?? ''));
        $aditionalId = (int)$this->onlyDigits($rawAd);

        if ($dryRun) {
            if ($aditionalId > 0) {
                $value = $this->toDecimal((string)($titularRow['Valor'] ?? ''));
                $this->line("OrderAditional TITULAR (dry-run) order {$orderId} aditional {$aditionalId} value {$value}");
            } else {
                $this->line("OrderAditional TITULAR (dry-run) order {$orderId} sem adicional");
            }
            return;
        }

        // Espelha: se não tiver adicional, remove qualquer registro antigo
        DB::table('order_aditionals')->where('order_id', $orderId)->delete();

        if ($aditionalId <= 0) {
            return;
        }

        // Regra confirmada: valor do adicional do titular vem da coluna Valor da linha do titular
        $value = $this->toDecimal((string)($titularRow['Valor'] ?? ''));
        if ($value <= 0) {
            $this->addIssue('ORDER', $titularRow, "Titular com adicional informado mas sem PREÇO (Valor)");
            return;
        }

        DB::table('order_aditionals')->insert([
            'order_id' => $orderId,
            'aditional_id' => $aditionalId,
            'value' => $value,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->line("OrderAditional TITULAR criado order {$orderId} aditional {$aditionalId} value {$value}");
    }

    /**
     * Regras:
     * - Sempre espelha a planilha: apaga todos os registros do order e recria
     * - Dependente com Adicional vazio => aditional_id=3, value=0.00 (existe mas não é cobrado)
     * - Dependente com Adicional preenchido => aditional_id=Adicional, value=Valor
     */
    private function syncAditionalsDependents(int $orderId, array $dependentsWithRow, bool $dryRun): void
    {
        if ($dryRun) {
            $this->line("Sync adicionais dependentes do Order {$orderId} (dry-run) qty=" . count($dependentsWithRow));
            return;
        }

        DB::table('order_aditionals_dependents')->where('order_id', $orderId)->delete();

        $now = now();
        $rows = [];

        foreach ($dependentsWithRow as [$dep, $row]) {
            // segurança: só dependente
            $cpfTitularRef = $this->onlyDigits((string)($row['CPF/CNPJ Titular'] ?? ''));
            if ($cpfTitularRef === '') continue;

            $rawAd = trim((string)($row['Adicional'] ?? ''));
            $aditionalId = (int)$this->onlyDigits($rawAd);

            if ($aditionalId <= 0) {
                // dependente existe mas não é cobrado
                $aditionalId = self::NOT_CHARGED_ADITIONAL_ID;
                $value = 0.00;
            } else {
                $value = $this->toDecimal((string)($row['Valor'] ?? ''));

                if ($value <= 0) {
                    $this->addIssue('DEPENDENTE', $row, "não foi cadastrado como DEPENDENTE COBRADO porque estava faltando PREÇO (Valor) do adicional");
                    $aditionalId = self::NOT_CHARGED_ADITIONAL_ID;
                    $value = 0.00;
                }
            }

            $rows[] = [
                'order_id' => $orderId,
                'dependent_id' => $dep->id,
                'aditional_id' => $aditionalId,
                'value' => $value,
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        if (!empty($rows)) {
            DB::table('order_aditionals_dependents')->insert($rows);
        }

        $this->line("Adicionais dependentes recriados Order {$orderId}: " . count($rows));
    }

    // =========================================================
    // VALIDAÇÕES / RELATÓRIO
    // =========================================================

    private function validateClientRow(array $row): array
    {
        $missing = [];

        $cpf = $this->onlyDigits((string)($row['CPF'] ?? ''));
        if (strlen($cpf) !== 11) $missing[] = 'CPF';

        if ($this->cleanString((string)($row['Nome'] ?? '')) === '') $missing[] = 'NOME';

        $birth = $this->parseDateBr((string)($row['Data de Nascimento'] ?? ''));
        if (!$birth) $missing[] = 'DATA DE NASCIMENTO';

        $gender = $this->mapGender((string)($row['Gênero'] ?? $row['Genero'] ?? ''));
        if ($gender === '') $missing[] = 'GÊNERO';

        $marital = $this->mapMaritalStatus((string)($row['Estado Civil'] ?? ''));
        if ($marital === '') $missing[] = 'ESTADO CIVIL';

        if ($this->cleanString((string)($row['Logradouro'] ?? '')) === '') $missing[] = 'ENDEREÇO (Logradouro)';
        if ($this->cleanString((string)($row['Número'] ?? $row['Numero'] ?? '')) === '') $missing[] = 'ENDEREÇO (Número)';
        if ($this->cleanString((string)($row['Bairro'] ?? '')) === '') $missing[] = 'ENDEREÇO (Bairro)';
        if ($this->cleanString((string)($row['Cidade'] ?? '')) === '') $missing[] = 'ENDEREÇO (Cidade)';

        $uf = strtoupper($this->cleanString((string)($row['Estado'] ?? '')));
        if (strlen($uf) !== 2) $missing[] = 'ENDEREÇO (Estado/UF)';

        $cep = $this->onlyDigits((string)($row['CEP'] ?? ''));
        if ($cep === '') $missing[] = 'ENDEREÇO (CEP)';

        // Email pode ser default, então não é obrigatório aqui.

        return $missing;
    }

    private function validateDependentRow(array $row): array
    {
        $missing = [];

        $cpf = $this->onlyDigits((string)($row['CPF'] ?? ''));
        if (strlen($cpf) !== 11) $missing[] = 'CPF';

        if ($this->cleanString((string)($row['Nome'] ?? '')) === '') $missing[] = 'NOME';

        $birth = $this->parseDateBr((string)($row['Data de Nascimento'] ?? ''));
        if (!$birth) $missing[] = 'DATA DE NASCIMENTO';

        $rel = $this->cleanString((string)($row['Grau parentesco'] ?? $row['Grau Parentesco'] ?? ''));
        if ($rel === '') $missing[] = 'GRAU PARENTESCO';

        return $missing;
    }

    private function validateOrderRow(array $row): array
    {
        $missing = [];

        $productCode = (int)$this->onlyDigits((string)($row['Produto'] ?? ''));
        if (!$this->mapProductCodeToId($productCode)) $missing[] = 'PRODUTO';

        $value = $this->toDecimal((string)($row['Valor'] ?? ''));
        if ($value <= 0) $missing[] = 'PREÇO (Valor)';

        // dia vencimento pode ser opcional, mas se quiser exigir:
        // $dv = (int)$this->onlyDigits((string)($row['Dia Vencimento'] ?? ''));
        // if ($dv <= 0) $missing[] = 'DIA VENCIMENTO';

        return $missing;
    }

    private function addIssue(string $personType, array $row, string $reason): void
    {
        // Em dry-run você pode querer ver o relatório também; se quiser salvar mesmo em dry-run, remova essa linha.
        if ($this->dryRun) {
            $name = $this->cleanString((string)($row['Nome'] ?? ''));
            $this->warn("[RELATÓRIO] {$name} não foi cadastrado como {$personType} porque {$reason}");
            return;
        }

        $name = $this->cleanString((string)($row['Nome'] ?? ''));
        $cpf = $this->onlyDigits((string)($row['CPF'] ?? ''));

        DB::table('import_issues')->insert([
            'source' => self::ISSUE_SOURCE,
            'person_type' => $personType, // CLIENTE|DEPENDENTE|ORDER
            'name' => $name ?: null,
            'cpf' => $cpf ?: null,
            'reason' => "{$name} não foi cadastrado como {$personType} porque {$reason}",
            'row' => json_encode($row),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    // =========================================================
    // HELPERS
    // =========================================================

    private function mapProductCodeToId(int $code): ?int
    {
        return self::PRODUCT_MAP[$code] ?? null;
    }

    private function mapGender(string $g): string
    {
        $g = strtoupper($this->cleanString($g));
        return match ($g) {
            'F' => 'feminino',
            'M' => 'masculino',
            default => '',
        };
    }

    private function mapMaritalStatus(string $status): string
    {
        $status = strtoupper($this->cleanString($status));
        return match ($status) {
            'SOLTEIRO', 'SOLTEIRA' => 'solteiro',
            'CASADO', 'CASADA' => 'casado',
            'DIVORCIADO', 'DIVORCIADA' => 'divorciado',
            'VIUVO', 'VIÚVO', 'VIUVA', 'VIÚVA' => 'viuvo',
            'AMASIADO', 'CONVIVENTE', 'UNIAO ESTAVEL', 'UNIÃO ESTÁVEL' => 'uniao_estavel',
            default => '',
        };
    }

    private function mapRelationship(string $gp): string
    {
        $gp = strtoupper($this->cleanString($gp));
        return match ($gp) {
            'MÃE', 'MAE' => 'mae-pai',
            'PAI' => 'mae-pai',
            'FILHO' => 'filho',
            'FILHA' => 'filha',
            'CONJUGE', 'CÔNJUGE', 'CONVIVENTE' => 'conjuge',
            default => strtolower($gp),
        };
    }

    private function mapOrderChargeType(string $forma): ?string
    {
        $forma = strtoupper($this->cleanString($forma));
        return match ($forma) {
            'BOLETO', 'PIX' => 'BOLETO',
            'CARTAO', 'CARTÃO', 'CREDIT_CARD', 'DEBIT_CARD' => 'CARTAO',
            default => null,
        };
    }

    private function toDecimal(string $value): float
    {
        $v = trim($value);
        if ($v === '') return 0.0;
        $v = str_replace(['R$', ' '], '', $v);
        $v = str_replace('.', '', $v); // remove milhar
        $v = str_replace(',', '.', $v);
        if (!is_numeric($v)) return 0.0;
        return (float)$v;
    }

    private function fakeId(): int
    {
        return $this->fakeId--;
    }

    private function cleanString(string $s): string
    {
        $s = trim($s);
        $s = str_replace("\u{00A0}", ' ', $s);
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

        try {
            if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $date)) {
                return Carbon::createFromFormat('d/m/Y', $date)->startOfDay();
            }
            if (preg_match('/^\d{4}\-\d{2}\-\d{2}$/', $date)) {
                return Carbon::createFromFormat('Y-m-d', $date)->startOfDay();
            }
            return Carbon::parse($date)->startOfDay();
        } catch (\Throwable) {
            return null;
        }
    }

    private function mergeWithoutBlank(array $old, array $new): array
    {
        foreach ($new as $k => $v) {
            if (is_string($v) && trim($v) === '' && isset($old[$k]) && trim((string)$old[$k]) !== '') {
                $new[$k] = $old[$k];
            }
        }
        return $new;
    }

    private function readCsvToArray(string $path, ?string $delimiterOpt, ?string $forcedEncoding): array
    {
        $handle = fopen($path, 'rb');
        if (!$handle) throw new \RuntimeException("Não foi possível abrir {$path}");

        $sample = [];
        for ($i = 0; $i < 8 && !feof($handle); $i++) {
            $line = fgets($handle);
            if ($line === false) break;
            $sample[] = $line;
        }
        rewind($handle);

        $delimiter = $delimiterOpt ? ($delimiterOpt === '\t' ? "\t" : $delimiterOpt) : $this->detectDelimiter($sample);
        $encoding = $forcedEncoding ?: $this->detectEncoding($sample);

        $headerLine = fgets($handle);
        if ($headerLine === false) return [[], [], $delimiter];

        $headerLine = $this->toUtf8($headerLine, $encoding);
        $header = array_map(fn($h) => trim((string)$h), str_getcsv($headerLine, $delimiter));

        $rows = [];
        while (!feof($handle)) {
            $line = fgets($handle);
            if ($line === false) break;
            if (trim($line) === '') continue;

            $line = $this->toUtf8($line, $encoding);
            $fields = str_getcsv($line, $delimiter);

            if (count($fields) < count($header)) $fields = array_pad($fields, count($header), '');
            if (count($fields) > count($header)) $fields = array_slice($fields, 0, count($header));

            $row = array_combine($header, $fields);
            $rows[] = $row;
        }

        fclose($handle);
        return [$header, $rows, $delimiter];
    }

    private function detectDelimiter(array $lines): string
    {
        $candidates = [',', ';', "\t", '|'];
        $best = ';';
        $bestAvg = 0;

        foreach ($candidates as $cand) {
            $counts = [];
            foreach ($lines as $l) {
                $l = trim($l);
                if ($l === '') continue;
                $counts[] = count(str_getcsv($l, $cand));
            }
            $avg = $counts ? array_sum($counts) / count($counts) : 0;
            if ($avg > $bestAvg) {
                $bestAvg = $avg;
                $best = $cand;
            }
        }

        return $best;
    }

    private function detectEncoding(array $lines): string
    {
        $joined = implode('', $lines);
        $enc = mb_detect_encoding($joined, ['UTF-8', 'Windows-1252', 'ISO-8859-1'], true);
        return $enc ?: 'UTF-8';
    }

    private function toUtf8(string $text, string $encoding): string
    {
        $text = preg_replace('/^\xEF\xBB\xBF/', '', $text);
        if (strtoupper($encoding) === 'UTF-8') return $text;
        $converted = @mb_convert_encoding($text, 'UTF-8', $encoding);
        return $converted !== false ? $converted : $text;
    }
}