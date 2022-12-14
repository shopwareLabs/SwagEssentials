<?php

declare(strict_types=1);
/**
 * (c) shopware AG <info@shopware.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SwagEssentials\Redis\PluginConfig;

use Shopware\Components\Plugin\ConfigReader as ConfigReaderInterface;
use Shopware\Components\Plugin\DBALConfigReader;
use Shopware\Models\Shop\Shop;
use SwagEssentials\Redis\RedisConnection;

class Reader implements ConfigReaderInterface
{
    public const HASH_NAME = 'sw_config';

    /**
     * @var DBALConfigReader
     */
    protected $configReader;

    /**
     * @var RedisConnection
     */
    protected $redis;

    /**
     * @var int
     */
    protected $cachingTtlPluginConfig;

    public function __construct(DBALConfigReader $configReader, RedisConnection $redis, int $cachingTtlPluginConfig)
    {
        $this->configReader = $configReader;
        $this->redis = $redis;
        $this->cachingTtlPluginConfig = $cachingTtlPluginConfig;
    }

    /**
     * @param string $pluginName
     *
     * @return array
     */
    public function getByPluginName($pluginName, Shop $shop = null)
    {
        $key = [
            'pluginName' => $pluginName,
            'shopId' => $shop ? $shop->getId() : 0,
        ];

        $key = implode('|', $key);

        $result = $this->redis->hGet(self::HASH_NAME, $key);
        if ($result) {
            return json_decode($result, true);
        }

        $result = $this->configReader->getByPluginName($pluginName, $shop);

        $this->redis->hSet(self::HASH_NAME, $key, json_encode($result));
        $this->redis->expire(self::HASH_NAME, $this->cachingTtlPluginConfig);

        return $result;
    }
}
