<?php
/**
 * Invoice Ninja (https://invoiceninja.com).
 *
 * @link https://github.com/invoiceninja/invoiceninja source repository
 *
 * @copyright Copyright (c) 2025. Invoice Ninja LLC (https://invoiceninja.com)
 *
 * @license https://www.elastic.co/licensing/elastic-license
 */

namespace App\DataMapper;

/**
 * BaseSettings.
 */
class BaseSettings
{
    public static function setCasts($obj, $casts)
    {
        foreach ($casts as $key => $value) {
            $obj->{$key} = self::castAttribute($key, $obj->{$key});
        }

        return $obj;
    }

    public static function castAttribute($key, $value)
    {
        switch ($key) {
            case 'int':
            case 'integer':
                return is_scalar($value) ? (int) $value : 0;
            case 'real':
            case 'float':
            case 'double':
                return is_scalar($value) ? (float) $value : 0;
            case 'string':
                return is_scalar($value) ? (string) $value : '';
            case 'bool':
            case 'boolean':
                return is_scalar($value) ? boolval($value) : false;
            case 'object':
                return json_decode($value);
            case 'array':
            case 'json':
                return json_decode($value, true);
            default:
                return $value;
        }
    }
}
