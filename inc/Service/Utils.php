<?php


namespace GHOSTLABS\UNDERCARD\Service;

if ( ! defined( 'ABSPATH' ) ) exit;

class Utils
{
    
    public function setPayload(mixed $data): array
    {
        $payload = is_string($data) ? json_decode(stripslashes($data), true) : $data;

        if (!is_array($payload)) {
            return [];
        }

        return $this->arrayMapRecursive('sanitize_text_field', $payload);
    }

    public function arrayMapRecursive(callable $callback, array $array): array
    {
        if (empty($array)) return [];

        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $array[$key] = $this->arrayMapRecursive($callback, $value);
            } else {
                $array[$key] = call_user_func($callback, $value);
            }
        }

        return $array;
    }
}
