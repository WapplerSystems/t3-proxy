<?php
namespace WapplerSystems\Proxy\Frontend\DataProcessing;


use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class LanguageMenuProcessor extends \TYPO3\CMS\Frontend\DataProcessing\LanguageMenuProcessor
{

    public function __construct()
    {
        parent::__construct();

        $this->allowedConfigurationKeys = array_merge($this->allowedConfigurationKeys,[
            'overrides',
            'overrides.',
        ]);
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

        // Get Configuration
        $this->menuTargetVariableName = $this->getConfigurationValue('as');

        // Validate and Build Configuration
        $this->validateAndBuildConfiguration();

        // Process Configuration
        $menuContentObject = $cObj->getContentObject('HMENU');
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
        $processedData[$this->menuTargetVariableName] = $processedMenu;
        return $processedData;
    }


}
