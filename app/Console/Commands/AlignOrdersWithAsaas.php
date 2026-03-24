<?php

namespace App\Console\Commands;

use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class AlignOrdersWithAsaas extends Command
{
    protected $signature = 'orders:align-with-asaas
        {--test : Não grava no banco}
        {--limit= : Limita quantidade de orders}
        {--order= : Processa apenas um order_id}
        {--apply-charge-fields=1 : 1 atualiza charge_type/charge_date; 0 não}
        {--tolerance=0.01 : Tolerância de diferença}';

    protected $description = 'Ajusta orders para bater com o valor cobrado no Asaas (desconto ou aumento do preço base)';

    private array $paidStatuses = ['RECEIVED', 'CONFIRMED', 'RECEIVED_IN_CASH'];

    public function handle(): int
    {
        $test = (bool) $this->option('test');
        $limit = $this->option('limit') ? (int)$this->option('limit') : null;
        $onlyOrder = $this->option('order') ? (int)$this->option('order') : null;
        $applyChargeFields = ((int)$this->option('apply-charge-fields')) === 1;
        $tolerance = (float) $this->option('tolerance');

        if ($test) $this->warn('MODO TESTE: não gravará no banco.');

        // Orders que possuem financial_asaas (ou seja, têm cobranças Asaas)
        $q = Order::query()
            ->whereExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('financial')
                    ->join('financial_asaas', 'financial_asaas.financial_id', '=', 'financial.id')
                    ->whereColumn('financial.order_id', 'orders.id');
            })
            ->orderBy('id');

        if ($onlyOrder) $q->where('id', $onlyOrder);
        if ($limit) $q->limit($limit);

        $orders = $q->get(['id']);

        $this->info("Orders a processar: {$orders->count()}");

        $bar = $this->output->createProgressBar($orders->count());
        $bar->start();

        foreach ($orders as $o) {
            DB::transaction(function () use ($o, $test, $applyChargeFields, $tolerance) {
                $this->processOneOrder((int)$o->id, $test, $applyChargeFields, $tolerance);
            });

            $bar->advance();
        }

        $bar->finish();
        $this->newLine(2);

        $this->info('Finalizado.');
        return self::SUCCESS;
    }

    private function processOneOrder(int $orderId, bool $test, bool $applyChargeFields, float $tolerance): void
    {
        $order = Order::find($orderId);
        if (!$order) return;

        // 1) Achar cobrança referência
        $lastPaid = DB::table('financial')
            ->join('financial_asaas', 'financial_asaas.financial_id', '=', 'financial.id')
            ->where('financial.order_id', $orderId)
            ->whereIn('financial.status', $this->paidStatuses)
            ->orderByDesc('financial.due_date')
            ->select('financial.*', 'financial_asaas.asaas_payment_id', 'financial.payment_method')
            ->first();

        $lastAny = DB::table('financial')
            ->join('financial_asaas', 'financial_asaas.financial_id', '=', 'financial.id')
            ->where('financial.order_id', $orderId)
            ->orderByDesc('financial.created_at')
            ->select('financial.*', 'financial_asaas.asaas_payment_id', 'financial.payment_method')
            ->first();

        $ref = $lastPaid ?: $lastAny;
        if (!$ref) return;

        $asaasValue = (float) ($ref->value ?? 0);
        if ($asaasValue <= 0) return;

        // 2) Calcular total atual do pedido
        $base = (float) DB::table('order_prices')->where('order_id', $orderId)->value('product_value');

        $depsSum = (float) DB::table('order_aditionals_dependents')
            ->where('order_id', $orderId)
            ->sum('value');

        $titularAddSum = 0.0;
        if (Schema::hasTable('order_aditionals')) {
            $titularAddSum = (float) DB::table('order_aditionals')
                ->where('order_id', $orderId)
                ->sum('value');
        }

        $totalAtual = $base + $depsSum + $titularAddSum;

        // 3) Comparar e ajustar
        $diff = round($asaasValue - $totalAtual, 2); // positivo => Asaas maior
        if (abs($diff) <= $tolerance) {
            // Já bate: zera desconto e opcionalmente atualiza charge fields
            if ($test) return;

            $order->discount_type = null;
            $order->discount_value = null;

            if ($applyChargeFields) {
                $order->charge_date = $ref->due_date ? (int) Carbon::parse($ref->due_date)->day : $order->charge_date;
                $order->charge_type = $this->mapOrderChargeTypeFromFinancial($ref->payment_method);
            }

            $order->save();
            return;
        }

        if ($diff < 0) {
            // Asaas menor => aplicar desconto (positivo)
            $discount = abs($diff);

            if ($test) return;

            $order->discount_type = 'R$';
            $order->discount_value = $discount;

            if ($applyChargeFields) {
                $order->charge_date = $ref->due_date ? (int) Carbon::parse($ref->due_date)->day : $order->charge_date;
                $order->charge_type = $this->mapOrderChargeTypeFromFinancial($ref->payment_method);
            }

            $order->save();
            return;
        }

        // diff > 0 : Asaas maior => aumentar preço base
        // regra: aumentar order_prices.product_value de forma que:
        // baseNovo + depsSum + titularAddSum == asaasValue
        $baseNovo = round($asaasValue - ($depsSum + $titularAddSum), 2);
        if ($baseNovo < 0) $baseNovo = 0;

        if ($test) return;

        // zera desconto (se existir)
        $order->discount_type = null;
        $order->discount_value = null;

        // atualiza order_prices
        DB::table('order_prices')
            ->where('order_id', $orderId)
            ->update([
                'product_value' => $baseNovo,
                'updated_at' => now(),
            ]);

        if ($applyChargeFields) {
            $order->charge_date = $ref->due_date ? (int) Carbon::parse($ref->due_date)->day : $order->charge_date;
            $order->charge_type = $this->mapOrderChargeTypeFromFinancial($ref->payment_method);
        }

        $order->save();
    }

    // orders.charge_type conforme sua regra:
    // boleto/pix => BOLETO
    // cartão => CARTAO
    private function mapOrderChargeTypeFromFinancial(?string $paymentMethod): string
    {
        return match ($paymentMethod) {
            'CREDIT_CARD', 'DEBIT_CARD' => 'CARTAO',
            default => 'BOLETO',
        };
    }
}