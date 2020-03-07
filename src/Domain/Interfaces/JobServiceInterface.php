<?php

namespace PhpBundle\Queue\Domain\Interfaces;

use PhpBundle\Queue\Domain\Enums\PriorityEnum;

interface JobServiceInterface
{

    public function push(JobInterface $job, int $priority = PriorityEnum::NORMAL);

    public function runAll(string $channel = null): int;

}