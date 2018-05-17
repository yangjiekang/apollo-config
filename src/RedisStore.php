<?php
namespace Totoro\Apollo;

use Predis\Client;
use Totoro\Apollo\Commands\Helpers\Publisher;
use Totoro\Apollo\Commands\PublishComponentCommand;

class RedisStore implements Store
{
    protected $client;

    protected $serviceKey;

    protected $config;

    protected $urlKey;

    protected $serviceName;

    public function __construct(Client $client, $serviceConfig)
    {
        $this->client = $client;
        $this->serviceKey = $serviceConfig['service_key'];
        $this->urlKey = $serviceConfig['url_key'];
        $this->serviceName =  $serviceConfig['service_names'];
        $data = $this->client->get($this->serviceKey);
        $this->config = ! is_null($data) ? $this->unserialize($data) : [];
    }

    public function get($key)
    {
        if(strpos($key, '_notificationId') !== false){
            return $this->getNotificationId($key);
        }
        if (in_array($key, $this->serviceName)){
            return $this->getServiceUrl($key);
        }
        /*
         * TODO 当值不存在时，远程更新一次,这里只做服务参数，其他的先不重新获取
         * 先注释掉,获取耗时要6~10秒
         */
//        if (empty($this->config[$key])){
//            $a = new PublishComponentCommand();
//            (new Publisher($a))->publishComponent();
//            $this->setConfig();
//        }
        $value = null;
        if (isset($this->config[$key])){
            $value = $this->config[$key];
        }
        return ! is_null($value) ? $value : null;
    }

    public function setConfig()
    {
        $data = $this->client->get($this->serviceKey);
        $this->config = ! is_null($data) ? $this->unserialize($data) : [];
    }

    public function getNotificationId($key)
    {
        return isset($this->source[$key]) ? $this->source[$key] : null;
    }

    public function all()
    {
        $keys = $this->client->keys('*');
        $data = [];
        foreach ($keys as $key){
            $data[$key] = $this->unserialize($this->client->get($key));
        }
        return $data;
    }

    public function put($key, $value)
    {
        $this->client->set($key, $this->serialize($value));
    }

    public function putMany(array $values)
    {
        foreach ($values as $key => $value) {
            $this->put($key, $value);
        }
    }

    public function getServiceUrl($key)
    {
        $data = $this->client->get($this->urlKey);
        $urls = ! is_null($data) ? $this->unserialize($data) : [];
        return isset($urls[$key]) ? $urls[$key] : null;
    }

    public function flush()
    {
        $this->client->flushdb();
        return true;
    }

    /**
     * Serialize the value.
     *
     * @param  mixed  $value
     * @return mixed
     */
    protected function serialize($value)
    {
        return is_numeric($value) ? $value : serialize($value);
    }

    /**
     * Unserialize the value.
     *
     * @param  mixed  $value
     * @return mixed
     */
    protected function unserialize($value)
    {
        return is_numeric($value) ? $value : unserialize($value);
    }


}