<?php

declare(strict_types=1);

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
