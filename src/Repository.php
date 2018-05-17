<?php
namespace Totoro\Apollo;

class Repository
{
    /**
     * The cache store implementation.
     *
     * @var \Illuminate\Contracts\Cache\Store
     */
    protected $store;

    /**
     * Create a new cache repository instance.
     *
     * @param  \Totoro\Apollo\Store  $store
     * @return void
     */
    public function __construct(Store $store)
    {
        $this->store = $store;
    }

    /**
     * Determine if an item exists in the cache.
     *
     * @param  string  $key
     * @return bool
     */
    public function has($key)
    {
        return ! is_null($this->get($key));
    }

    /**
     * Retrieve an item from the cache by key.
     *
     * @param  string  $key
     * @param  mixed   $default
     * @return mixed
     */
    public function get($key, $default = null)
    {

        $value = $this->store->get($key);

        if (is_null($value)){
            $value = value($default);
        }

        return $value;
    }

    public function all()
    {
        return $this->store->all();
    }


    /**
     * Store an item in the cache.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function put($key, $value)
    {

        $this->store->put($key, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function set($key, $value)
    {
        $this->put($key, $value);
    }

    /**
     * Store multiple items in the cache for a given number of minutes.
     *
     * @param  array  $values
     * @param  \DateTimeInterface|\DateInterval|float|int  $minutes
     * @return void
     */
    public function putMany(array $values)
    {
         $this->store->putMany($values);
    }

    /**
     * {@inheritdoc}
     */
    public function clear()
    {
        return $this->store->flush();
    }

    /**
     * Get the apollo store implementation.
     *
     * @return \Illuminate\Contracts\Cache\Store
     */
    public function getStore()
    {
        return $this->store;
    }

    /**
     * 根据参数获取配置值
     * @param  string $name 参数
     * @return mixed
     */
    public function __get($key)
    {
        return $this->get($key);
    }


    /**
     * Handle dynamic calls into macros or pass missing methods to the store.
     *
     * @param  string  $method
     * @param  array   $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {

        return $this->store->$method(...$parameters);
    }

    /**
     * Clone apollo repository instance.
     *
     * @return void
     */
    public function __clone()
    {
        $this->store = clone $this->store;
    }
}
