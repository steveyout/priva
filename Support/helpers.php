<?php

if (!function_exists('setenv')) {
    function setenv($key, $value)
    {
        $envFile = app()->environmentFilePath();

        $data = file_get_contents($envFile);

        $data = preg_replace("#^{$key}=.*$#m", "{$key}={$value}", $data);

        file_put_contents($envFile, $data);
    }
}

if (!function_exists('float_number_format')) {
    function float_number_format($number, $dec_point = '.', $thousands_sep = ',')
    {
        if (false === $pos = strpos($number, '.')) {
            return number_format($number, 0, $dec_point, $thousands_sep);
        }

        $integer = substr($number, 0, $pos);
        $decimal = substr($number, $pos + 1);

        return number_format($integer, 0, $dec_point, $thousands_sep) . $dec_point . rtrim($decimal, '.0');
    }
}
