<?php

namespace Totoro\Apollo\Commands\Helpers;

use GuzzleHttp\Client;
use GuzzleHttp\Pool;
use Illuminate\Console\Command;

class Publisher
{
    protected $command;

    public function __construct(Command $command)
    {
        $this->command = $command;
    }

    public function publishFile($source, $destinationPath, $fileName)
    {
        if (! is_dir($destinationPath)) {
            if (! mkdir($destinationPath, 0755, true)) {
                $this->command->error('Cant crate directory: '.$destinationPath);
            }
        }

        if (! is_writable($destinationPath)) {
            if (! chmod($destinationPath, 0755)) {
                $this->command->error('Destination path is not writable');
            }
        }

        if (file_exists($source)) {
            if (! copy($source, $destinationPath.'/'.$fileName)) {
                $this->command->error('File was not copied');
            }
        } else {
            $this->command->error('Source file does not exists');
        }
    }

    public function publishDirectory($source, $destination)
    {
        if (! is_dir($source)) {
            $this->command->error('Bad source path');
        } else {
            $dir = opendir($source);

            if (! is_dir($destination)) {
                if (! mkdir($destination, 0755, true)) {
                    $this->command->error('Cant crate directory: '.$destination);
                }
            }

            while (false !== ($file = readdir($dir))) {
                if (($file != '.') && ($file != '..')) {
                    if (is_dir($source.'/'.$file)) {
                        $this->publishDirectory($source.'/'.$file, $destination.'/'.$file);
                    } else {
                        copy($source.'/'.$file, $destination.'/'.$file);
                    }
                }
            }
            closedir($dir);
        }
    }


    /**
     * 放到这里主要是为了其他代码能直接调用,因为没有找打可以直接执行Command的方法
     */
    public function publishComponent()
    {
        $componentConfig = config('apollo.components');
        $serviceKey = config('apollo.services.service_key');
        $componentName = $componentConfig['name'];
        $apps = $componentConfig['apps'];
        $componentUrl = $componentConfig['url'];
        $ak = $componentConfig['iam']['ak'];
        $sk = $componentConfig['iam']['sk'];

        $notifications = [];
        foreach ($apps as $key => $component) {
            $notifications[$key] = [
                'namespaceName'  => $component['namespaceName'],
                'notificationId' => app('apollo')->get($key . $componentName . '_notificationId')
            ];
        }

        $url = rtrim($componentUrl, '/') . '/notifications/v2?';
        $url .= 'appId=' . $apps[$componentName]['appId'];
        $url .= '&cluster=' . $apps[$componentName]['clusterName'];
        $url .= '&notifications=' . json_encode(array_values($notifications));
        $url .= '&AccessKeyId=' . $ak;
        $stringToSign = md5(microtime(true));
        $signature = base64_encode(hash_hmac('sha256', $stringToSign, $sk, true));
        $url .= '&StringToSign=' . $stringToSign;
        $url .= '&Signature=' . $signature;
        $res = file_get_contents($url);
        if ($res) {
            $res = json_decode($res, true);
            $change_notifications = array_column($res, 'notificationId', 'namespaceName');
            $service_url = [];
            foreach ($notifications as $key => $val) {
                if (isset($change_notifications[$val['namespaceName']])) {
                    app('apollo')->put($key . $componentName . '_notificationId', $change_notifications[$val['namespaceName']]);
                }
                $url = rtrim($componentUrl, '/') . '/configs/';
                $url .= $apps[$key]['appId'] . '/';
                $url .= $apps[$key]['clusterName'] . '/';
                $url .= $apps[$key]['namespaceName'] . '?';
                $url .= 'AccessKeyId=' . $ak;
                $stringToSign = md5(microtime(true));
                $signature = $this->percentEncode(base64_encode(hash_hmac('sha256', $stringToSign, $sk, true)));
                $url .= '&StringToSign=' . $stringToSign;
                $url .= '&Signature=' . $signature;
                $service_url[] = $url;
            }

            //并行通信
            $client = new Client();
            $requests = function ($option) use ($client) {
                foreach ($option as $url) {
                    $query = ['http_errors' => false, 'timeout' => 60];

                    yield function () use ($client, $url, $query) {
                        return $client->requestAsync('GET', $url, $query);
                    };
                }
            };
            $result = [];
            $pool = new Pool($client, $requests($service_url), [
                'concurrency' => count($service_url),
                'fulfilled'   => function ($response, $index) use ($service_url, &$result) {
                    $res = json_decode($response->getBody()->getContents(), true);
                    if ($res) {
                        $result[$index] = $res;
                    }
                },
                'rejected'    => function ($reason, $index) {
                },
            ]);
            $promise = $pool->promise();
            $promise->wait();

            if ($result) {
                $data = [];
                foreach ($result as $res) {
                    if (!empty($res['configurations'])) {
                        $data = array_merge_recursive($data, $res['configurations']);
                    }
                }
                if ($data) {
                    $oldData = app('apollo')->all();
                    $newData = array_merge(is_array($oldData) ? $oldData : [], [$serviceKey => $data]);
                    app('apollo')->putMany($newData);
                }
            }
        }
    }

    public function percentEncode($str)
    {
        $res = urlencode($str);
        $res = preg_replace('/\+/', '%20', $res);
        $res = preg_replace('/\*/', '%2A', $res);
        $res = preg_replace('/%7E/', '~', $res);
        return $res;
    }
}
