# queue

基于beanstalkd实现的任务队列，方便去分发任务和解决任务,此库可适用以下场景：

- 用作延时队列：比如可以用于如果用户30分钟内不操作，任务关闭。
- 用作定时任务：比如可以用于专门的后台任务。
- 用作异步操作：这是所有消息队列都最常用的，先将任务仍进去，顺序执行。
- 用作循环队列：用release命令可以循环执行任务，比如可以做负载均衡任务分发。
- 用作兜底机制：比如一个请求有失败的概率，可以用Beanstalk不断重试，设定超时时间，时间内尝试到成功为止

## 关于Beanstalkd
Beanstalkd 是一个轻量级的内存型队列，利用了和 Memcache 类似的协议。依赖 libevent 单线程事件分发机制, 可以部署多个实例，但是高并发支持还是不太友好；

## 几个重要的概念

- job：一个需要异步处理的任务，是 Beanstalkd 中的基本单元，需要放在一个 tube 中。
- tube：一个有名的任务队列，用来存储统一类型的 job，是 producer 和 consumer 操作的对象。
- producer：Job 的生产者，通过 put 命令来将一个 job 放到一个 tube 中。
- consumer：Job的消费者，通过 reserve/release/bury/delete 命令来获取 job 或改变 job 的状态。


### Job的生命周期

任务在队里之中被称作 Job. 一个 Job 在 Beanstalkd 中有以下的生命周期：

- put 将一个任务放置进 tube 中
- deayed 这个任务现在再等待中，需要若干秒才能准备完毕【延迟队列】
- ready 这个任务已经准备好了，可以消费了。所有的消费都是要从取 ready 状态的 job
- reserved 这个任务已经被消费者消费
- release 这个 job 执行失败了，把它放进 ready 状态队列中。让其他队列执行
- bury 这个 job 执行失败了，但不希望其他队列执行，先把它埋起来

```php
    
     put with delay               release with delay
      ----------------> [DELAYED] <------------.
                            |                   |
                            | (time passes)     |
                            |                   |
       put                  v     reserve       |       delete
      -----------------> [READY] ---------> [RESERVED] --------> *poof*
                           ^  ^                |  |
                           |   \  release      |  |
                           |    `-------------'   |
                           |                      |
                           | kick                 |
                           |                      |
                           |       bury           |
                        [BURIED] <---------------'
                           |
                           |  delete
                            `--------> *poof*
```


## 怎么使用

具体实例可参考[测试例子](https://github.com/KickPeach/queue/blob/master/tests/QueueTest.php)

### 安装

`composer require kickpeach/queue -vvv`

### Overview

* [Create Queue](#create-queue)
* [Create Job](#create-job)
* [Dispatch Job](#dispatch-job)
* [Process Job](#process-job)
* [Recommend](#recommend)
* [Example](#example)
* [Todo](#todo)

### Create Queue

```
use KickPeach\Queue\Drivers\Beanstalkd;
use KickPeach\Queue\Queue;

$queue = new Queue(new Beanstalkd($host, $port));
```

### Create Job

```
<?php

use KickPeach\Queue\Job;

class ExampleJob extends Job
{
    /**
     * @var string job queue name (beanstalkd tube)
     */
    public $queue = 'default';

    /**
     * The "time to run" for all pushed jobs. (beanstalkd ttr, timeout)
     *
     * @var int 允许 worker 执行的最大秒数,超时 job 将会被 release 到 ready 状态.
     */
    public $retry_after = 60;

    /**
     * The number of times the job may be attempted.
     *
     * @var int 最大尝试次数
     */
    public $tries = 1;

    /**
     * @var array
     */
    public $words;

    public function __construct(array $words)
    {
        $this->words = $words;
    }

    public function handle()
    {
        var_export($this->words);

        var_dump($this->retry_after, $this->tries);

        // throw new \Exception('handle job with error...lol ^_^');
    }
}
```

specifying job queue by defining `$queue` , specifying Max Job Attempts by defining `$tries` , specifying timeout Values by defining `$retry_after` .

### Dispatch Job

```
$queue->push(new ExampleJob(['i', 'love', 'china']));
```

of cause, you can dispatch job later (push a delayed job) :

```
$queue->later(60, new ExampleJob(['i', 'love', 'china']));
```

### Process Job

```
$worker = new Worker($queue);

$worker->daemon();
```

> Note: `$worker->daemon()` is blocking.

by default, the worker will will listen  the tube named `default`, you can specifying worker queue (beanstalkd tube) like :

```
$queueTube = 'sendEmail';
$worker = new Worker($queue, $queueTube);

$worker->daemon();
```

you can specifying worker with sleep time while there is no job, and memoryLimit, like :

```
$sleep = 60;
$memoryLimit = 128;

$queueTube = 'sendEmail';
$worker = new Worker($queue, $queueTube);

$worker->daemon($sleep, $memoryLimit);
```


# License
The MIT License (MIT).


