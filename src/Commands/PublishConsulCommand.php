<?php

namespace Totoro\Apollo\Commands;

use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use Illuminate\Console\Command;

class PublishConsulCommand extends Command
{
    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'apollo:publish-consul';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publish server url';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function handle()
    {
        $this->info('Publish consul server url');

        $consulConfig = config('apollo.consul');
        $urlKey = config('apollo.services.url_key');
        //通信服务地址
        $consulUrl = $consulConfig['url'];
        $consulToken = $consulConfig['token'];

        $url = $consulUrl . '/v1/agent/members?token=' . $consulToken . '&wan=true';
        $res = file_get_contents($url);
        if (!$res){
            $this->info('Publish failed');
        }
        $res = json_decode($res, true);
        $service_name = [];
        foreach ($res as $v) {
            if (!empty($v['Addr'])) {
                $url = 'http://' . $v['Addr'] . ':8500/v1/agent/services?token=' . $consulToken;
                $_res = file_get_contents($url);
                if ($_res) {
                    $_res = json_decode($_res, true);
                    foreach ($_res as $vv) {
                        if (!empty($vv['Service']) && !in_array($vv['Service'], $service_name)) {
                            $service_name[] = $vv['Service'];
                        }
                    }
                }
            }
        }
        if ($service_name) {
            $client = new Client();
            $requests = function ($service_name) use ($client, $consulUrl, $consulToken) {
                foreach ($service_name as $option) {
                    $url = $consulUrl . '/v1/health/service/' . $option . '?passing=true&token=' . $consulToken;
                    $query = ['http_errors'=>false, 'timeout'=>60];

                    yield function() use ($client, $url, $query) {
                        return $client->requestAsync('GET', $url, $query);
                    };
                }
            };
            $result = [];
            $pool = new Pool($client, $requests($service_name), [
                'concurrency' => count($service_name),
                'fulfilled'   => function ($response, $index) use ($service_name, &$result) {
                    $res = json_decode($response->getBody()->getContents(), true);
                    if ($res) {
                        $result[$service_name[$index]] = $res;
                    }
                },
                'rejected' => function ($reason, $index) {
                },
            ]);
            $promise = $pool->promise();
            $promise->wait();

            //处理返回数据
            $data = [];
            if ($result) {
                foreach ($result as $key => $res) {
                    if ($res) {
                        $_key = array_rand($res);
                        if (!empty($res[$_key]['Service']['Tags'])) {
                            $tag = array_filter($res[$_key]['Service']['Tags'], function ($val) {
                                return strpos($val, 'service-url=') !== false;
                            });
                            if ($tag) {
                                $tag = current($tag);
                                list(, $data[$key]) = explode('=', $tag, 2);
                            }
                        }
                        if (empty($data[$key])) {
                            $data[$key] = 'http://' . $res[$_key]['Service']['Address'];
                            if (!empty($res[$_key]['Service']['Port'])) {
                                $data[$key] .= ':' . $res[$_key]['Service']['Port'];
                            }
                        }
                    }
                }
            }
            if ($data) {
                $oldData = app('apollo')->all();
                $newData = array_merge(is_array($oldData) ? $oldData : [], [$urlKey => $data]);
                app('apollo')->putMany($newData);
            }
        }

        $this->info('Publish finish');

    }
}
