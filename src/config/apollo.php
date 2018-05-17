<?php

return [

    /*
    |--------------------------------------------------------------------------
    | 设置存储方式
    |--------------------------------------------------------------------------
    |
    | shm只支持linux下使用
    |
    | 支持: "redis", "shm"
    |
    */
    'default' => env('APOLLO_DRIVER', 'redis'),

    /*
    |--------------------------------------------------------------------------
    | 存储方式
    |--------------------------------------------------------------------------
    |
    | redis 需要先安装 "predis/predis": "^1.1"
    | shm 默认内存ID 1024，可以不用修改
    |
    */
    'stores'     => [
        'redis' => [
            'driver'   => 'redis',
            'host' => env('REDIS_HOST', '127.0.0.1'),
            'password' => env('REDIS_PASSWORD', null),
            'port' => env('REDIS_PORT', 6379),
            'database' => env('DATABASE', 10),
        ],
        'shm'   => [
            'driver' => 'shm',
            'id'     => 1024,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | 服务参数
    |--------------------------------------------------------------------------
    |
    | 需要设置好服务名、服务读取配置的key、获取服务地址的key
    |
    */
    'services'   => [
        /**
         * 在这里设置服务名
         *
         * 'ms-user', //用户服务
         * 'cn-css', //云安全
         * 'cn-grs', //grs服务
         */
        'service_names' => [
            'ms-user',
            'ms-billing'
        ],

        //当前服务的配置名,如 billing_apollo_config 必须唯一
        'service_key'   => 'billing_apollo_config',

        //服务地址的KEY, 如 server_url_cache
        'url_key'       => 'server_url_cache',
    ],

    /*
    |--------------------------------------------------------------------------
    | 注册中心
    |--------------------------------------------------------------------------
    |
    | 需要设置好服务名、服务读取配置的key、获取服务地址的key
    |
    */
    'consul'     => [
        'url'   => 'http://10.10.10.10:8500',
        'token' => 'xxxx-xxxx-xxxx-xxxx',
    ],

    /*
    |--------------------------------------------------------------------------
    | 配置中心
    |--------------------------------------------------------------------------
    |
    | 设置组件名，比如当前是 bill服务
    |
    */
    'components' => [
        //每个服务里的组件名，必须唯一
        'name' => 'bill',
        'iam'  => [
            'ak' => 'abcdefghijklmnopqrstuvwxyz',
            'sk' => 'abcdefghijklmnopqrstuvwxyz'
        ],
        //配置中心通信地址
        'url'  => 'http://10.10.10.10:8080',
        //参照配置中心，需要用到哪些就配置哪些
        'apps' => [
            //计费服务
            'bill'  => [
                //应用ID,参照配置中心
                'appId'         => 'ms-billing',
                //集群名
                'clusterName'   => 'abcd',
                'namespaceName' => 'application', //Namespace的名字
            ],
            //实际上只用到了namespaceName
            'common' => [
                'appId'             => 'common',
                'clusterName'       => 'wuxi1',
                'namespaceName'     => 'CHINAC.endpoint'
            ],
            //管用控、订单、计费服务通用配置
            'cpp'    => [
                'appId'             => 'common',
                'clusterName'       => 'wuxi1',
                'namespaceName'     => 'CHINAC.cppcommon'
            ]
        ]
    ]


];