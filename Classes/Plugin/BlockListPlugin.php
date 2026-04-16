<?php
declare(strict_types=1);

/*
 * This file is part of the "proxy" Extension for TYPO3 CMS.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 */

namespace WapplerSystems\Proxy\Plugin;

use WapplerSystems\Proxy\Config;
use WapplerSystems\Proxy\Event\ProxyEvent;

class BlockListPlugin extends AbstractPlugin
{

    function onBeforeRequest(ProxyEvent $event)
    {

        $user_ip = $_SERVER['REMOTE_ADDR'];
        $user_ip_long = sprintf('%u', ip2long($user_ip));

        $url = $event['request']->getUrl();
        $url_host = parse_url($url, PHP_URL_HOST);

        $fnc_custom = Config::get('blocklist.custom');
        if (is_callable($fnc_custom)) {

            $ret = call_user_func($fnc_custom, compact('user_ip', 'user_ip_long', 'url', 'url_host'));
            if (!$ret) {
                throw new \Exception("Error: Access Denied!");
            }

            return;
        }

        // url filter!
        $url_block = (array)Config::get('blocklist.url_block');
        foreach ($url_block as $ub) {

            if (strpos($url, $ub) !== false) {
                throw new \Exception("Error: Access to {$url} has been blocked!");
            }
        }

        /*
        1. Wildcard format:     1.2.3.*
        2. CIDR format:         1.2.3/24  OR  1.2.3.4/255.255.255.0
        3. Start-End IP format: 1.2.3.0-1.2.3.255
        */
        $ip_match = false;
        $action_block = true;

        if (Config::has('blocklist.ip_allow')) {
            $ip_match = Config::get('blocklist.ip_allow');
            $action_block = false;
        } else if (Config::has('blocklist.ip_block')) {
            $ip_match = Config::get('blocklist.ip_block');
        }

        if ($ip_match) {
            $m = $this->matchIp($user_ip, (array)$ip_match);

            // ip matched and we are in block_mode
            // ip NOT matched and we are in allow mode
            if (($m && $action_block) || (!$m && !$action_block)) {
                throw new \Exception("Error: Access denied!");
            }
        }
    }

    /**
     * Match an IP address against a list of patterns.
     * Supports wildcard (1.2.3.*), CIDR (1.2.3.0/24), and range (1.2.3.0-1.2.3.255) formats.
     */
    private function matchIp(string $ip, array $patterns): bool
    {
        $ipLong = ip2long($ip);
        if ($ipLong === false) {
            return false;
        }

        foreach ($patterns as $pattern) {
            $pattern = trim($pattern);
            if ($pattern === '') {
                continue;
            }

            // Range format: 1.2.3.0-1.2.3.255
            if (str_contains($pattern, '-')) {
                [$rangeStart, $rangeEnd] = explode('-', $pattern, 2);
                $startLong = ip2long(trim($rangeStart));
                $endLong = ip2long(trim($rangeEnd));
                if ($startLong !== false && $endLong !== false && $ipLong >= $startLong && $ipLong <= $endLong) {
                    return true;
                }
                continue;
            }

            // CIDR format: 1.2.3.0/24 or 1.2.3.4/255.255.255.0
            if (str_contains($pattern, '/')) {
                [$subnet, $mask] = explode('/', $pattern, 2);
                if (str_contains($mask, '.')) {
                    // Netmask format: 255.255.255.0
                    $maskLong = ip2long($mask);
                } else {
                    // CIDR prefix length
                    $maskLong = -1 << (32 - (int)$mask);
                }
                $subnetLong = ip2long($subnet);
                if ($subnetLong !== false && $maskLong !== false && ($ipLong & $maskLong) === ($subnetLong & $maskLong)) {
                    return true;
                }
                continue;
            }

            // Wildcard format: 1.2.3.*
            if (str_contains($pattern, '*')) {
                $regex = '/^' . str_replace(['*', '.'], ['\\d+', '\\.'], $pattern) . '$/';
                if (preg_match($regex, $ip)) {
                    return true;
                }
                continue;
            }

            // Exact match
            if ($ip === $pattern) {
                return true;
            }
        }

        return false;
    }
}
