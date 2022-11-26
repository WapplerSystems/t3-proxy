<?php

declare(strict_types=1);

namespace WapplerSystems\Proxy\Routing\Aspect;

use TYPO3\CMS\Core\Routing\Aspect\MappableAspectInterface;


class PathMapper implements MappableAspectInterface
{


    /**
     * @param array $settings
     * @throws \InvalidArgumentException
     */
    public function __construct(array $settings)
    {

    }


    /**
     * {@inheritdoc}
     */
    public function generate(string $value): ?string
    {
        return $value;
    }

    /**
     * {@inheritdoc}
     */
    public function resolve(string $value): ?string
    {
        return $value;
    }


}
