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

    /** @var ContainerInterface */
    private $container;

    private function makeContainer(): ContainerInterface
    {
        $container = Container::getInstance();
        $container->bind(Manager::class, \PhpLab\Eloquent\Db\Helpers\Manager::class, true);
        $container->bind(JobRepositoryInterface::class, JobRepository::class, true);
        $container->bind(JobServiceInterface::class, JobService::class, true);
        $container->bind(ContainerInterface::class, Container::class, true);
        return $container;
    }

    private function clearQueue()
    {
        $jobRepository = $this->container->get(JobRepositoryInterface::class);
        $jobRepository->deleteByCondition([]);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->container = $this->makeContainer();
        $this->clearQueue();
    }

    public function testExample()
    {
        $jobService = $this->container->get(JobServiceInterface::class);

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

        $jobService->runAll('sms');

        try {
            $jobService->runAll('email');
            $this->assertTrue(false);
        } catch (AlreadyExistsException $e) {
            $this->assertEquals('qwerty', $e->getMessage());
        }
    }

}