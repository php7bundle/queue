<?php

namespace PhpBundle\Queue\Tests\Unit;

use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager;
use PhpBundle\Queue\Domain\Interfaces\JobRepositoryInterface;
use PhpBundle\Queue\Domain\Interfaces\JobServiceInterface;
use PhpBundle\Queue\Domain\Repositories\Eloquent\JobRepository;
use PhpBundle\Queue\Domain\Services\JobService;
use PhpBundle\Queue\Tests\Libs\Jobs\ExampleJob;
use PhpLab\Core\Domain\Helpers\EntityHelper;
use PhpLab\Core\Exceptions\AlreadyExistsException;
use PhpLab\Test\Base\BaseTest;
use Psr\Container\ContainerInterface;

final class ExampleTest extends BaseTest
{

    private function makeContainer(): ContainerInterface
    {
        $container = Container::getInstance();

        $container->singleton(Manager::class, function (ContainerInterface $container) {
            $manager = new \PhpLab\Eloquent\Db\Helpers\Manager;
            return $manager;
        });

        $container->singleton(JobRepositoryInterface::class, function (ContainerInterface $container) {
            $manager = new JobRepository($container->get(Manager::class));
            return $manager;
        });

        $container->singleton(JobServiceInterface::class, function (ContainerInterface $container) {
            $jobService = new JobService($container->get(JobRepositoryInterface::class), $container);
            return $jobService;
        });

        return $container;
    }

    private function clearQueue(ContainerInterface $container)
    {
        $jobRepository = $container->get(JobRepositoryInterface::class);
        $jobRepository->deleteByCondition([]);
    }

    public function testExample()
    {

        $container = $this->makeContainer();
        $jobService = $container->get(JobServiceInterface::class);
        $this->clearQueue($container);

        $jobCollection = $jobService->all();
        $this->assertEmpty($jobCollection->all());

        $job = new ExampleJob;
        $job->messageText = 'qwerty';
        $pushResult = $jobService->push($job);

        $jobCollection = $jobService->all();

        $this->assertArraySubset([
            [
                'channel' => 'email',
                'class' => 'PhpBundle\\Queue\\Tests\\Libs\\Jobs\\ExampleJob',
                'data' => 'YToxOntzOjExOiJtZXNzYWdlVGV4dCI7czo2OiJxd2VydHkiO30=',
                'priority' => 200,
                'delay' => 0,
                'attempt' => 0,
            ],
        ], EntityHelper::collectionToArray($jobCollection));

        try {
            $jobService->runAll();
            $this->assertTrue(false);
        } catch (AlreadyExistsException $e) {
            $this->assertEquals('qwerty', $e->getMessage());
        }
    }

}