<?php

namespace Michaelrk02\SchedulerPhp;

/**
 * Executor interface
 */
interface ExecutorInterface
{
    /**
     * Get the maximum number of executions per batch or `null` for unlimited
     * 
     * @return int|null
     */
    public function getMaxExecutions();

    /**
     * Execute the task
     * 
     * @param \Michaelrk02\SchedulerPhp\Task $task Task to execute
     * 
     * @return bool True indicates a successful execution, or false otherwise
     */
    public function execute($task);

    /**
     * Decides whether to merge tasks with the same group ID
     * 
     * @return bool
     */
    public function shouldMergeTasks();

    /**
     * Merge two tasks onto a single task
     * 
     * This function should return when `shouldMergeTasks()` method returns true
     * 
     * @param \Michaelrk02\SchedulerPhp\Task $existingTask Existing task already in the queue
     * @param \Michaelrk02\SchedulerPhp\Task $insertedTask Recently inserted task
     * 
     * @return \Michaelrk02\SchedulerPhp\Task|null Result of the task merging, or `null` if unsupported
     */
    public function merge($existingTask, $insertedTask);
}
