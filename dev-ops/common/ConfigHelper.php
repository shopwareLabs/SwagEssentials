<?php declare(strict_types=1);

include __DIR__ . '/../../shopware/vendor/autoload.php';

class ConfigHelper
{
    const CONFIG_PATH = __DIR__ . '/../../shopware/config.php';

    public static function enableElasticSearch()
    {
        $config = self::getConfig(self::CONFIG_PATH);

        $config['es'] = [
            'enabled' => true,
            'number_of_replicas' => 0,
            'number_of_shards' => null,
            'client' => [
                'hosts' => [
                    '10.123.123.50:9200',
                ],
            ],
        ];

        self::saveConfig($config);
    }

    public static function enableDebug()
    {
        $config = self::getConfig(self::CONFIG_PATH);

        $config['errorHandler'] = [
            'throwOnRecoverableError' => true,
        ];

        $config['front'] = [
            'noErrorHandler' => true,
            'throwExceptions' => true,
            'disableOutputBuffering' => true,
            'showException' => true,
        ];

        $config['model'] = [
            'cacheProvider' => 'array',
        ];

        $config['phpsettings'] = [
            'error_reporting' => E_ALL,
            'display_errors' => 1,
        ];

        self::saveConfig($config);
    }

    public static function enableCsrfProtection()
    {
        $config = self::getConfig(self::CONFIG_PATH);

        if (isset($config['csrfprotection'])) {
            unset($config['csrfprotection']);
        }

        self::saveConfig($config);
    }

    public static function enableSwagEssentialsModule(string $moduleName)
    {
        $config = self::getConfig(self::CONFIG_PATH);
        if (!isset($config['swag_essentials'])) {
            $config['swag_essentials'] = [
                'modules' => [
                    // invalidate multiple appserver right from the backend
                    'CacheMultiplexer' => false,
                    // additional caching for shops without HTTP cache
                    'Caching' => false,
                    // use multiple read databases
                    'PrimaryReplica' => false,
                    // use NumberRanges from Redis
                    'RedisNumberRange' => false,
                    // use PluginConfig from Redis
                    'RedisPluginConfig' => false,
                    // store ListProducts in Redis
                    'RedisProductGateway' => false,
                    // Use Redis as HTTP cache backend
                    'RedisStore' => false,
                    // Use Redis for storing cached translations
                    'RedisTranslation' => false,
                ],
                'redis' =>
                    [
                        [
                            'host' => 'app_redis',
                            'port' => 6379,
                            'persistent' => true,
                            'dbindex' => 0,
                            'auth' => 'app',
                        ],
                    ],
                'cache_multiplexer_hosts' =>
                    [
                        [
                            'host' => 'http://10.123.123.31/api',
                            'user' => 'demo',
                            'password' => 'demo',

                        ],
                        [
                            'host' => 'http://10.123.123.32/api',
                            'user' => 'demo',
                            'password' => 'demo',

                        ],
                    ],
                // enable/disable caches
                'caching_enable_urls' => true,
                'caching_enable_list_product' => true,
                'caching_enable_product' => true,
                // ttl configs
                'caching_ttl_urls' => 3600,
                'caching_ttl_list_product' => 3600,
                'caching_ttl_product' => 3600,

                // ttl config for redis cache
                'caching_ttl_plugin_config' => 3600,

                'caching_ttl_translation' => 3600,
            ];
        }

        if (isset($config['swag_essentials']['modules'][$moduleName])) {
            $config['swag_essentials']['modules'][$moduleName] = true;
        }

        if ($moduleName === 'RedisStore') {
            $httpCache = [
                'storeClass' => \SwagEssentials\Redis\Store\RedisStore::class,
                'compressionLevel' => 9,
                'redisConnections' => [
                    0 =>
                        [
                            'host' => 'app_redis',
                            'port' => 6379,
                            'persistent' => true,
                            'dbindex' => 0,
                            'auth' => 'app',
                        ],
                ],
            ];

            if (isset($config['httpcache'])) {
                $httpCache = array_merge($config['httpcache'], $httpCache);
            }

            $config['httpcache'] = $httpCache;
        }

        if ($moduleName === 'PrimaryReplica') {
            $db = [
                'factory' => '\SwagEssentials\PrimaryReplica\PdoFactory',
                'replicas' => [
                    'replica-slave' => [
                        'username' => 'app',
                        'password' => 'app',
                        'dbname' => 'shopware',
                        'host' => '10.123.123.41',
                        'port' => '',
                    ],
                ],
            ];

            $config['db'] = array_merge($config['db'], $db);
        }

        self::saveConfig($config);
    }

    public static function disableElasticSearch()
    {
        $config = self::getConfig(self::CONFIG_PATH);

        if (isset($config['es'])) {
            unset($config['es']);
        }

        self::saveConfig($config);
    }

    public static function disableDebug()
    {
        $config = self::getConfig(self::CONFIG_PATH);

        if (isset($config['errorHandler'])) {
            unset($config['errorHandler']);
        }

        if (isset($config['front'])) {
            unset($config['front']);
        }

        if (isset($config['model'])) {
            unset($config['model']);
        }

        if (isset($config['phpsettings'])) {
            unset($config['phpsettings']);
        }

        self::saveConfig($config);
    }

    public static function disableCsrfProtection()
    {
        $config = self::getConfig(self::CONFIG_PATH);

        $config['csrfprotection'] = [
            'frontend' => false,
            'backend' => false,
        ];

        self::saveConfig($config);
    }

    public static function disableSwagEssentialsModule(string $moduleName = '')
    {
        $config = self::getConfig(self::CONFIG_PATH);

        if (isset($config['swag_essentials']['modules'][$moduleName])) {
            $config['swag_essentials']['modules'][$moduleName] = false;
        }

        if ($moduleName === 'PrimaryReplica') {
            unset($config['db']['factory'], $config['db']['replicas']);
        }


        self::saveConfig($config);
    }

    protected static function getConfig(string $configPath)
    {
        if (!file_exists($configPath)) {
            throw new RuntimeException('please install shopware first!');
        }

        return include $configPath;
    }

    protected static function saveConfig($config)
    {
        $configFile = '<?php 
            require_once __DIR__.\'/custom/plugins/SwagEssentials/Redis/Store/RedisStore.php\';
            require_once __DIR__.\'/custom/plugins/SwagEssentials/Redis/Factory.php\';
            require_once __DIR__.\'/custom/plugins/SwagEssentials/Redis/RedisConnection.php\';
            require_once __DIR__ . \'/custom/plugins/SwagEssentials/PrimaryReplica/PdoFactory.php\';
            
        return ' . var_export($config, true) . ';';
        file_put_contents(self::CONFIG_PATH, $configFile, LOCK_EX);
    }
}