## Apollo config manage
- 方便程序快速获取配置参数
- 支持`redis`、`SHM`、`file` 存储方式
- `SHM` 只支持linux下使用
- Laravel 5.5+

## Install
```
composer require totoro/apollo-config
```

#### In Laravel
- 在 `config/app.php` 
```
'providers' => [
    ...
    ...
    Totoro\Apollo\ApolloServiceProvider::class,
]
```

#### In Lumen
- 在 `bootstrap/app.php` 
```
$app->configure('apollo');
$app->register(Totoro\Apollo\ApolloServiceProvider::class);
```


## Usage

```
//值是否存在
app('apollo')->has($key)

//获取某个值
app('apollo')->get($key)

//获取所有值
app('apollo')->all()

```

## 重要说明

`APOLLO_DRIVER` 为 `file` 时只要很简单的配置就行
 
 

## Configuration

- 执行 `php artisan apollo:publish-config` 将配置文件发布到 (`config/apollo.php`)

### 当 `APOLLO_DRIVER`设置为 `redis` 或者 `shm` 时
- 配置文件根据部署的参数设置好
- 执行 `php artisan apollo:publish-consul` 将注册中心的服务地址发布到存储库中
- 执行 `php artisan apollo:publish-component` 将配置中心参数发布到存储库中
- 可以执行 `php artisan apollo:clear-apollo` 清空当前存储库


#### Cron

在 `app/Console/Kerner.php`中加上定时任务调用
```
protected $commands = [
    ...
    ...
    PublishConsulCommand::class,
    PublishComponentCommand::class
];
    
protected function schedule(Schedule $schedule)
{
    ...
    ...
    
    //更新注册中心服务地址
    $schedule->command(PublishConsulCommand::class)->everyMinute();
    //更新配置中心单当前服务参数
    $schedule->command(PublishComponentCommand::class)->everyMinute();
 }
```

- 启动调度器
```
* * * * * php /your-project-path/laravel/artisan schedule:run >> /dev/null 2>&1 
```

### 当 `APOLLO_DRIVER`设置为 `file`  时

- `.env` 中设置配置文件的路径，默认 `/etc/xultra/php_conf`，当然也可以只直接在`config/apollo.php`中设置
```
APOLLO_DRIVER=file
APOLLO_CONF=/etc/xultra/php_conf
```
- 在配置文件 `/etc/xultra/php_conf` 中设置参数
```
xu-billing=http://127.0.0.1:1000
xu-audit=http://127.0.0.1000
```
