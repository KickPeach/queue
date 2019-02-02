<?php
/**
 * Created by PhpStorm.
 * User: seven
 * Date: 2019/2/2
 * Time: 11:02
 */

namespace KickPeach\Queue\Drivers\Contrasts\Jobs;

use Pheanstalk\PheanstalkInterface;

interface JobInterface
{
    /**
     * 获取任务
     * @return string
     */
    public function getRawBody();


    /**
     * release the job back into the queue
     * @param int $delay
     * @return void
     */
    public function release($delay = PheanstalkInterface::DEFAULT_DELAY);

    /**
     * bury the job in the queue
     * @return void
     */
    public function bury();

    /**
     * get the number of times the has been attempted
     * @return int
     */
    public function attempts();

    /**
     * get the job identifier
     * @return string
     */
    public function getJobId();

}