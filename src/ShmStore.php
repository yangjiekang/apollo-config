<?php
namespace Totoro\Apollo;

use Illuminate\Console\Scheduling\Schedule;
use Totoro\Apollo\Commands\Helpers\Publisher;
use Totoro\Apollo\Commands\PublishComponentCommand;

class ShmStore implements Store
{
    /**
     * @var int
     * @access protected
     */
    protected $shm;

    protected $config;

    protected $source;

    protected $serviceKey;

    protected $urlKey;

    protected $serviceName;

    public function __construct(Shm $shm, $serviceConfig)
    {
        $this->shm = $shm;
        $this->serviceKey = $serviceConfig['service_key'];
        $this->urlKey = $serviceConfig['url_key'];
        $this->serviceName =  $serviceConfig['service_names'];
        $this->source = $this->shm->selfExists() ? json_decode($this->shm->read(), true) : [];
        $this->config = isset($this->source[$this->serviceKey]) ? $this->source[$this->serviceKey] : [];
    }

    public function all()
    {
        return $this->source;
    }


    public function test()
    {
        return 'shm store';
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
        return isset($this->config[$key]) ? $this->config[$key] : null;
    }

    public function setConfig()
    {
        $this->source = $this->shm->selfExists() ? json_decode($this->shm->read(), true) : [];
        $this->config = isset($this->source[$this->serviceKey]) ? $this->source[$this->serviceKey] : [];
    }

    public function getNotificationId($key)
    {
        return isset($this->source[$key]) ? $this->source[$key] : null;
    }

    public function getServiceUrl($key)
    {
        $urls = isset($this->source[$this->urlKey]) ? $this->source[$this->urlKey] : [];
        return isset($urls[$key]) ? $urls[$key] : null;
    }


     public function put($key, $value)
    {
        $this->source[$key] = $value;
        $data = json_encode($this->source);
        $this->shm->write($data);
    }


    public function putMany(array $values)
    {
        foreach ($values as $key => $value){
            $this->put($key, $value);
        }
    }


    public function flush()
    {
        $this->shm->delete();
        return true;
    }
}