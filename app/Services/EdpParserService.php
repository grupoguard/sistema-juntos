<?php

namespace App\Services;

use App\Models\LogRegister;
use App\Models\LogMovement;
use App\Models\FailedReturn;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class EdpParserService
{
    /**
     * Layout do Registro Tipo B (Cadastramento)
     * 
     * Baseado em análise real dos arquivos:
     * B1504303386441101000000000004990---------JC 20251001        RUA SAMAMBAIAS 120
     * 
     * Posição   Tamanho   Campo
     * 001-001   1         B.01 - Código do registro (B)
     * 002-010   9         B.02 - Número da instalação
     * 011-012   2         B.03 - Código de valor extra
     * 013-015   3         B.04 - Código do produto
     * 016-017   2         B.05 - Número de parcelas
     * 018-032   15        B.06 - Valor da parcela
     * 033-041   9         B.07 - Futuro (geralmente ---------)
     * 042-044   3         B.08 - Código do município
     * 045-045   1         ESPAÇO (importante!)
     * 046-053   8         B.09 - Data inicial (AAAAMMDD)
     * 054-061   8         B.10 - Data final (AAAAMMDD)
     * 062-101   40        B.11 - Endereço
     * 102-141   40        B.12 - Nome
     * 142-148   7         B.13 - Futuro
     * 149-150   2         B.14 - Código de anomalias
     * 151-151   1         B.15 - Código do movimento
     * B1503977976440800000000000000000---------SJ                                                                                                                  053
     * B0334112396440801000000000002990---------SJ 20250501        RUA AGUAPEI 30                                    CICERA ANA FELISMINA DE SOUZA                  002
     * B1504303386441101000000000004990---------JC 20250501        RUA SAMAMBAIAS 120                                CAMILA ZULMIRA INACIO RODRIGUES DOS SANT       002
     * B0331408636440801000000000002990---------SJ 20241201        RUA VESTA 288                                     JOSE AFONSO DE OLIVEIRA                        002
     * 
     */

    private $layoutRegistroB = [
        'register_code'       => ['pos' => 0,   'len' => 1],
        'installation_number' => ['pos' => 1,   'len' => 9],
        'extra_value'         => ['pos' => 10,  'len' => 2],
        'product_cod'         => ['pos' => 12,  'len' => 3],
        'number_installment'  => ['pos' => 15,  'len' => 2],
        'value_installment'   => ['pos' => 17,  'len' => 15],
        'future1'             => ['pos' => 32,  'len' => 9],
        'city_code'           => ['pos' => 41,  'len' => 3],
        'start_date'          => ['pos' => 44,  'len' => 8],
        'end_date'            => ['pos' => 52,  'len' => 8],
        'address'             => ['pos' => 60,  'len' => 50],
        'name'                => ['pos' => 110, 'len' => 40],
        'future2'             => ['pos' => 150, 'len' => 7],
        'code_anomaly'        => ['pos' => 157, 'len' => 2],
        'code_move'           => ['pos' => 159, 'len' => 1],
    ];

    /**
     * Layout do Registro Tipo F (Movimento)
     * 
     * Baseado em análise real:
     * F03485022864410-----B05JC10M       202512JC 2025123000000000000399006...
     * F0354438476440801   B08JC05M       202411JC 2024121000000000000299001...
     * 
     * Posição   Tamanho   Campo
     * 001-001   1         F.01 - Código do registro (F)
     * 002-010   9         F.02 - Número da instalação
     * 011-012   2         F.03 - Código valor extra
     * 013-015   3         F.04 - Código do produto
     * 016-020   5         F.05 - Número da parcela (pode ser -----)
     * 021-035   15        F.06 - Roteiro de leitura
     * 036-041   6         F.07 - Data faturamento (AAAAMM)
     * 042-044   3         F.08 - Código município
     * 045-045   1         ESPAÇO (importante!)
     * 046-053   8         F.09 - Data movimento (AAAAMMDD)
     * 054-068   15        F.10 - Valor movimento
     * 069-070   2         F.11 - Código de retorno
     * 071-150   80        F.12 - Futuro
     * 151-151   1         F.13 - Código do movimento
     */
    private $layoutRegistroF = [
        'register_code'       => ['pos' => 0,   'len' => 1],
        'installation_number' => ['pos' => 1,   'len' => 9],
        'extra_value'         => ['pos' => 10,  'len' => 2],
        'product_cod'         => ['pos' => 12,  'len' => 3],
        'installment'         => ['pos' => 15,  'len' => 5],
        'reading_script'      => ['pos' => 20,  'len' => 15],
        'date_invoice'        => ['pos' => 35,  'len' => 6],
        'city_code'           => ['pos' => 41,  'len' => 3],
        'date_movement'       => ['pos' => 44,  'len' => 8],
        'value'               => ['pos' => 52,  'len' => 15],
        'code_return'         => ['pos' => 67,  'len' => 2],
        'future'              => ['pos' => 69,  'len' => 90],
        'code_move'           => ['pos' => 159, 'len' => 1],
    ];

    /**
     * Processa uma linha do arquivo EDP
     */
    public function processarLinha($linha, $arquivoData = null)
    {
         try {
            Log::debug("🔍 Linha BRUTA (antes de qualquer processamento)");
            Log::debug("🔍 Tamanho BRUTO (bytes): " . strlen($linha));

            // Remover quebra de linha se houver
            $linha = rtrim($linha, "\r\n");
            Log::debug("🔍 Após rtrim quebra de linha (bytes): " . strlen($linha));

            $linha = rtrim($linha);
            Log::debug("🔍 Após rtrim espaços (bytes): " . strlen($linha));
            
            // Verificar tamanho mínimo da linha
            if (strlen($linha) < 1) {
                return;
            }

            $tipoRegistro = substr($linha, 0, 1);

            switch ($tipoRegistro) {
                case 'A':
                case 'Z':
                    // Ignorar cabeçalho e rodapé
                    Log::info("Registro {$tipoRegistro} ignorado (cabeçalho/rodapé)");
                    break;

                case 'B':
                    $this->processarRegistroB($linha, $arquivoData);
                    break;

                case 'F':
                    $this->processarRegistroF($linha, $arquivoData);
                    break;

                default:
                    Log::warning("Tipo de registro desconhecido: {$tipoRegistro}");
                    $this->registrarFalha('UNKNOWN', $linha, "Tipo de registro desconhecido: {$tipoRegistro}");
            }

        } catch (\Exception $e) {
            Log::error("Erro ao processar linha: " . $e->getMessage());
            $this->registrarFalha('ERROR', $linha, $e->getMessage());
        }
    }

    /**
     * Processa registro tipo B (Cadastramento)
     */
    private function processarRegistroB($linha, $arquivoData)
    {
        try {
            Log::debug("=== PROCESSANDO REGISTRO B ===");
            Log::debug("Linha original B: " . $linha);
            Log::debug("Tamanho da linha: " . strlen($linha));

            $dados = $this->extrairCampos($linha, $this->layoutRegistroB);

            Log::debug("Dados extraídos ANTES trim:", $dados);

            // Validações básicas
            if (empty($dados['installation_number'])) {
                throw new \Exception("Número de instalação vazio");
            }

            // Converter datas
            $dados['start_date'] = $this->converterData($dados['start_date']);
            $dados['end_date'] = $this->converterData($dados['end_date']);
            $dados['arquivo_data'] = $arquivoData;

            // Limpar espaços
            foreach ($dados as $key => $value) {
                if (is_string($value)) {
                    $dados[$key] = trim($value);
                }
            }

            try {
                $registro = LogRegister::create($dados);
                Log::info("✅ Registro B SALVO! ID: {$registro->id}, Instalação: {$dados['installation_number']}, code_anomaly: {$dados['code_anomaly']}, code_move: {$dados['code_move']}");
            } catch (\Exception $dbError) {
                Log::error("❌ ERRO DATABASE ao salvar registro B: " . $dbError->getMessage());
                Log::error("SQL Error Code: " . ($dbError->getCode() ?? 'N/A'));
                Log::error("Dados que falharam:", $dados);
                throw $dbError; // Re-lança para o catch externo registrar na tabela de falhas
            }

        } catch (\Exception $e) {
            Log::error("Erro ao processar registro B: " . $e->getMessage());
            $this->registrarFalha('B', $linha, $e->getMessage(), $arquivoData);
        }
    }

    /**
     * Processa registro tipo F (Movimento)
     */
    private function processarRegistroF($linha, $arquivoData) 
    {
        try {
            Log::debug("=== PROCESSANDO REGISTRO F ===");
            Log::debug("Linha original F: " . $linha);
            Log::debug("Tamanho da linha: " . strlen($linha));

            $dados = $this->extrairCampos($linha, $this->layoutRegistroF);

            Log::debug("Dados extraídos ANTES trim:", $dados);

            // Validações básicas
            if (empty($dados['installation_number'])) {
                throw new \Exception("Número de instalação vazio");
            }

            $dados['arquivo_data'] = $arquivoData;

            if (empty($dados['code_return'])) {
                $dados['code_return'] = '01';
                Log::debug("⚠️ code_return estava vazio, definido como '01'");
            }

            // Limpar espaços
            foreach ($dados as $key => $value) {
                if (is_string($value)) {
                    $dados[$key] = trim($value);
                }
            }

            try {
                $registro = LogMovement::create($dados);
                Log::info("✅ Registro F SALVO! ID: {$registro->id}, Instalação: {$dados['installation_number']}, code_return: {$dados['code_return']}");
            } catch (\Exception $dbError) {
                Log::error("❌ ERRO DATABASE ao salvar registro F: " . $dbError->getMessage());
                Log::error("SQL Error Code: " . ($dbError->getCode() ?? 'N/A'));
                Log::error("Dados que falharam:", $dados);
                throw $dbError; // Re-lança para o catch externo registrar na tabela de falhas
            }

        } catch (\Exception $e) {
            Log::error("Erro ao processar registro F: " . $e->getMessage());
            $this->registrarFalha('F', $linha, $e->getMessage(), $arquivoData);
        }
    }

    private function extrairCampos($linha, $layout)
    {
        $dados = [];

        foreach ($layout as $campo => $config) {
            $pos = $config['pos'];
            $len = $config['len'];

            // Extrair o valor
            $valor = substr($linha, $pos, $len);

            // Se o campo for vazio/nulo, deixar como null
            $valor = trim($valor);
            $valor = $valor === '' ? null : $this->removerAcentos($valor);

            $dados[$campo] = $valor === '' ? null : $valor;
        }

        // ✅ LOG DE DEBUG para campos críticos quando null
        if (isset($layout['code_anomaly']) && empty($dados['code_anomaly'])) {
            Log::warning("code_anomaly vazio! Linha tem " . strlen($linha) . " caracteres. Últimos 10: '" . substr($linha, -10) . "'");
        }

        return $dados;
    }

    private function converterData($dataStr)
    {
        if (empty($dataStr) || strlen($dataStr) !== 8) {
            return null;
        }

        try {
            $dia = substr($dataStr, 0, 2);
            $mes = substr($dataStr, 2, 2);
            $ano = substr($dataStr, 4, 4);

            return Carbon::createFromFormat('d/m/Y', "{$dia}/{$mes}/{$ano}")->format('Y-m-d');
        } catch (\Exception $e) {
            Log::warning("Data inválida: {$dataStr}");
            return null;
        }
    }

    private function registrarFalha($tipoRegistro, $linhaCompleta, $erro, $arquivoData = null)
    {
        FailedReturn::create([
            'record_type' => $tipoRegistro,
            'line_content' => $linhaCompleta,
            'error_message' => $erro,
            'arquivo_data' => $arquivoData,
            'processed' => false
        ]);
    }

    private function removerAcentos($string)
    {
        if ($string === null) {
            return null;
        }

        // Garante ISO-8859-1 puro e substitui acentos
        $string = iconv('ISO-8859-1', 'ASCII//TRANSLIT//IGNORE', $string);

        // Remove qualquer coisa que não seja ASCII imprimível
        return preg_replace('/[^\x20-\x7E]/', '', $string);
    }
}