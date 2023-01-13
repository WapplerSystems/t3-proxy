<?php
declare(strict_types=1);

/*
 * This file is part of the "proxy" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace WapplerSystems\Proxy\Backend\Form;

class PluginsRegistry {


    public function getPluginsList(array &$configuration) {

        foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['proxy']['plugins'] as $name => $className) {

            $configuration['items'][] = [$name, $name];

        }

    }

}
