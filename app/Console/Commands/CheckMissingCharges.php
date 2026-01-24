<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Order;
use App\Models\Financial;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class CheckMissingCharges extends Command
{
    protected $signature = 'financial:check-missing 
                            {--create : Criar registros faltantes como PENDING}
                            {--from= : Data inicial (Y-m-d)}
                            {--to= : Data final (Y-m-d)}';
    
    protected $description = 'Verifica pedidos sem cobrança em determinados meses';

    public function handle()
    {
        $this->info('🔍 Verificando cobranças faltantes...');
        $this->newLine();

        $createMode = $this->option('create');
        $from = $this->option('from') ? Carbon::parse($this->option('from')) : Carbon::now()->subYear();
        $to = $this->option('to') ? Carbon::parse($this->option('to')) : Carbon::now();

        // Buscar todos os pedidos ativos (não cancelados e charge_type != EDP)
        $orders = Order::whereNull('canceled_at')
            ->where(function($q) {
                $q->where('charge_type', '!=', 'EDP')
                  ->orWhereNull('charge_type');
            })
            ->with(['client', 'orderPrice'])
            ->get();

        $this->info("📊 Analisando {$orders->count()} pedidos ativos...");
        $this->newLine();

        $missingCharges = [];
        $progressBar = $this->output->createProgressBar($orders->count());
        $progressBar->start();

        foreach ($orders as $order) {
            $missing = $this->findMissingMonths($order, $from, $to);
            if (!empty($missing)) {
                $missingCharges[] = [
                    'order' => $order,
                    'missing_months' => $missing
                ];
            }
            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        if (empty($missingCharges)) {
            $this->info('✓ Nenhuma cobrança faltante encontrada!');
            return Command::SUCCESS;
        }

        // Exibir relatório
        $totalMissing = array_sum(array_map(fn($item) => count($item['missing_months']), $missingCharges));
        $this->warn("⚠️  {$totalMissing} cobranças faltantes em {count($missingCharges)} pedidos!");
        $this->newLine();

        $this->displayMissingCharges($missingCharges);

        // Criar registros se solicitado
        if ($createMode) {
            if ($this->confirm('Deseja criar os registros faltantes?')) {
                $this->createMissingCharges($missingCharges);
            }
        } else {
            $this->newLine();
            $this->info('💡 Para criar os registros faltantes, execute:');
            $this->line('   php artisan financial:check-missing --create');
        }

        return Command::SUCCESS;
    }

    private function findMissingMonths($order, $from, $to)
    {
        // Data de início do pedido
        $orderStart = Carbon::parse($order->created_at)->startOfMonth();
        
        // Se o pedido foi criado depois do período de análise, ajustar
        if ($orderStart->greaterThan($from)) {
            $from = $orderStart;
        }

        $missing = [];
        $period = CarbonPeriod::create($from, '1 month', $to);

        foreach ($period as $month) {
            // Verificar se existe cobrança neste mês
            $exists = Financial::where('order_id', $order->id)
                ->whereYear('due_date', $month->year)
                ->whereMonth('due_date', $month->month)
                ->exists();

            if (!$exists) {
                $missing[] = $month->format('Y-m');
            }
        }

        return $missing;
    }

    private function displayMissingCharges($missingCharges)
    {
        $tableData = [];
        foreach ($missingCharges as $item) {
            $order = $item['order'];
            $months = implode(', ', $item['missing_months']);
            
            $tableData[] = [
                $order->id,
                $order->client->name,
                $order->client->cpf,
                count($item['missing_months']),
                $months
            ];
        }

        $this->table(
            ['Order ID', 'Cliente', 'CPF', 'Meses Faltantes', 'Períodos'],
            $tableData
        );
    }

    private function createMissingCharges($missingCharges)
    {
        $this->info('📝 Criando registros faltantes...');
        $this->newLine();

        $created = 0;
        $progressBar = $this->output->createProgressBar(
            array_sum(array_map(fn($item) => count($item['missing_months']), $missingCharges))
        );
        $progressBar->start();

        foreach ($missingCharges as $item) {
            $order = $item['order'];
            $orderPrice = $order->orderPrice;

            if (!$orderPrice) {
                $this->warn("\n⚠️  Order {$order->id} não tem preço definido. Pulando...");
                continue;
            }

            foreach ($item['missing_months'] as $monthStr) {
                $month = Carbon::parse($monthStr . '-01');
                
                // Definir data de vencimento (usar charge_date do pedido ou dia 10)
                $dueDay = $order->charge_date ?? 10;
                $dueDate = $month->copy()->day($dueDay);

                Financial::create([
                    'order_id' => $order->id,
                    'value' => $orderPrice->product_value,
                    'due_date' => $dueDate,
                    'payment_method' => 'BOLETO',
                    'status' => 'PENDING',
                    'description' => "Cobrança retroativa - {$month->format('m/Y')}",
                    'created_at' => now(),
                    'updated_at' => now()
                ]);

                $created++;
                $progressBar->advance();
            }
        }

        $progressBar->finish();
        $this->newLine(2);
        $this->info("✓ {$created} registros criados com sucesso!");
    }
}