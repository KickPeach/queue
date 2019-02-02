<?php
/**
 * Created by PhpStorm.
 * User: seven
 * Date: 2019/2/2
 * Time: 11:17
 */

namespace KickPeach\Queue\Drivers\Contrasts\Jobs;

use Pheanstalk\PheanstalkInterface;
use Pheanstalk\Job as PheanstalkeJob;

class BeanstalkdJob implements JobInterface
{
    /**
     * @var  PheanstalkInterface
     */
    protected $pheanstalk;

    /**
     * @var PheanstalkeJob
     */
    protected $job;

    /**
     * @var string
     */
    protected $queue;
    public function __construct(PheanstalkInterface $pheanstalk,PheanstalkeJob $job,$queue)
    {
        $this->pheanstalk = $pheanstalk;
        $this->job = $job;
        $this->queue = $queue;
    }

    public function getRawBody()
    {
        return $this->job->getData();
    }

    public function release($delay = PheanstalkInterface::DEFAULT_DELAY)
    {
        $this->pheanstalk->release($this->job,PheanstalkInterface::DEFAULT_PRIORITY);
    }

    public function bury()
    {
        $this->pheanstalk->bury($this->job);
    }

    public function attempts()
    {
        $stats = $this->pheanstalk->statsJob($this->job);
        return (int)$stats->reserves;
    }

    public function getJobId()
    {
        return $this->job->getId();
    }

    public function getPheanstalk()
    {
        return $this->pheanstalk;
    }

    public function getPheanstalkJob()
    {
        return $this->job;
    }

    public function delete()
    {
        $this->pheanstalk->delete($this->job);
    }


}