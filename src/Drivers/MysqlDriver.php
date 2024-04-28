<?php

namespace Michaelrk02\SchedulerPhp\Drivers;

use DateTime;
use Michaelrk02\SchedulerPhp\DriverInterface;
use Michaelrk02\SchedulerPhp\Task;
use PDO;

class MysqlDriver implements DriverInterface
{
    protected $pdo;
    protected $queueTable;

    public function initialize($pdo, $queueTable)
    {
        $this->pdo = $pdo;
        $this->queueTable = $queueTable;
    }

    public function getTasks($actionId, $count)
    {
        $whereClause = 'WHERE CURRENT_TIMESTAMP >= `schedule_time`';
        $whereClause .= $actionId !== null ? ' AND `action_id` = '.$this->pdo->quote($actionId) : '';

        $limitClause = $count !== null ? 'LIMIT '.$count : '';
        $query = 'SELECT * FROM `'.$this->queueTable.'` '.$whereClause.' ORDER BY `priority` DESC, `timestamp` ASC '.$limitClause;

        $tasks = [];
        foreach ($this->pdo->query($query) as $row) {
            $row['schedule_time'] = (new DateTime($row['schedule_time']))->format('c');
            $task = Task::fromArray($row);
            $tasks[] = $task;
        }
        return $tasks;
    }

    public function addTask($task, $executor)
    {
        if (($task->getGroupId() !== null) && ($executor->shouldMergeTasks())) {
            $existingQuery = 'SELECT * FROM `'.$this->queueTable.'` WHERE `action_id` = '.$this->pdo->quote($task->getActionId()).' AND `group_id` = '.$this->pdo->quote($task->getGroupId()).'';
            $existing = $this->pdo->query($existingQuery)->fetch(PDO::FETCH_ASSOC);
            if ($existing !== false) {
                $existing['schedule_time'] = (new DateTime($existing['schedule_time']))->format('c');
                $existing = Task::fromArray($existing);
                $task = $executor->merge($existing, $task);

                $this->deleteTask($existing->getId());
            }
        }

        $query = 'INSERT INTO `'.$this->queueTable.'` (`id`, `action_id`, `group_id`, `priority`, `schedule_time`, `data`) VALUES (:id, :action_id, :group_id, :priority, :schedule_time, :data)';
        $row = $task->toArray();
        if ($row['group_id'] === null) {
            $row['group_id'] = '';
        }
        $row['schedule_time'] = (new DateTime($row['schedule_time']))->format('Y-m-d H:i:s');

        $this->pdo->prepare($query)->execute($row);

        return $task->getId();
    }

    public function deleteTask($taskId)
    {
        $this->pdo->query('DELETE FROM `'.$this->queueTable.'` WHERE `id` = '.$this->pdo->quote($taskId));
    }

}
