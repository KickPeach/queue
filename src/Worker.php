<?php
/**
 * Created by PhpStorm.
 * User: seven
 * Date: 2019/2/2
 * Time: 21:21
 */

namespace KickPeach\Queue;

use KickPeach\Queue\Drivers\Contrasts\Jobs\JobInterface;

class Worker
{
    /**
     * @var Queue
     */
    protected $queue;

    /**
     * @var string 需要处理的queue
     */
    protected $queueTube;

    public function __construct(Queue $queue,$queueTube = '')
    {
        $this->queue = $queue;
        $this->queueTube = $queueTube;
    }

    /**
     * listen to the given queue in a loop.
     * @param int $sleep 没有新的有效任务产生时的休眠时间（单位：秒）
     * @param int $memoryLimit 内存限制，单位（mb）
     * @throws \Exception
     */
    public function daemon($sleep =60,$memoryLimit = 128)
    {
        $memoryLimit = $memoryLimit * 1024*1024;
        $startTime = time();
        while(true){
            $ret = $this->runNextJob();
            if (!$ret) {
                sleep($sleep);
            }
            ($this->memoryExceeded($memoryLimit) || $this->queueShouldRestart($startTime)) && $this->stop();
        }
    }

    public function runNextJob()
    {
        $job = $this->queue->pop($this->queueTube);

        if ($job) {
            $payload = json_decode($job->getRawBody(),true);
            if (JSON_ERROR_NONE !== json_last_error()) {
                throw new \InvalidArgumentException('Unable to reate payload'.json_last_error_msg());
            }

            if (isset($payload['type']) && $payload['type']===Queue::TYPE) {
                $obj = unserialize($payload['job']);
                if($obj instanceof Job) {
                    $this->process($job,$obj);
                    return true;
                }
            }
            throw new \Exception('不能识别的Job类型，jobid'.$job->getJobId());
        }else{
            return false;
        }
    }

    /**
     * 对队列的bury监控，防止失败未处理的job过多
     * @param JobInterface $job
     * @param Job $obj job中反序列化后具体的实例对象，应用开发者定义的job
     */
    protected function process(JobInterface $job,Job $obj)
    {
        try{
            $obj->setJob($job);
            $this->handleWithObj($obj);
            $job->delete();
        }catch (\Exception $e) {
            if ($job->attempts()<$obj->tries) {
                $job->release();
            }else{
                $job->bury();
                $this->logProcessError($e);
            }
        }
    }

    /**
     * 执行具体任务，可以重载该方法
     * @param Job $obj
     */
    protected function handleWithObj(Job $obj)
    {
        $obj->handle();
    }

    protected function logProcessError(\Exception $e)
    {
        echo (string) $e."\n";
        return;
    }

    /**
     * Determine if the memory limit has been exceeded.
     * @param $memoryLimit
     * @return bool
     */
    protected function memoryExceeded($memoryLimit)
    {
        return memory_get_usage(true)>=$memoryLimit;
    }


    /**
     * override 采用cache存储，重启命令执行时间和worker $starttime 比较
     * @param $startTime
     * @return boolcai
     */
    protected function queueShouldRestart($startTime)
    {
        return false;
    }

    protected function stop()
    {
        throw new \Exception('queue worker stop with memoryExceed or shouldRestart,queue:'.$this->queueTube);
    }
}