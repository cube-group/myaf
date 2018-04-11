## LQueue-是对于amqp类库的初级封装
### LRabbitMQ Demo
```
use \Base\Queue\LQueue;

$queue = new LRabbitMQ([
    'host' => '127.0.0.1',
    'port' => '5672',
    'login' => 'guest',
    'password' => 'guest',
    'database' => 'exchange_name',
    'vhost' => '/'
]);
//生产
$bool = $queue->product('hello','channel_router_key');
var_dump($bool);
//消费
$message = $queue->consume('channel_router_key');
//处理消费数据...
//最近一条消费数据是否在业务上成功,false则不会删除这条数据,true会删除
$queue->consumeStatus(true);//or false
```
### LRedisMQ Demo
```
use \Base\Queue\LQueue;

$queue = new LRedisMQ([
    'host' => '127.0.0.1',
    'port' => '5672',
    'password' => 'guest',
    'database' => '0',
]);
//生产
$bool = $queue->product('hello','list_key');
var_dump($bool);
//消费
$message = $queue->consume('list_key');
//处理消费数据...
//最近一条消费数据是否在业务上成功,false则会将刚刚的数据再次lPush
$queue->consumeStatus(false);//or true
```
### Any Question?