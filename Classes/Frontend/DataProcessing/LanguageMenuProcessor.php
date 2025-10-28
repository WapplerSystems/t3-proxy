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

    protected function validateConfiguration()
    {
        $this->allowedConfigurationKeys = array_merge($this->allowedConfigurationKeys,[
            'overrides',
            'overrides.',
        ]);

        parent::validateConfiguration();
    }

    /**
     * @param ContentObjectRenderer $cObj The data of the content element or page
     * @param array $contentObjectConfiguration The configuration of Content Object
     * @param array $processorConfiguration The configuration of this processor
     * @param array $processedData Key/value store of processed data (e.g. to be passed to a Fluid View)
     * @return array the processed data as key/value store
     */
    public function process(ContentObjectRenderer $cObj, array $contentObjectConfiguration, array $processorConfiguration, array $processedData): array
    {
        $this->cObj = $cObj;
        $this->processorConfiguration = $processorConfiguration;

        // Validate and Build Configuration
        $this->validateAndBuildConfiguration();

        // Process Configuration
        $menuContentObject = $this->contentObjectFactory->getContentObject('HMENU', $cObj->getRequest(), $cObj);
        $renderedMenu = $menuContentObject->render($this->menuConfig);
        if (!$renderedMenu) {
            return $processedData;
        }

        $overrides = $this->processorConfiguration['overrides.'] ?? [];

        // Process menu
        $menu = json_decode($renderedMenu, true);
        $processedMenu = [];
        if (is_iterable($menu)) {
            foreach ($menu as $key => $language) {
                $processedMenu[$key] = $language;
                if (array_key_exists($language['hreflang'],$overrides)) {
                    $processedMenu[$key]['link'] = $overrides[$language['hreflang']];
                }
            }
        }
        // Return processed data
        $processedData[$this->getConfigurationValue('as')] = $processedMenu;
        return $processedData;
    }


}
