<?php

namespace PhpBundle\Queue\Domain\Jobs;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;

abstract class BaseJob implements ContainerAwareInterface
{

    use ContainerAwareTrait;

}