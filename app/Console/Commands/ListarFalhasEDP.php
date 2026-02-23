<?php

namespace App\Console\Commands;

use App\Models\FailedReturn;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ListarFalhasEDP extends Command
{
    protected $signature = 'edp:listar-falhas 
                            {--type= : Filtrar por tipo (B, F, UNKNOWN, ERROR)}
                            {--limit=50 : Quantidade de registros}
                            {--export : Exportar para CSV}';
    
    protected $description = 'Lista falhas no processamento de retornos EDP';

    public function handle()
    {
        $query = FailedReturn::unprocessed();

        if ($type = $this->option('type')) {
            $query->byType(strtoupper($type));
        }

        $limit = $this->option('limit');
        $falhas = $query->latest()->limit($limit)->get();

        if ($falhas->isEmpty()) {
            $this->info('✅ Nenhuma falha encontrada!');
            return Command::SUCCESS;
        }

        $this->warn("⚠️  {$falhas->count()} falhas encontradas:");
        $this->newLine();

        // Estatísticas por tipo
        $stats = FailedReturn::unprocessed()
            ->select('record_type', DB::raw('count(*) as total'))
            ->groupBy('record_type')
            ->get();

        $this->info('📊 Estatísticas:');
        foreach ($stats as $stat) {
            $this->line("   Tipo {$stat->record_type}: {$stat->total}");
        }
        $this->newLine();

        // Listar falhas
        $tableData = [];
        foreach ($falhas as $falha) {
            $tableData[] = [
                $falha->id,
                $falha->record_type,
                substr($falha->error_message, 0, 50) . '...',
                substr($falha->line_content, 0, 60) . '...',
                $falha->arquivo_data?->format('d/m/Y') ?? 'N/A',
                $falha->created_at->format('d/m H:i')
            ];
        }

        $this->table(
            ['ID', 'Tipo', 'Erro', 'Linha (preview)', 'Arq. Data', 'Criado'],
            $tableData
        );

        if ($this->option('export')) {
            $this->exportarFalhas($falhas);
        }

        $this->newLine();
        $this->info('💡 Para ver uma falha específica: php artisan edp:ver-falha {id}');
        $this->info('💡 Para exportar: php artisan edp:listar-falhas --export');

        return Command::SUCCESS;
    }

    private function exportarFalhas($falhas)
    {
        $filename = storage_path('app/edp_falhas_' . now()->format('Y-m-d_His') . '.csv');
        $file = fopen($filename, 'w');

        // Header
        fputcsv($file, ['ID', 'Tipo', 'Erro', 'Linha Completa', 'Data Arquivo', 'Criado']);

        // Dados
        foreach ($falhas as $falha) {
            fputcsv($file, [
                $falha->id,
                $falha->record_type,
                $falha->error_message,
                $falha->line_content,
                $falha->arquivo_data?->format('Y-m-d') ?? '',
                $falha->created_at->toDateTimeString()
            ]);
        }

        fclose($file);
        $this->info("✓ Arquivo exportado: {$filename}");
    }
}
