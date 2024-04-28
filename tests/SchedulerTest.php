<?php

namespace Michaelrk02\SchedulerPhp\Test;

use DateInterval;
use DateTime;
use Michaelrk02\SchedulerPhp\Task;
use Michaelrk02\SchedulerPhp\Test\Helpers\ExampleScheduler;
use PDO;
use PHPUnit\Framework\TestCase;

class SchedulerTest extends TestCase
{
    private $scheduler;
    private $pdo;

    protected function setUp(): void
    {
        $this->scheduler = new ExampleScheduler();

        $this->pdo = new PDO(TEST_PDO_DSN, TEST_PDO_USER, TEST_PDO_PASS);
        $this->pdo->query('DELETE FROM `'.TEST_QUEUE_TABLE.'`');
    }

    public function testAddTasks()
    {
        $output = '';
        $output .= 'Sending mail to jim@example.com with contents: Hello, Jim!'.PHP_EOL;
        $output .= 'Sending mail to john@example.com with contents: Hello, John!'.PHP_EOL;

        $this->expectOutputString($output);

        $tasks = [
            [
                'id' => null,
                'action_id' => 'mail',
                'group_id' => null,
                'priority' => Task::PRIORITY_NORMAL,
                'schedule_time' => null,
                'data' => json_encode(['address' => 'jim@example.com', 'contents' => 'Hello, Jim!'])
            ],
            [
                'id' => null,
                'action_id' => 'mail',
                'group_id' => null,
                'priority' => Task::PRIORITY_NORMAL,
                'schedule_time' => null,
                'data' => json_encode(['address' => 'john@example.com', 'contents' => 'Hello, John!'])
            ]
        ];

        foreach ($tasks as $task) {
            $task = Task::fromArray($task);

            $this->scheduler->addTask($task);
        }

        $this->scheduler->run();
    }

    public function testMergeTasks()
    {
        $output = 'Sending mail to joe@example.com with contents: Hello, Joe!; Will you come to my birthday party?'.PHP_EOL;

        $this->expectOutputString($output);

        $tasks = [
            [
                'id' => null,
                'action_id' => 'mail',
                'group_id' => 'joe@example.com',
                'priority' => Task::PRIORITY_NORMAL,
                'schedule_time' => null,
                'data' => json_encode(['address' => 'joe@example.com', 'contents' => 'Hello, Joe!'])
            ],
            [
                'id' => null,
                'action_id' => 'mail',
                'group_id' => 'joe@example.com',
                'priority' => Task::PRIORITY_NORMAL,
                'schedule_time' => null,
                'data' => json_encode(['address' => 'joe@example.com', 'contents' => 'Will you come to my birthday party?'])
            ]
        ];

        foreach ($tasks as $task) {
            $task = Task::fromArray($task);
            $this->scheduler->addTask($task);
        }

        $this->scheduler->run();
    }

    public function testImportantTasks()
    {
        $output = '';
        $output .= 'Sending mail to jim@example.com with contents: You have an important meeting next hour'.PHP_EOL;
        $output .= 'Sending mail to jim@example.com with contents: Can we have a dinner tonight?'.PHP_EOL;
        $output .= 'Sending mail to jim@example.com with contents: Meet me at the restaurant'.PHP_EOL;

        $this->expectOutputString($output);

        $tasks = [
            [
                'id' => null,
                'action_id' => 'mail',
                'group_id' => null,
                'priority' => Task::PRIORITY_NORMAL,
                'schedule_time' => null,
                'data' => json_encode(['address' => 'jim@example.com', 'contents' => 'Can we have a dinner tonight?'])
            ],
            [
                'id' => null,
                'action_id' => 'mail',
                'group_id' => null,
                'priority' => Task::PRIORITY_NORMAL,
                'schedule_time' => null,
                'data' => json_encode(['address' => 'jim@example.com', 'contents' => 'Meet me at the restaurant'])
            ],
            [
                'id' => null,
                'action_id' => 'mail',
                'group_id' => null,
                'priority' => Task::PRIORITY_HIGH,
                'schedule_time' => null,
                'data' => json_encode(['address' => 'jim@example.com', 'contents' => 'You have an important meeting next hour'])
            ]
        ];

        foreach ($tasks as $task) {
            $task = Task::fromArray($task);

            $this->scheduler->addTask($task);
        }

        $this->scheduler->run();
    }

    public function testExecutionLimit()
    {
        $tasks = 10;
        $limit = 5;

        $output = '';
        for ($i = 0; $i < $limit; $i++) {
            $output .= 'Sending mail to jim@example.com with contents: This is the number '.($i + 1).' email'.PHP_EOL;
        }

        $this->expectOutputString($output);

        for ($i = 0; $i < $tasks; $i++) {
            $task = new Task(null, 'mail', null, Task::PRIORITY_NORMAL, null, ['address' => 'jim@example.com', 'contents' => 'This is the number '.($i + 1).' email']);

            $this->scheduler->addTask($task);
        }

        $this->scheduler->run();
    }

    public function testSpecificTasks()
    {
        $output = 'Hello world'.PHP_EOL;
        $this->expectOutputString($output);

        $tasks = [
            [
                'id' => null,
                'action_id' => 'mail',
                'group_id' => null,
                'priority' => Task::PRIORITY_NORMAL,
                'schedule_time' => null,
                'data' => json_encode(['address' => 'jim@example.com', 'contents' => 'Hello, Jim!'])
            ],
            [
                'id' => null,
                'action_id' => 'echo',
                'group_id' => null,
                'priority' => Task::PRIORITY_NORMAL,
                'schedule_time' => null,
                'data' => json_encode(['message' => 'Hello world'])
            ]
        ];

        foreach ($tasks as $task) {
            $task = Task::fromArray($task);

            $this->scheduler->addTask($task);
        }

        $this->scheduler->run('echo');
    }

    public function testScheduledTasks()
    {
        $output = '';
        $output .= 'Sending mail to jim@example.com with contents: This is a scheduled message'.PHP_EOL;

        $this->expectOutputString($output);

        $tasks = [
            [
                'id' => null,
                'action_id' => 'mail',
                'group_id' => null,
                'priority' => Task::PRIORITY_NORMAL,
                'schedule_time' => (new DateTime('+1 sec'))->format('c'),
                'data' => json_encode(['address' => 'jim@example.com', 'contents' => 'This is a scheduled message'])
            ]
        ];

        foreach ($tasks as $task) {
            $task = Task::fromArray($task);

            $this->scheduler->addTask($task);
        }

        $this->assertEquals(0, $this->scheduler->run());
        sleep(1);
        $this->assertEquals(1, $this->scheduler->run());
    }
}
