<?php

namespace StellarSecurity\CommerceLaravel\Support;

class Redactor
{
    public static function redactArray(array $data): array
    {
        $copy = $data;

        if (isset($copy['shipping']) && is_array($copy['shipping'])) {
            $copy['shipping'] = ['_redacted' => true];
        }

        foreach (['password', 'authorization', 'auth', 'token'] as $key) {
            if (array_key_exists($key, $copy)) {
                $copy[$key] = '[REDACTED]';
            }
        }

        return $copy;
    }

    public static function redactHeaders(array $headers): array
    {
        $copy = $headers;

        foreach ($copy as $k => $v) {
            if (strtolower((string) $k) === 'authorization') {
                $copy[$k] = '[REDACTED]';
            }
        }

        return $copy;
    }
}
