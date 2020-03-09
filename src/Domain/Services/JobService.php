<?php

namespace PhpBundle\Queue\Domain\Services;

use Illuminate\Support\Collection;
use PhpBundle\Queue\Domain\Entities\JobEntity;
use PhpBundle\Queue\Domain\Entities\TotalEntity;
use PhpBundle\Queue\Domain\Enums\PriorityEnum;
use PhpBundle\Queue\Domain\Interfaces\JobInterface;
use PhpBundle\Queue\Domain\Interfaces\Repositories\JobRepositoryInterface;
use PhpBundle\Queue\Domain\Interfaces\Services\JobServiceInterface;
use PhpBundle\Queue\Domain\Queries\NewTaskQuery;
use PhpLab\Core\Domain\Base\BaseService;
use PhpLab\Core\Domain\Helpers\EntityHelper;
use PhpLab\Core\Domain\Helpers\ValidationHelper;
use PhpLab\Core\Helpers\DiHelper;
use Psr\Container\ContainerInterface;

class JobService extends BaseService implements JobServiceInterface
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
        //$isAvailable = $this->beforeMethod([$this, 'push']);
        $jobEntity = new JobEntity;
        $jobEntity->setChannel('email');
        $jobEntity->setJob($job);
        $jobEntity->setPriority($priority);
        //$jobEntity->setDelay();
        ValidationHelper::validateEntity($jobEntity);
        $this->getRepository()->create($jobEntity);
        return $jobEntity;
    }

    public function newTasks(string $channel = null): Collection
    {
        $query = new NewTaskQuery($channel);
        $jobCollection = $this->getRepository()->all($query);
        return $jobCollection;
    }

    public function runAll(string $channel = null): TotalEntity
    {
        $query = new NewTaskQuery($channel);
        /** @var Collection | JobEntity[] $jobCollection */
        $jobCollection = $this->getRepository()->all($query);
        $totalEntity = new TotalEntity;
        foreach ($jobCollection as $jobEntity) {
            $job = $this->getJobInstance($jobEntity, $this->container);
            $jobEntity->incrementAttempt();
            try {
                $job->run();
                $jobEntity->setCompleted();
                $totalEntity->incrementSuccess($jobEntity);
            } catch (\Throwable $e) {
                $totalEntity->incrementFail($jobEntity);
            }
            $this->getRepository()->update($jobEntity);
        }
        return $totalEntity;
    }

    private function getJobInstance(JobEntity $jobEntity, ContainerInterface $container): JobInterface
    {
        $jobClass = $jobEntity->getClass();
        /** @var JobInterface $jobInstance */
        $jobInstance = DiHelper::make($jobClass, $container);
        $data = $jobEntity->getJob();
        EntityHelper::setAttributes($jobInstance, $data);
        return $jobInstance;
    }
}
