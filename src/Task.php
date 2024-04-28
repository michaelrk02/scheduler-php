<?php

namespace Michaelrk02\SchedulerPhp;

use DateTime;
use Ramsey\Uuid\Uuid;

/**
 * Task class
 */
class Task
{
    /**
     * High task priority
     * 
     * @var int PRIORITY_HIGH
     */
    public const PRIORITY_HIGH = 3;

    /**
     * Normal task priority
     * 
     * @var int PRIORITY_NORMAL
     */
    public const PRIORITY_NORMAL = 2;

    /**
     * Low task priority
     * 
     * @var int PRIORITY_LOW
     */
    public const PRIORITY_LOW = 1;

    /**
     * @var string $id
     */
    protected $id;

    /**
     * @var string $actionId
     */
    protected $actionId;

    /**
     * @var string|null $groupId
     */
    protected $groupId;

    /**
     * @var int $priority
     */
    protected $priority;

    /**
     * @var \DateTime|null $scheduleTime
     */
    protected $scheduleTime;

    /**
     * @var array $data
     */
    protected $data;

    /**
     * Construct a task object
     * 
     * @param string|null $id Task identifier or `null` to autogenerate
     * @param string $actionId Action ID of the task corresponding to its executor
     * @param string|null $groupId Group ID of the task or `null` if ungrouped
     * @param int $priority Priority of the task in {@see \Michaelrk02\SchedulerPhp\Task} constants
     * @param \DateTime|null $scheduleTime Schedule time or `null` to execute immediately
     * @param array $data User-supplied data
     * 
     * @return self
     */
    public function __construct($id, $actionId, $groupId = null, $priority = Task::PRIORITY_NORMAL, $scheduleTime = null, $data = [])
    {
        if (!isset($id)) {
            $id = Uuid::uuid4()->toString();
        }
        $this->id = $id;

        $this->actionId = $actionId;
        $this->groupId = $groupId;
        $this->priority = $priority;

        if (!isset($scheduleTime)) {
            $scheduleTime = new DateTime();
        }
        $this->scheduleTime = $scheduleTime;

        $this->data = $data;
    }

    /**
     * Get the ID of the task
     * 
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get the action ID of the task
     * 
     * @return string
     */
    public function getActionId()
    {
        return $this->actionId;
    }

    /**
     * Get the group ID of the task
     * 
     * @return string|null
     */
    public function getGroupId()
    {
        return $this->groupId;
    }

    /**
     * Get the priority of the task
     * 
     * @return int
     */
    public function getPriority()
    {
        return $this->priority;
    }

    /**
     * Get the schedule time of the task
     * 
     * @return \DateTime|null
     */
    public function getScheduleTime()
    {
        return $this->scheduleTime;
    }

    /**
     * Get the user-supplied data for the task
     * 
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Convert this task to array representation
     * 
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->id,
            'action_id' => $this->actionId,
            'group_id' => $this->groupId,
            'priority' => $this->priority,
            'schedule_time' => $this->scheduleTime->format('c'),
            'data' => json_encode($this->data)
        ];
    }

    /**
     * Create a task from array representation
     * 
     * @return \Michaelrk02\SchedulerPhp\Task
     */
    public static function fromArray($array)
    {
        $id = $array['id'];
        $actionId = $array['action_id'];
        $groupId = $array['group_id'] === '' ? null : $array['group_id'];
        $priority = $array['priority'];
        $scheduleTime = $array['schedule_time'] !== null ? new DateTime($array['schedule_time']) : new DateTime();
        $data = json_decode($array['data'], true);

        return new Task($id, $actionId, $groupId, $priority, $scheduleTime, $data);
    }
}
