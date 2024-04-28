<?php

namespace Michaelrk02\SchedulerPhp\Test\Helpers;

use Michaelrk02\SchedulerPhp\ExecutorInterface;

class EchoExampleExecutor implements ExecutorInterface
{
    public function getMaxExecutions()
    {
        return null;
    }

    public function execute($task)
    {
        $data = $task->getData();
        echo $data['message'].PHP_EOL;

        return true;
    }

    public function shouldMergeTasks()
    {
        return false;
    }

    public function merge($existingTask, $insertedTask)
    {
        return null;
    }
}
