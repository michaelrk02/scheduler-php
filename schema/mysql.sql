CREATE TABLE `task_queue` (
    `id` CHAR(36) NOT NULL,

    `timestamp` DATETIME(6) NOT NULL DEFAULT (SYSDATE(6)),
    `action_id` VARCHAR(128) NOT NULL,
    `group_id` VARCHAR(256) NOT NULL,
    `priority` INT NOT NULL,
    `schedule_time` DATETIME NOT NULL,
    `data` TEXT,

    PRIMARY KEY (`id`),
    INDEX `IX_TaskQueue_Priority_Timestamp` (`priority` DESC, `timestamp` ASC),
    INDEX `IX_TaskQueue_Action_Group` (`action_id`, `group_id`)
);
