<?php

namespace Michaelrk02\SchedulerPhp\Test\Helpers;

use Michaelrk02\SchedulerPhp\ExecutorInterface;
use Michaelrk02\SchedulerPhp\Task;

class MailExampleExecutor implements ExecutorInterface
{
    public function getMaxExecutions()
    {
        return 5;
    }

    public function execute($task)
    {
        $data = $task->getData();

        echo 'Sending mail to '.$data['address'].' with contents: '.$data['contents'].PHP_EOL;

        return true;
    }

    public function shouldMergeTasks()
    {
        return true;
    }

    public function merge($existingTask, $insertedTask)
    {
        $existingData = $existingTask->getData();
        $insertedData = $insertedTask->getData();

        $mergedData = [];
        $mergedData['address'] = $insertedData['address'];
        $mergedData['contents'] = $existingData['contents'].'; '.$insertedData['contents'];

        return new Task(null, $insertedTask->getActionId(), $insertedTask->getGroupId(), $insertedTask->getPriority(), $insertedTask->getScheduleTime(), $mergedData);
    }
}
