<?php
declare(strict_types=1);

/*
 * This file is part of the "proxy" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

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
