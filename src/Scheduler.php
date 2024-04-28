<?php

namespace Michaelrk02\SchedulerPhp;

use PDO;

/**
 * Scheduler class that can be inherited for customization
 */
class Scheduler
{
    /**
     * @var \PDO $pdo
     */
    protected $pdo;

    /**
     * @var string $queueTable
     */
    protected $queueTable;

    /**
     * @var \Michaelrk02\SchedulerPhp\DriverInterface $driver
     */
    protected $driver;

    /**
     * @var array<string, \Michaelrk02\SchedulerPhp\ExecutorInterface> $executors
     */
    protected $executors;

    /**
     * Construct the scheduler object
     * 
     * @param string $dsn Database connection string
     * @param string $username Database username
     * @param string $password Database password
     * @param string $queueTable Database table containing the queued tasks
     * 
     * @return self
     */
    public function __construct($dsn, $username, $password, $queueTable)
    {
        $this->pdo = new PDO($dsn, $username, $password);
        $this->queueTable = $queueTable;

        $driverName = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);
        $driverClass = [
            'mysql' => \Michaelrk02\SchedulerPhp\Drivers\MysqlDriver::class
        ];

        $this->driver = new $driverClass[$driverName]();
        $this->driver->initialize($this->pdo, $queueTable);

        $this->executors = [];
    }

    /**
     * Add a task executor for an action
     * 
     * @param string $actionId The identifier of the associated action
     * @param \Michaelrk02\SchedulerPhp\ExecutorInterface $executor Excecutor of the associated action
     * 
     * @return void
     */
    public function addExecutor($actionId, $executor)
    {
        $this->executors[$actionId] = $executor;
    }

    /**
     * Add a task to the queue
     * 
     * @param \Michaelrk02\SchedulerPhp\Task $task Task to add
     * 
     * @return void
     */
    public function addTask($task)
    {
        $actionId = $task->getActionId();
        $executor = $this->executors[$actionId];

        $this->driver->addTask($task, $executor);
    }

    /**
     * Run a single batch of execution in FIFO manner
     * 
     * @param string|null $actionId Whether to execute only tasks with this action ID, or `null` to execute all tasks
     * @param int|null $maxExecutions Maximum number of task executions in this batch or `null` to disable
     * 
     * @return int The number of successful executions
     */
    public function run($actionId = null, $maxExecutions = null)
    {
        $log = [];

        foreach ($this->driver->getTasks($actionId, $maxExecutions) as $task) {
            $actionId = $task->getActionId();
            $executor = $this->executors[$actionId];

            if (!array_key_exists($actionId, $log)) {
                $log[$actionId] = [
                    'max' => $executor->getMaxExecutions(),
                    'total' => 0,
                    'successful' => 0
                ];
            }

            if ($log[$actionId]['max'] !== null) {
                if ($log[$actionId]['total'] >= $log[$actionId]['max']) {
                    break;
                }
            }

            $log[$actionId]['total']++;
            if ($executor->execute($task)) {
                $log[$actionId]['successful']++;
            }

            $this->driver->deleteTask($task->getId());
        }

        $successful = 0;
        foreach ($log as $actionId => $execution) {
            $successful += $execution['successful'];
        }
        return $successful;
    }
}
