<?php
/**
 * Created by PhpStorm.
 * User: seven
 * Date: 2019/2/2
 * Time: 23:24
 */

namespace Tests\KickPeach\Queue\Jobs;

use KickPeach\Queue\Job;

class ExampleJob extends Job
{
    /**
     * @var string job queue name
     */
    public $queue = 'default';

    /**
     * @var int 允许worker执行的最大秒数，超时job将会被release到ready状态
     */
    public $retry_after  =60;

    /**
     * @var int 最大尝试次数
     */
    public $tries = 1;


    public $idArr;

    public function __construct(array $idArr)
    {
        $this->idArr = $idArr;
    }

    public function handle()
    {
        var_export($this->idArr);

        var_dump($this->retry_after,$this->tries);
    }
}