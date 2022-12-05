<?php

namespace WapplerSystems\Proxy\Backend\Form;

class PluginsRegistry {


    public function getPluginsList(array &$configuration) {

        foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['proxy']['plugins'] as $name => $className) {

            $configuration['items'][] = [$name, $name];

        }

    }

}
