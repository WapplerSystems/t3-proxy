<?php
declare(strict_types=1);

/*
 * This file is part of the "proxy" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace WapplerSystems\Proxy\Plugin;


class AbstractAssetPlugin extends AbstractPlugin
{

    protected array $whiteList;


    protected function isOnWhiteList($path): bool
    {
        foreach ($this->whiteList as $pattern) {
            if (str_contains($path, $pattern)) {
                return true;
            }
        }
        return false;
    }

    /**
     * @param string|array $pathOrArray
     * @return void
     */
    public function addToWhiteList(string|array $pathOrArray)
    {
        if (is_string($pathOrArray)) {
            $this->whiteList[] = $pathOrArray;
        }
        if (is_array($pathOrArray)) {
            $this->whiteList = array_merge($this->whiteList, $pathOrArray);
        }
    }

}
