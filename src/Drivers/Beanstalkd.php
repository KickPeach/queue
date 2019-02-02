<?php
/**
 * Created by PhpStorm.
 * User: seven
 * Date: 2019/2/2
 * Time: 11:32
 */

namespace KickPeach\Queue\Drivers;

use KickPeach\Queue\Drivers\Contrasts\Jobs\JobInterface;
use KickPeach\Queue\Drivers\Contrasts\QueueInterface;
use Pheanstalk\Pheanstalk;
use Pheanstalk\Job as PheanstalkJob;
use Pheanstalk\PheanstalkInterface;
use KickPeach\Queue\Drivers\Contrasts\Jobs\BeanstalkdJob;

class Beanstalkd implements QueueInterface
{
    protected $pheanstalk;

    public function __construct($host,$port = PheanstalkInterface::DEFAULT_PORT)
    {
       $this->pheanstalk = new Pheanstalk($host,$port);
    }

    /**
     * 立即分发到队列
     *
     * @param $queue
     * @param $payload
     * @param int|Contrasts\Time $ttr
     * @return int
     */
    public function push($queue,$payload,$ttr = PheanstalkInterface::DEFAULT_TTR)
    {
        return $this->pushRaw($queue,$payload,$ttr);
    }

    /**
     * @param   $delay
     * @param $queue
     * @param $payload
     * @param $ttr
     * @return int
     */
    public function later($delay, $queue, $payload, $ttr)
    {
        return $this->pushRaw($queue,$payload,$ttr,$delay);
    }

    /**
     * push a payload into the queue
     * @param string $queue 队列名
     * @param string $payload job内容
     * @param int $ttr 允许worker执行的最大秒数
     * @param int $delay 延迟read的秒数
     * @param int $priority 优先级
     * @return int
     *
     *  如果worker在这段时间不能delete，release，bury job，那么job超时，服务器将release此job，此job的状态迁移为ready。
     *  最小为1秒，如果客户端指定为0将会被重置为1。
     */
    public function pushRaw(
        $queue,
        $payload,
        $ttr = PheanstalkInterface::DEFAULT_TTR,
        $delay = PheanstalkInterface::DEFAULT_DELAY,
        $priority = PheanstalkInterface::DEFAULT_PRIORITY
    )
    {
        if (empty($ttr)) {
            $ttr = PheanstalkInterface::DEFAULT_TTR;
        }

        return $this->pheanstalk
            ->useTube($queue)
            ->put($payload,$priority,$delay,$ttr);
    }


    /**
     * @param $queue
     * @return JobInterface|null
     */
    public function pop($queue)
    {
        $job = $this->pheanstalk->watchOnly($queue)->reserve(0);

        if ($job instanceof PheanstalkJob) {
            return new BeanstalkdJob($this->pheanstalk,$job,$queue);
        }

        return null;
    }


}