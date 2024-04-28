<?php

namespace Michaelrk02\SchedulerPhp;

/**
 * Database driver interface
 */
interface DriverInterface
{
    /**
     * Initialize the driver with PDO and queue table
     * 
     * @param \PDO $pdo Database connection
     * @param string $queueTable Database table containing the queue
     * 
     * @return void
     */
    public function initialize($pdo, $queueTable);


    /**
     * Get the next tasks to execute
     * 
     * @param string|null $actionId Action ID to filter, or `null` for all actions
     * @param int|null $count The number of tasks to get, or `null` for unlimited
     * 
     * @return \Michaelrk02\SchedulerPhp\Task[] Array of tasks
     */
    public function getTasks($actionId, $count);

    /**
     * Add a task to the queue
     * 
     * This function is also responsible for task merging
     * 
     * @param \Michaelrk02\SchedulerPhp\Task $task Task to add
     * @param \Michaelrk02\SchedulerPhp\ExecutorInterface $executor Executor associated to this task. Use this interface to merge tasks
     * 
     * @return string ID of the added task
     */
    public function addTask($task, $executor);

    /**
     * Delete a task with specified task ID
     * 
     * @param string $taskId ID of the task to delete
     * 
     * @return void
     */
    public function deleteTask($taskId);
}
