<?php
/**
 * Created by PhpStorm.
 * User: seven
 * Date: 2019/2/2
 * Time: 15:12
 */

namespace KickPeach\Queue;

use KickPeach\Queue\Drivers\Contrasts\QueueInterface;

class Queue
{
    const TYPE = 'kickpeach';

    /**
     * @var QueueInterface
     */
    protected $driver;

    /**
     * @var string
     */
    protected $defaultQueue = 'default';

    public function __construct(QueueInterface $driver)
    {
        $this->driver = $driver;
    }

    /**
     * push a new job into the queue.
     * @param Job $job
     * @return mixed
     */
    public function push(Job $job)
    {
        return $this->driver->push(
            $job->queue,
            $this->createPayload($job),
            $job->retry_after
        );
    }

    /**
     * @param int $delay
     * @param Job $job
     * @return mixed
     */
    public function later($delay,Job $job)
    {
        return $this->driver->later(
            $delay,
            $job->queue,
            $this->createPayload($job),
            $job->retry_after);
    }

    /**
     * @param string $queue
     * @return null
     */
    public function pop($queue='')
    {
        return $this->driver->pop($this->getQueue($queue));
    }

    /**
     * get the queue or return the default
     * @param $queue
     * @return string
     */
    public function getQueue($queue)
    {
        return $queue ? $queue:$this->defaultQueue;
    }

    /**
     * create a payload string from the given job and data
     * @param Job $job
     * @return false|string
     */
    protected function createPayload(Job $job)
    {
        $payload = json_encode([
            'type'=>self::TYPE,
            'job'=>serialize(clone $job),
        ]);

        if (JSON_ERROR_NONE !== json_last_error()) {
            throw new \InvalidArgumentException('Unable to create payload');
        }

        return $payload;
    }



}