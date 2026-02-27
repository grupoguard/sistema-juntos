<?php

namespace App\Console\Commands;

use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DedupeUniodontoOrders extends Command
{
    protected $signature = 'orders:dedupe-uniodonto
        {--dry-run : Não grava no banco}
        {--limit= : Limita quantos clientes com duplicidade processar}
        {--client= : Processa apenas um client_id específico}
        {--product=4 : Product id alvo (default 4)}';

    protected $description = 'Remove pedidos duplicados do produto Uniodonto mantendo o mais antigo e migrando relações/financial';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $limit = $this->option('limit') ? (int) $this->option('limit') : null;
        $clientId = $this->option('client') ? (int) $this->option('client') : null;
        $productId = (int) $this->option('product');

        if ($dryRun) {
            $this->warn('MODO DRY-RUN: nada será alterado.');
        }

        $dupesQuery = Order::query()
            ->select('client_id', 'product_id', DB::raw('COUNT(*) as c'))
            ->where('product_id', $productId)
            ->groupBy('client_id', 'product_id')
            ->having('c', '>', 1)
            ->orderBy('client_id');

        if ($clientId) {
            $dupesQuery->where('client_id', $clientId);
        }

        $dupes = $dupesQuery->get();

        if ($limit) {
            $dupes = $dupes->take($limit);
        }

        $this->info("Grupos com duplicidade encontrados: {$dupes->count()}");

        $bar = $this->output->createProgressBar($dupes->count());
        $bar->start();

        foreach ($dupes as $group) {
            $cid = (int) $group->client_id;

            try {
                if ($dryRun) {
                    $this->processClient($cid, $productId, true);
                } else {
                    DB::transaction(function () use ($cid, $productId) {
                        $this->processClient($cid, $productId, false);
                    });
                }
            } catch (\Throwable $e) {
                $this->error("Falha ao deduplicar client {$cid}: {$e->getMessage()}");
            } finally {
                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine(2);

        $this->info('Deduplicação finalizada.');
        return self::SUCCESS;
    }

    private function processClient(int $clientId, int $productId, bool $dryRun): void
    {
        $orders = Order::query()
            ->where('client_id', $clientId)
            ->where('product_id', $productId)
            ->orderBy('created_at') // mais antigo primeiro
            ->orderBy('id')
            ->get();

        if ($orders->count() <= 1) return;

        $keep = $orders->first();
        $toDelete = $orders->slice(1);

        $this->line("Client {$clientId}: mantendo Order {$keep->id} e removendo " . $toDelete->count() . " duplicado(s)");

        foreach ($toDelete as $dup) {
            // 1) Migrar financial -> order antigo
            $finCount = DB::table('financial')
                ->where('order_id', $dup->id)
                ->count();

            // 2) Migrar adicionais dependentes
            $dupRows = DB::table('order_aditionals_dependents')
                ->where('order_id', $dup->id)
                ->get();

            // 3) Migrar order_price se o keep não tiver
            $keepHasPrice = DB::table('order_prices')->where('order_id', $keep->id)->exists();
            $dupPrice = DB::table('order_prices')->where('order_id', $dup->id)->first();

            if ($dryRun) {
                $this->warn("DRY-RUN: Order dup {$dup->id} -> keep {$keep->id} | financial={$finCount} | aditionals=" . $dupRows->count() . " | copyPrice=" . (($dupPrice && !$keepHasPrice) ? 'yes' : 'no'));
                continue;
            }

            // financial
            if ($finCount > 0) {
                DB::table('financial')->where('order_id', $dup->id)->update([
                    'order_id' => $keep->id,
                    'updated_at' => now(),
                ]);
            }

            // order_aditionals_dependents: inserir no keep evitando duplicar
            foreach ($dupRows as $r) {
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

            // order_prices: se o keep não tiver, copiar do dup
            if (!$keepHasPrice && $dupPrice) {
                DB::table('order_prices')->insert([
                    'order_id' => $keep->id,
                    'product_id' => $dupPrice->product_id,
                    'product_value' => $dupPrice->product_value,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            // limpar dados do dup (relacionamentos) antes de deletar
            DB::table('order_aditionals_dependents')->where('order_id', $dup->id)->delete();
            DB::table('order_prices')->where('order_id', $dup->id)->delete();

            // deletar o order duplicado (o mais novo)
            $dupId = $dup->id;
            $dup->delete();

            // log no order_logs (se existir tabela)
            DB::table('order_logs')->insert([
                'user_id' => null,
                'order_id' => $keep->id,
                'table_ajust' => 'orders_dedupe',
                'obj_antes_alteracao' => json_encode([
                    'kept_order_id' => $keep->id,
                    'deleted_order_id' => $dupId,
                    'financial_moved' => $finCount,
                    'aditionals_moved' => $dupRows->count(),
                    'copied_price' => (!$keepHasPrice && (bool)$dupPrice),
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