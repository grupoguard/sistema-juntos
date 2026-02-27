<?php

namespace App\Helpers;

class MoneyExtenso
{
    public static function brl(float $value): array
    {
        $value = round($value, 2);

        $reais = (int) floor($value);
        $centavos = (int) round(($value - $reais) * 100);

        $reaisTxt = self::numero($reais, true);
        $centTxt  = self::numero($centavos, false);

        if ($reais === 0) $reaisTxt = 'zero real';
        if ($reais > 1)   $reaisTxt = self::numero($reais, true) . ' reais';

        if ($centavos === 0) {
            $extenso = $reaisTxt;
        } else {
            $centPart = ($centavos === 1) ? $centTxt . ' centavo' : $centTxt . ' centavos';
            $extenso = $reaisTxt . ' e ' . $centPart;
        }

        return [
            'number'  => number_format($value, 2, ',', '.'),
            'extenso' => $extenso,
        ];
    }

    // Converte 0..999999999 (suficiente pra mensalidade)
    private static function numero(int $n, bool $feminino = false): string
    {
        $n = max(0, $n);

        if ($n === 0) return 'zero';

        $unidMasc = ['', 'um', 'dois', 'três', 'quatro', 'cinco', 'seis', 'sete', 'oito', 'nove'];
        $unidFem  = ['', 'uma', 'duas', 'três', 'quatro', 'cinco', 'seis', 'sete', 'oito', 'nove'];
        $unid = $feminino ? $unidFem : $unidMasc;

        $dez = ['dez','onze','doze','treze','quatorze','quinze','dezesseis','dezessete','dezoito','dezenove'];
        $dezenas = ['','', 'vinte','trinta','quarenta','cinquenta','sessenta','setenta','oitenta','noventa'];
        $centenasMasc = ['','cento','duzentos','trezentos','quatrocentos','quinhentos','seiscentos','setecentos','oitocentos','novecentos'];
        $centenasFem  = ['','cento','duzentas','trezentas','quatrocentas','quinhentas','seiscentas','setecentas','oitocentas','novecentas'];
        $centenas = $feminino ? $centenasMasc : $centenasFem;

        $partes = [];

        $milhoes = intdiv($n, 1000000);
        $n %= 1000000;

        $milhares = intdiv($n, 1000);
        $n %= 1000;

        $resto = $n;

        if ($milhoes > 0) {
            $txt = self::numero($milhoes, false);
            $partes[] = ($milhoes === 1) ? 'um milhão' : $txt . ' milhões';
        }

        if ($milhares > 0) {
            if ($milhares === 1) {
                $partes[] = 'mil';
            } else {
                $partes[] = self::numero($milhares, false) . ' mil';
            }
        }

        if ($resto > 0) {
            $partes[] = self::ate999($resto, $unid, $dez, $dezenas, $centenas);
        }

        // liga com " e " apenas no final quando fizer sentido
        if (count($partes) === 1) return $partes[0];

        $last = array_pop($partes);
        return implode(', ', $partes) . ' e ' . $last;
    }

    private static function ate999(int $n, array $unid, array $dez, array $dezenas, array $centenas): string
    {
        if ($n === 100) return 'cem';

        $c = intdiv($n, 100);
        $d = intdiv($n % 100, 10);
        $u = $n % 10;

        $out = [];

        if ($c > 0) $out[] = $centenas[$c];

        $du = $n % 100;

        if ($du >= 10 && $du <= 19) {
            $out[] = $dez[$du - 10];
        } else {
            if ($d > 1) $out[] = $dezenas[$d];
            if ($d === 1 && $u === 0) $out[] = 'dez';
            if ($d !== 1 && $u > 0) $out[] = $unid[$u];
        }

        // junta com " e " entre centenas/dezenas/unidades
        return implode(' e ', array_filter($out));
    }
}