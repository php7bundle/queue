<?php

namespace PhpBundle\Queue\Domain\Helpers;

use PhpLab\Core\Domain\Helpers\EntityHelper;
use PhpBundle\Queue\Domain\Entities\JobEntity;
use PhpBundle\Queue\Domain\Interfaces\JobInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class JobHelper
{

    public static function forgeJob(JobEntity $jobEntity, ContainerInterface $container = null): JobInterface
    {
        $jobClass = $jobEntity->getClass();
        /** @var JobInterface $jobInstance */
        $jobInstance = new $jobClass;
        $data = $jobEntity->getJob();
        if ($container) {
            $data['container'] = $container;
        }
        EntityHelper::setAttributes($jobInstance, $data);
        return $jobInstance;
    }

    public static function encode(object $job): string
    {
        $data = get_object_vars($job);
        $serializedData = serialize($data);
        $base64Data = base64_encode($serializedData);
        return $base64Data;
        //$this->setData($base64Data);
        //$this->setClass(get_class($job));
    }

    public static function decode(string $data)
    {
        $serializedData = base64_decode($data);
        $job = unserialize($serializedData);
        return $job;
    }

}