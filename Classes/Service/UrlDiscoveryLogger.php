<?php
declare(strict_types=1);

/*
 * This file is part of the "proxy" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace WapplerSystems\Proxy\Service;

use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;

/**
 * Logs each proxied target URL exactly once.
 *
 * Two-tier deduplication: a static in-memory set short-circuits subsequent hits
 * within the same PHP process (zero I/O), and a TYPO3 cache (proxy_url_seen)
 * persists the set across requests. The cache stores only md5 hashes, never the
 * URL itself, so size is bounded. Flush via `cache:flush --tag proxy_url_seen`.
 */
final class UrlDiscoveryLogger implements LoggerAwareInterface
{
    use LoggerAwareTrait;

    /** @var array<string, true> */
    private static array $seen = [];

    private bool $enabled;

    public function __construct(
        private readonly FrontendInterface $cache,
        ExtensionConfiguration             $extensionConfiguration,
    ) {
        try {
            $this->enabled = (bool)$extensionConfiguration->get('proxy', 'enableUrlLogging');
        } catch (\Throwable) {
            $this->enabled = false;
        }
    }

    public function logOnce(string $url): void
    {
        if (!$this->enabled || $url === '') {
            return;
        }

        $hash = md5($url);
        if (isset(self::$seen[$hash])) {
            return;
        }
        self::$seen[$hash] = true;

        if ($this->cache->has($hash)) {
            return;
        }

        $this->logger?->info('proxy url discovered', ['url' => $url]);

        try {
            $this->cache->set($hash, '1', ['proxy_url_seen'], 0);
        } catch (\Throwable) {
            // Best effort — a write failure must not affect the proxy request.
        }
    }
}