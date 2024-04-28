<?php

namespace Michaelrk02\SchedulerPhp\Test\Helpers;

use Michaelrk02\SchedulerPhp\Scheduler;

class ExampleScheduler extends Scheduler
{
    public function __construct()
    {
        parent::__construct(TEST_PDO_DSN, TEST_PDO_USER, TEST_PDO_PASS, TEST_QUEUE_TABLE);

        $this->addExecutor('echo', new EchoExampleExecutor());
        $this->addExecutor('mail', new MailExampleExecutor());
    }
}
