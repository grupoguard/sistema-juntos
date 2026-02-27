<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DedupeUniodontoOrders extends Command
{
    protected $signature = 'orders:dedupe-by-client
        {--dry-run : Não grava no banco}
        {--limit= : Limita quantos clients processar}
        {--only-client= : Processa apenas um client_id}
        {--only-product= : Processa apenas um product_id específico (recomendado em produção)}';

    protected $description = 'Deduplica orders por client_id mantendo o mais antigo e migrando relações/financial antes de remover o(s) mais novo(s)';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;
        $onlyClient = $this->option('only-client') ? (int) $this->option('only-client') : null;
        $onlyProduct = $this->option('only-product') ? (int) $this->option('only-product') : null;

        if ($dryRun) $this->warn('MODO DRY-RUN: nada será alterado.');

        $query = Order::query()
            ->select('client_id', DB::raw('COUNT(*) as c'))
            ->groupBy('client_id')
            ->having('c', '>', 1)
            ->orderBy('client_id');

        if ($onlyClient) {
            $query->where('client_id', $onlyClient);
        }

        if ($onlyProduct) {
            $query->where('product_id', $onlyProduct);
        }

        $groups = $query->get();

        if ($limit) $groups = $groups->take($limit);

        $this->info("Clients com duplicidade de orders encontrados: {$groups->count()}");

        $bar = $this->output->createProgressBar($groups->count());
        $bar->start();

        foreach ($groups as $g) {
            $clientId = (int) $g->client_id;

            try {
                if ($dryRun) {
                    $this->processClient($clientId, $onlyProduct, true);
                } else {
                    DB::transaction(function () use ($clientId, $onlyProduct) {
                        $this->processClient($clientId, $onlyProduct, false);
                    });
                }
            } catch (\Throwable $e) {
                $this->error("Falha ao deduplicar client {$clientId}: {$e->getMessage()}");
            } finally {
                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('Deduplicação finalizada.');

        return self::SUCCESS;
    }

    private function processClient(int $clientId, ?int $onlyProduct, bool $dryRun): void
    {
        $orders = Order::query()
            ->where('client_id', $clientId)
            ->when($onlyProduct, fn($q) => $q->where('product_id', $onlyProduct))
            ->orderBy('created_at') // mais antigo primeiro
            ->orderBy('id')
            ->get();

        if ($orders->count() <= 1) return;

        $keep = $orders->first();
        $toDelete = $orders->slice(1);

        $this->line("Client {$clientId}: mantendo Order {$keep->id} e removendo {$toDelete->count()} mais novo(s)");

        foreach ($toDelete as $dup) {

            $finCount = DB::table('financial')->where('order_id', $dup->id)->count();
            $dupAditionals = DB::table('order_aditionals_dependents')->where('order_id', $dup->id)->get();

            $keepHasPrice = DB::table('order_prices')->where('order_id', $keep->id)->exists();
            $dupPrice = DB::table('order_prices')->where('order_id', $dup->id)->first();

            if ($dryRun) {
                $this->warn("DRY-RUN: dup {$dup->id} -> keep {$keep->id} | financial={$finCount} | aditionals={$dupAditionals->count()} | copyPrice=" . (($dupPrice && !$keepHasPrice) ? 'yes' : 'no'));
                continue;
            }

            // 1) migrar financial
            if ($finCount > 0) {
                DB::table('financial')
                    ->where('order_id', $dup->id)
                    ->update(['order_id' => $keep->id, 'updated_at' => now()]);
            }

            // 2) mesclar adicionais dependentes (evita duplicar)
            foreach ($dupAditionals as $r) {
                $exists = DB::table('order_aditionals_dependents')
                    ->where('order_id', $keep->id)
                    ->where('dependent_id', $r->dependent_id)
                    ->where('aditional_id', $r->aditional_id)
                    ->exists();

                if (!$exists) {
                    DB::table('order_aditionals_dependents')->insert([
                        'order_id' => $keep->id,
                        'dependent_id' => $r->dependent_id,
                        'aditional_id' => $r->aditional_id,
                        'value' => $r->value,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            // 3) copiar order_price se necessário
            if (!$keepHasPrice && $dupPrice) {
                DB::table('order_prices')->insert([
                    'order_id' => $keep->id,
                    'product_id' => $dupPrice->product_id,
                    'product_value' => $dupPrice->product_value,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // 4) limpar relações do dup e deletar dup
            DB::table('order_aditionals_dependents')->where('order_id', $dup->id)->delete();
            DB::table('order_prices')->where('order_id', $dup->id)->delete();

            $dupId = $dup->id;
            $dup->delete();

            // 5) log
            DB::table('order_logs')->insert([
                'user_id' => null,
                'order_id' => $keep->id,
                'table_ajust' => 'orders_dedupe_by_client',
                'obj_antes_alteracao' => json_encode([
                    'kept_order_id' => $keep->id,
                    'deleted_order_id' => $dupId,
                    'financial_moved' => $finCount,
                    'aditionals_moved' => $dupAditionals->count(),
                    'copied_price' => (!$keepHasPrice && (bool) $dupPrice),
                ]),
                'obj_depois_alteracao' => json_encode([
                    'kept_order_id' => $keep->id,
                ]),
                'order_status' => $keep->status,
                'created_at' => now(),
            ]);

            $this->info("OK: deletado Order {$dupId}, mantido {$keep->id} (financial movidos: {$finCount})");
        }
    }
}