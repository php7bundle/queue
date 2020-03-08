<?php

namespace PhpBundle\Queue\Domain\Interfaces;

use Psr\Container\ContainerInterface;

interface JobInterface
{

    public function run();

    public function setContainer(ContainerInterface $container = null);

}