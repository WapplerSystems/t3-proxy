<?php
declare(strict_types=1);

/*
 * This file is part of the "proxy" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace WapplerSystems\Proxy\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Cache\CacheManager;

class FlushCacheCommand extends Command
{
    public function __construct(private readonly CacheManager $cacheManager)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setDescription('Flush the proxy response cache');
        $this->setHelp('Removes all cached proxy responses. Use this after changes to proxied content sources.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $cache = $this->cacheManager->getCache('proxy_responses');
            $cache->flush();
            $io->success('Proxy response cache has been flushed.');
        } catch (\Throwable $e) {
            $io->error('Failed to flush proxy cache: ' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}