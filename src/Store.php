<?php
namespace Totoro\Apollo;

interface Store
{

    public function all();
    /**
     * Retrieve an item from the cache by key.
     *
     * @param  string|array  $key
     * @return mixed
     */
    public function get($key);

    /**
     * Store an item in the cache for a given number of minutes.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function put($key, $value);

    /**
     * Store multiple items in the cache for a given number of minutes.
     *
     * @param  array  $values
     * @return void
     */
    public function putMany(array $values);

    /**
     * Remove all items from the cache.
     *
     * @return bool
     */
    public function flush();
}
