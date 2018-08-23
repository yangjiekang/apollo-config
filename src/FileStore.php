<?php

namespace Totoro\Apollo;



class FileStore implements Store
{

    /**
     * The file path.
     *
     * @var string
     */
    protected $filePath;

    /**
     * The loader instance.
     *
     * @var \Dotenv\Loader|null
     */
    protected $loader;

    protected $data;

    public function __construct($path, $file = '.env')
    {
        $this->filePath = $this->getFilePath($path, $file);
        $this->loader = new EnvFile($this->filePath, true);
        $this->loader->load();
        $this->data = $this->loader->getData();
    }

    public function all()
    {
        return $this->data;
    }
    /**
     * Retrieve an item from the cache by key.
     *
     * @param  string|array  $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->data[$key] ?? null;
    }

    /**
     * Store an item in the cache for a given number of minutes.
     *
     * @param  string  $key
     * @param  mixed   $value
     * @return void
     */
    public function put($key, $value){}

    /**
     * Store multiple items in the cache for a given number of minutes.
     *
     * @param  array  $values
     * @return void
     */
    public function putMany(array $values){}

    /**
     * Remove all items from the cache.
     *
     * @return bool
     */
    public function flush(){}

    /**
     * Returns the full path to the file.
     *
     * @param string $path
     * @param string $file
     *
     * @return string
     */
    protected function getFilePath($path, $file)
    {
        if (!is_string($file)) {
            $file = '.env';
        }

        $filePath = rtrim($path, DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.$file;

        return $filePath;
    }
}