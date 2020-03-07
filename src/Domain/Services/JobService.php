<?php

namespace PhpBundle\Queue\Domain\Services;

use PhpLab\Core\Domain\Helpers\EntityHelper;
use PhpLab\Core\Domain\Base\BaseCrudService;
use PhpLab\Core\Domain\Helpers\ValidationHelper;
use PhpBundle\Queue\Domain\Entities\JobEntity;
use PhpBundle\Queue\Domain\Enums\PriorityEnum;
use PhpBundle\Queue\Domain\Helpers\JobHelper;
use PhpBundle\Queue\Domain\Interfaces\JobInterface;
use PhpBundle\Queue\Domain\Interfaces\JobRepositoryInterface;
use PhpBundle\Queue\Domain\Interfaces\JobServiceInterface;
use Psr\Container\ContainerInterface;

class JobService extends BaseCrudService implements JobServiceInterface
{

    protected $container;

    public function __construct(JobRepositoryInterface $repository, ContainerInterface $container)
    {
        $this->repository = $repository;
        $this->container = $container;
    }

    public function getRepository(): JobRepositoryInterface
    {
        return parent::getRepository();
    }

    public function push(JobInterface $job, int $priority = PriorityEnum::NORMAL)
    {
        $isAvailable = $this->beforeMethod([$this, 'push']);
        $jobEntity = new JobEntity;
        $jobEntity->setChannel('email');
        $jobEntity->setJob($job);
        $jobEntity->setPriority($priority);
        //$jobEntity->setDelay();
        ValidationHelper::validateEntity($jobEntity);
        $this->getRepository()->create($jobEntity);
        return $jobEntity;
    }

    public function runAll(string $channel = null): int
    {
        $jobCollection = $this->getRepository()->allForRun();
        foreach ($jobCollection as $jobEntity) {
            $job = JobHelper::forgeJob($jobEntity, $this->container);
            $job->run();
            $jobEntity->setReservedAt();
            $jobEntity->setDoneAt();
            $this->getRepository()->update($jobEntity);
        }
        return $jobCollection->count();
    }

}
