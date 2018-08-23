<?php
/**
 * Created by PhpStorm.
 * User: yangjiekang
 * DateTime: 2018-08-23 9:50
 * Email: 121491162@qq.com
 */

namespace Totoro\Apollo;


use Dotenv\Loader;

class EnvFile extends Loader
{
    protected $data;

    public function __construct($filePath, $immutable)
    {
        $this->data = [];
        parent::__construct($filePath, $immutable);
    }

    public function getData()
    {
        return $this->data;
    }

    public function load()
    {
        $this->ensureFileIsReadable();

        $filePath = $this->filePath;
        $lines = $this->readLinesFromFile($filePath);
        foreach ($lines as $line) {
            if (!$this->isComment($line) && $this->looksLikeSetter($line)) {
                $this->setEnvironmentVariable($line);
            }
        }

        return $lines;
    }

    /**
     * Set an environment variable.
     *
     * This is done using:
     * - putenv,
     * - $_ENV,
     * - $_SERVER.
     *
     * The environment variable value is stripped of single and double quotes.
     *
     * @param string      $name
     * @param string|null $value
     *
     * @return void
     */
    public function setEnvironmentVariable($name, $value = null)
    {
        list($name, $value) = $this->normaliseEnvironmentVariable($name, $value);

        // Don't overwrite existing environment variables if we're immutable
        // Ruby's dotenv does this with `ENV[key] ||= value`.
        if ($this->immutable && $this->getEnvironmentVariable($name) !== null) {
            return;
        }

        $this->data[$name] = $value;

    }
}