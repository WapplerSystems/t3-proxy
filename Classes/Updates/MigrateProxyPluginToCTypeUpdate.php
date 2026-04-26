<?php

declare(strict_types=1);

/*
 * This file is part of the "proxy" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace WapplerSystems\Proxy\Updates;

use TYPO3\CMS\Core\Attribute\UpgradeWizard;
use TYPO3\CMS\Core\Upgrades\AbstractListTypeToCTypeUpdate;

#[UpgradeWizard('proxy_migrateProxyPluginToCType')]
final class MigrateProxyPluginToCTypeUpdate extends AbstractListTypeToCTypeUpdate
{
    protected function getListTypeToCTypeMapping(): array
    {
        return [
            'proxy_proxy' => 'proxy_proxy',
        ];
    }

    public function getTitle(): string
    {
        return 'EXT:proxy: Migrate "proxy" plugin to content element';
    }

    public function getDescription(): string
    {
        return 'The "proxy" plugin was previously registered as a list_type under the generic "list" CType. '
            . 'Since TYPO3 13 plugins are registered as their own CType. This wizard migrates affected '
            . 'tt_content records (CType=list, list_type=proxy_proxy) to CType=proxy_proxy and clears list_type, '
            . 'and updates be_groups.explicit_allowdeny accordingly.';
    }
}
