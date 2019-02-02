<?php
/**
 * Created by PhpStorm.
 * User: seven
 * Date: 2019/2/2
 * Time: 15:18
 */

namespace KickPeach\Queue;


use KickPeach\Queue\Drivers\Contrasts\Jobs\JobInterface;

abstract class Job
{
    public $queue = 'default';

    public $retry_after = 60;

    public $tries = 1;

    abstract public function handle();

    protected $job;

    public function setJob(JobInterface $job)
    {
        $this->job = $job;
        return $this;
    }

    public function attempts()
    {
        return $this->job->attempts();
    }

    public function delete()
    {
        $this->job->delete();
    }

    public function release($delay=0)
    {
        $this->job->release($delay);
    }

    public function bury()
    {
        $this->job->bury();
    }



}