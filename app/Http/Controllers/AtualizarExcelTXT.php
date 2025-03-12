<?php

namespace App\Http\Controllers;

set_time_limit(0); // Remove limite de execução
ini_set('max_execution_time', 0); // Garante que não há limite no PHP

use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PlanilhaImport;
use App\Exports\NovaPlanilhaExport;
use Illuminate\Support\Carbon;

class AtualizarExcelTXT extends Controller
{

    public function index()
    {
        return view('pages.atualizar-planilha');
    }

    public function upload(Request $request)
    {
        $request->validate([
            'planilha' => 'required|mimes:xlsx,xls,csv',
            'arquivos_txt.*' => 'required|mimes:txt'
        ]);

        // Ordenar arquivos TXT pela data no nome
        $arquivosTxt = $request->file('arquivos_txt');
        usort($arquivosTxt, function ($a, $b) {
            $dataA = $this->extrairDataDoNome($a->getClientOriginalName());
            $dataB = $this->extrairDataDoNome($b->getClientOriginalName());
            return $dataA <=> $dataB;
        });

        // Consolidar informações dos arquivos TXT
        $dadosConsolidados = [];
        foreach ($arquivosTxt as $arquivoTxt) {
            $conteudoTxt = file($arquivoTxt->getRealPath());
            $dadosTxt = $this->processarArquivoTxt($conteudoTxt);

            foreach ($dadosTxt as $instalacao => $dados) {
                // Sobrescreve com informações mais recentes
                $dadosConsolidados[$instalacao] = $dados;
            }
        }

        // Processar a planilha Excel
        $import = new PlanilhaImport();
        Excel::import($import, $request->file('planilha'));
        $dadosPlanilha = $import->rows->map(function ($row) {
            // Remove colunas nulas
            return $row->filter(function ($value) {
                return $value !== null;
            });
        })->filter(function ($row) {
            // Remove linhas que ficaram vazias após o filtro
            return $row->isNotEmpty();
        })->values();
        
        // Criar nova planilha com colunas extras
        $resultadoFinal = [];

        foreach ($dadosPlanilha as $planilha) {
            // Garantir que o código de instalação tenha 9 dígitos
            $instalacao = $this->ajustarInstalacao($planilha['instalacao']);

            // Verifica se a instalação existe nos dados consolidados
            if (isset($dadosConsolidados[$instalacao])) {
                // Verificando se 'dt_evidencia' é numérico
                $dataExcel = is_numeric($planilha['dt_evidencia']) ? $planilha['dt_evidencia'] : 0;
        
                // Convertendo a data do Excel para uma data legível
                $data = Carbon::createFromFormat('Y-m-d H:i:s', '1900-01-01 00:00:00')
                    ->addDays($dataExcel - 2) // Ajuste para a data do Excel
                    ->format('d/m/Y'); // Formato desejado
        
                // Determinando o tipo (COBRANÇA ou EVIDÊNCIA)
                $tipo = isset($dadosConsolidados[$instalacao]['codigo_fina
                nceiro']) ? 'COBRANÇA' : 'EVIDÊNCIA';
        
                // Atualizando o valor da coluna dt_evidencia diretamente
                $planilha['dt_evidencia'] = $data;
        
                // Combinando as informações na ordem desejada:
                $resultadoFinal[] = array_merge(
                    // Colocando primeiro o tipo
                    [
                        'TIPO' => $tipo,
                    ],
                    // Dados originais da planilha (convertido para array)
                    $planilha->toArray(),
                    // Códigos financeiros por último
                    [
                        'codigo_financeiro' => $dadosConsolidados[$instalacao]['codigo_financeiro'] ?? 'N/A',
                        'descricao_financeiro' => $dadosConsolidados[$instalacao]['descricao_financeiro'] ?? 'N/A',
                        'codigo_erro' => $dadosConsolidados[$instalacao]['codigo_erro'] ?? 'N/A',
                        'descricao_erro' => $dadosConsolidados[$instalacao]['descricao_erro'] ?? 'N/A',
                        'codigo_final' => $dadosConsolidados[$instalacao]['codigo_final'] ?? 'N/A',
                        'descricao_final' => $dadosConsolidados[$instalacao]['descricao_final'] ?? 'N/A',
                    ]
                );
            } else {
                // Para instalações não encontradas nos dados consolidados, adiciona com valores padrão
                $resultadoFinal[] = array_merge(
                    // Colocando o tipo "NÃO PROCESSADO"
                    [
                        'TIPO' => 'NÃO PROCESSADO',
                    ],
                    // Dados originais da planilha (convertido para array)
                    $planilha->toArray(),
                    // Códigos financeiros com valores 'N/A'
                    [
                        'codigo_financeiro' => 'N/A',
                        'descricao_financeiro' => 'N/A',
                        'codigo_erro' => 'N/A',
                        'descricao_erro' => 'N/A',
                        'codigo_final' => 'N/A',
                        'descricao_final' => 'N/A',
                    ]
                );
            }
        }

        // Remova a linha que usa Excel::store()
        $novaPlanilha = new NovaPlanilhaExport($resultadoFinal);

       // Em vez de salvar a planilha, exporte diretamente para download
        return Excel::download($novaPlanilha, 'planilha_atualizada.xlsx');
    }

    private function extrairDataDoNome($nomeArquivo)
    {
        // Exemplo: 408_JUNTOS_CLUBE_BEN_20241030_233246_CONV.TXT
        if (preg_match('/BEN_(\d{8})_/', $nomeArquivo, $matches)) {
            return \Carbon\Carbon::createFromFormat('Ymd', $matches[1]);
        }
        return null;
    }

    private function processarArquivoTxt(array $conteudoTxt)
    {
        $dados = [];
        $codigosErro = $this->getCodigosErro();
        $codigosFinais = $this->getCodigosFinais();
        $codigosFinanceiro = $this->getCodigoFinanceiro();

        foreach ($conteudoTxt as $linha) {
            $prefixo = substr($linha, 0, 1);
            $linha = trim($linha); // Remover espaços em branco e quebras de linha no início e no final da string
            
            if ($prefixo === 'B') {
                // Processar linhas que começam com 'B'
                $instalacao = substr($linha, 1, 9);

                $ultimoDigito = substr($linha, -1); // Último dígito
                $penultimoDigito = substr($linha, -2, 1); // Penúltimo dígito
                $antepenultimoDigito = substr($linha, -3, 1); // Antepenúltimo dígito
                
                // Verificar no getCodigosFinais (último dígito)
                $descricaoFinal = $this->getCodigosFinais()[$ultimoDigito] ?? null;
                
                // Verificar no getCodigosErro (penúltimo e antepenúltimo dígitos)
                $codigoErro = $antepenultimoDigito . $penultimoDigito ; // Combinar os dois
                $descricaoErro = $this->getCodigosErro()[$codigoErro] ?? null;
                
                $dados[$instalacao] = [
                    'codigo_erro' => $codigoErro,
                    'descricao_erro' => $descricaoErro ?? 'Código desconhecido',
                    'codigo_final' => $ultimoDigito,
                    'descricao_final' => $descricaoFinal ?? 'Código desconhecido',
                ];
            } elseif ($prefixo === 'F') {
                // Processar linhas que começam com 'F'
                $instalacao = substr($linha, 1, 9);

                // Pegando os dígitos 68 e 69 para código financeiro
                $codigoFinanceiro = substr($linha, 67, 2); // Posição 68 e 69 para financeiro

                $dados[$instalacao] = [
                    'codigo_financeiro' => $codigoFinanceiro,
                    'descricao_financeiro' => $codigosFinanceiro[$codigoFinanceiro] ?? 'Código desconhecido',
                ];
            } else {
                // Ignora linhas que não começam com 'B' ou 'F'
                continue;
            }
        }

        return $dados;
    }


    private function getCodigosErro()
    {
        return [
            '00' => 'Sem anomalias',
            '01' => 'Número de instalação inválido',
            '02' => 'Data inicial inválida',
            '03' => 'Data final inválida',
            '04' => 'Endereço inválido',
            '05' => 'Código de movimento inválido',
            '06' => 'Inconsistência nas datas',
            '07' => 'Classe inválida',
            '08' => 'Código de situação de cobrança inválido',
            '09' => 'Número de parcelas inválido',
            '10' => 'Produto/código valor extra inválido',
            '11' => 'Sinistro sem seguro',
            '12' => 'Valor da parcela não numérica',
            '13' => 'Já têm cadastro ativo',
            '14' => 'Já excluído ou não Ativo',
            '15' => 'Suspensa',
            '16' => 'Desligada',
            '17' => 'Duplicidade Operando/Transferência de Titularidade',
            '18' => 'Atualização de Operando',
        ];
    }

    private function getCodigosFinais()
    {
        return [
            '1' => 'Exclusão automática por alteração cadastral',
            '2' => 'Inclusão automática',
            '3' => 'Inconsistência',
            '4' => 'Processando (Parcelamento)',
            '5' => 'Exclusão automática por revisão de conta',
            '6' => 'Incluído pela EDP',
        ];
    }

    private function getCodigoFinanceiro()
    {
        return [
            '01' => 'Faturamento do serviço + data de vencimento da conta',
            '03' => 'Não faturado + data zerada ',
            '04' => 'Devolução por revisão + data de processamento da revisão',
            '05' => 'Cobrança por revisão + data de processamento da revisão',
            '06' => 'Baixa do serviço + data de pagamento da conta',
            '07' => 'Volta a débito',
        ];
    }

    function ajustarInstalacao($instalacao) {
        // Adiciona zeros à esquerda para garantir 9 dígitos
        return str_pad($instalacao, 9, '0', STR_PAD_LEFT);
    }
}
