<?php
declare(strict_types=1);

/*
 * This file is part of the "proxy" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace WapplerSystems\Proxy\Frontend\DataProcessing;


use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

#[Autoconfigure(public: true)]
class LanguageMenuProcessor extends \TYPO3\CMS\Frontend\DataProcessing\LanguageMenuProcessor
{

    protected function validateConfiguration(): void
    {
        $this->allowedConfigurationKeys = array_merge($this->allowedConfigurationKeys,[
            'overrides',
            'overrides.',
        ]);

        parent::validateConfiguration();
    }

    public function process(ContentObjectRenderer $cObj, array $contentObjectConfiguration, array $processorConfiguration, array $processedData): array
    {
        $processedData = parent::process($cObj, $contentObjectConfiguration, $processorConfiguration, $processedData);

        $as = $this->getConfigurationValue('as');
        if (empty($processedData[$as]) || !is_array($processedData[$as])) {
            return $processedData;
        }

        $overrides = $processorConfiguration['overrides.'] ?? [];

        $processedMenu = [];
        foreach ($processedData[$as] as $key => $language) {
            $processedMenu[$key] = $language;
            if (isset($language['hreflang']) && array_key_exists($language['hreflang'], $overrides)) {
                $processedMenu[$key]['link'] = $overrides[$language['hreflang']];
            }
        }

        $processedData[$as] = $processedMenu;
        return $processedData;
    }
}
