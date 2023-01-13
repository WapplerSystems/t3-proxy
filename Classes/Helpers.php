<?php
declare(strict_types=1);

/*
 * This file is part of the "proxy" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace WapplerSystems\Proxy;

class Helpers
{


    /**
     * regular array_merge does not work if arrays have numeric keys...
     * @return array
     */
    public static function array_merge(): array
    {

        $arr = [];
        $args = func_get_args();

        foreach ((array)$args as $arg) {
            foreach ((array)$arg as $key => $value) {
                $arr[$key] = $value;
            }
        }

        return $arr;
    }

}
