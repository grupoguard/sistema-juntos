<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Financial;
use App\Models\OrderPrice;
use Illuminate\Support\Facades\DB;

class ListFinancialMismatches extends Command
{
    protected $signature = 'financial:list-mismatches 
                            {--export : Exportar para CSV}';
    
    protected $description = 'Lista todas as cobranças com divergência de valor';

    public function handle()
    {
        $this->info('🔍 Buscando divergências de preço...');
        $this->newLine();

        $mismatches = Financial::select('financial.*', 'orders.id as order_id', 'clients.name as client_name', 
                                       'clients.cpf', 'order_prices.product_value')
            ->join('orders', 'financial.order_id', '=', 'orders.id')
            ->join('clients', 'orders.client_id', '=', 'clients.id')
            ->leftJoin('order_prices', 'orders.id', '=', 'order_prices.order_id')
            ->whereRaw('ABS(financial.value - order_prices.product_value) > 0.01')
            ->get();

        if ($mismatches->isEmpty()) {
            $this->info('✓ Nenhuma divergência encontrada!');
            return Command::SUCCESS;
        }

        $this->warn("⚠️  {$mismatches->count()} divergências encontradas:");
        $this->newLine();

        $tableData = [];
        foreach ($mismatches as $item) {
            $difference = $item->value - $item->product_value;
            $tableData[] = [
                $item->order_id,
                $item->client_name,
                $item->cpf,
                'R$ ' . number_format($item->product_value, 2, ',', '.'),
                'R$ ' . number_format($item->value, 2, ',', '.'),
                'R$ ' . number_format(abs($difference), 2, ',', '.'),
                $item->asaas_payment_id
            ];
        }

        $this->table(
            ['Order ID', 'Cliente', 'CPF', 'Valor Esperado', 'Valor Cobrado', 'Diferença', 'Asaas ID'],
            $tableData
        );

        if ($this->option('export')) {
            $this->exportToCsv($mismatches);
        }

        return Command::SUCCESS;
    }

    private function exportToCsv($mismatches)
    {
        $filename = storage_path('app/financial_mismatches_' . now()->format('Y-m-d_His') . '.csv');
        $file = fopen($filename, 'w');

        // Header
        fputcsv($file, ['Order ID', 'Cliente', 'CPF', 'Valor Esperado', 'Valor Cobrado', 'Diferença', 'Asaas Payment ID']);

        // Dados
        foreach ($mismatches as $item) {
            $difference = $item->value - $item->product_value;
            fputcsv($file, [
                $item->order_id,
                $item->client_name,
                $item->cpf,
                $item->product_value,
                $item->value,
                abs($difference),
                $item->asaas_payment_id
            ]);
        }

        fclose($file);
        $this->info("✓ Arquivo exportado: {$filename}");
    }
}