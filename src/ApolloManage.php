<?php
namespace Totoro\Apollo;

use InvalidArgumentException;
use Illuminate\Contracts\Cache\Factory as FactoryContract;
use Predis\Client;

class ApolloManage implements FactoryContract
{
    /**
     * The application instance.
     *
     * @var \Illuminate\Foundation\Application
     */
    protected $app;

    /**
     * The array of resolved apollo stores.
     *
     * @var array
     */
    protected $stores = [];

    /**
     * Create a new Apollo manager instance.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * Get a apollo store instance by name.
     *
     * @param  string|null  $name
     * @return \Totoro\Apollo\Repository
     */
    public function store($name = null)
    {
        $name = $name ?: $this->getDefaultDriver();

        return $this->stores[$name] = $this->get($name);
    }

    /**
     * Get a apollo driver instance.
     *
     * @param  string  $driver
     * @return mixed
     */
    public function driver($driver = null)
    {
        return $this->store($driver);
    }

    /**
     * Attempt to get the store from the local cache.
     *
     * @param  string  $name
     * @return \Totoro\Apollo\Repository
     */
    protected function get($name)
    {
        return $this->stores[$name] ?? $this->resolve($name);
    }

    /**
     * Resolve the given store.
     *
     * @param  string  $name
     * @return \Totoro\Apollo\Repository
     *
     * @throws \InvalidArgumentException
     */
    protected function resolve($name)
    {
        $config = $this->getConfig($name);

        if (is_null($config)) {
            throw new InvalidArgumentException("Apollo store [{$name}] is not defined.");
        }


        $driverMethod = 'create'.ucfirst($config['driver']).'Driver';

        if (method_exists($this, $driverMethod)) {
            return $this->{$driverMethod}($config);
        } else {
            throw new InvalidArgumentException("Driver [{$config['driver']}] is not supported.");
        }
    }

    /**
     * Create an instance of the Redis cache driver.
     *
     * @param  array  $config
     * @return \Totoro\Apollo\Repository
     */
    protected function createRedisDriver(array $config)
    {
        $client = new Client($config);
        $serviceConfig = $this->getServiceConfig();
        return $this->repository(new RedisStore($client, $serviceConfig));
    }

    /**
     * Create an instance of the database cache driver.
     *
     * @param  array  $config
     * @return \Totoro\Apollo\Repository
     */
    protected function createShmDriver(array $config)
    {
        $shmId = $config['id'];
        $serviceConfig = $this->getServiceConfig();
        $shm = new Shm($shmId);
        return $this->repository(new ShmStore($shm, $serviceConfig));
    }

    /**
     * Create an instance of the database cache driver.
     *
     * @param  array  $config
     * @return \Totoro\Apollo\Repository
     */
    protected function createFileDriver(array $config)
    {
        $conf = $config['conf'];
        return $this->repository(new FileStore($conf));
    }

    /**
     * Create a new apollo repository with the given implementation.
     *
     * @param  \Totoro\Apollo\Store $store
     * @return \Totoro\Apollo\Repository
     */
    public function repository(Store $store)
    {
        return new Repository($store);
    }

    /**
     * Get the cache connection configuration.
     *
     * @param  string  $name
     * @return array
     */
    protected function getConfig($name)
    {
        return $this->app['config']["apollo.stores.{$name}"];
    }

    /**
     * Get the default cache driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->app['config']['apollo.default'];
    }

    public function getServiceConfig()
    {
        return $this->app['config']['apollo.services'];
    }

    /**
     * Set the default cache driver name.
     *
     * @param  string  $name
     * @return void
     */
    public function setDefaultDriver($name)
    {
        $this->app['config']['apollo.default'] = $name;
    }



    /**
     * Dynamically call the default driver instance.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->store()->$method(...$parameters);
    }
}