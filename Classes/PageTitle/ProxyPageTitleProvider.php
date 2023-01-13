<?php
declare(strict_types=1);

/*
 * This file is part of the "proxy" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace WapplerSystems\Proxy\PageTitle;


use TYPO3\CMS\Core\PageTitle\AbstractPageTitleProvider;

/**
 * Generate page title based on properties of the news model
 */
class ProxyPageTitleProvider extends AbstractPageTitleProvider
{

    public function setTitle(string $title): void
    {
        $this->title = $title;
    }
}
