<?php

namespace PhpBundle\Queue\Domain\Jobs;

use Symfony\Component\DependencyInjection\ContainerAwareTrait;

abstract class BaseJob
{

    use ContainerAwareTrait;

}