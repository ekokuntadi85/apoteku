<?php

namespace App\Helpers;

class NumberToWords
{
    private static $ones = [
        '', 'Satu', 'Dua', 'Tiga', 'Empat', 'Lima', 'Enam', 'Tujuh', 'Delapan', 'Sembilan',
        'Sepuluh', 'Sebelas', 'Dua Belas', 'Tiga Belas', 'Empat Belas', 'Lima Belas',
        'Enam Belas', 'Tujuh Belas', 'Delapan Belas', 'Sembilan Belas'
    ];

    private static $tens = [
        '', '', 'Dua Puluh', 'Tiga Puluh', 'Empat Puluh', 'Lima Puluh',
        'Enam Puluh', 'Tujuh Puluh', 'Delapan Puluh', 'Sembilan Puluh'
    ];

    public static function convert($number)
    {
        if ($number == 0) {
            return 'Nol';
        }

        if ($number < 0) {
            return 'Minus ' . self::convert(abs($number));
        }

        $words = '';

        if ($number >= 1000000) {
            $millions = floor($number / 1000000);
            $words .= self::convert($millions) . ' Juta ';
            $number %= 1000000;
        }

        if ($number >= 1000) {
            $thousands = floor($number / 1000);
            if ($thousands == 1) {
                $words .= 'Seribu ';
            } else {
                $words .= self::convert($thousands) . ' Ribu ';
            }
            $number %= 1000;
        }

        if ($number >= 100) {
            $hundreds = floor($number / 100);
            if ($hundreds == 1) {
                $words .= 'Seratus ';
            } else {
                $words .= self::$ones[$hundreds] . ' Ratus ';
            }
            $number %= 100;
        }

        if ($number >= 20) {
            $words .= self::$tens[floor($number / 10)] . ' ';
            $number %= 10;
        }

        if ($number > 0) {
            $words .= self::$ones[$number] . ' ';
        }

        return trim($words);
    }
}
