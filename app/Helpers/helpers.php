<?php

use NumberToWords\NumberToWords;

if (!function_exists('number_to_words_es')) {
    function number_to_words_es(float $number): string
    {
        $numberToWords = new NumberToWords();
        $transformer = $numberToWords->getNumberTransformer('es');

        $integerPart = intval($number);
        $decimalPart = round(($number - $integerPart) * 100);

        $words = $transformer->toWords($integerPart);
        $formattedDecimal = str_pad($decimalPart, 2, '0', STR_PAD_LEFT);

        return ucfirst($words) . ' pesos ' . $formattedDecimal . '/100 M.N.';
    }
}
