<?php
/**
 * Created by PhpStorm.
 * User: seven
 * Date: 2019/2/2
 * Time: 10:54
 */

namespace KickPeach\Queue\Drivers\Contrasts;


interface QueueInterface
{
    /**
     * 立即分发到队列
     * @param $queue
     * @param $payload
     * @param $ttr Time To Run:retry_after 允许worker执行的最大秒数
     * @return int
     */
    public function push($queue,$payload,$ttr);

    /**
     * 延时分发到队列
     * @param $delay 延迟read的秒数，在这段时间job为delayed状态
     * @param $queue
     * @param $payload
     * @param $ttr
     * @return int
     */
    public function later($delay,$queue,$payload,$ttr);


    /**
     * 从队列中取出任务
     * @param $queue
     * @return null
     */
    public function pop($queue);
}