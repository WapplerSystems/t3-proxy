<?php
declare(strict_types=1);

/*
 * This file is part of the "proxy" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace WapplerSystems\Proxy;

class Config
{
    private static $config = [];

    public static function get($key, $default = null)
    {
        return self::has($key) ? static::$config[$key] : $default;
    }

    public static function set($key, $value)
    {
        self::$config[$key] = $value;
    }

    public static function has($key)
    {
        return isset(static::$config[$key]);
    }

    public static function load($path)
    {
        if (file_exists($path)) {

            // Successful includes, unless overridden by the included file, return 1.
            $data = require($path);

            if (is_array($data)) {
                self::$config = array_merge(self::$config, $data);
            }
        }
    }
}
