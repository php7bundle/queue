<?php

namespace PhpBundle\Queue\Domain\Interfaces;

use Illuminate\Support\Collection;
use PhpLab\Core\Domain\Libs\Query;
use PhpLab\Core\Domain\Interfaces\Repository\CrudRepositoryInterface;
use PhpBundle\Queue\Domain\Entities\JobEntity;

interface JobRepositoryInterface extends CrudRepositoryInterface
{

    /**
     * Выбрать невыполненные и зависшие задачи
     * @param Query|null $query
     * @return JobEntity[]
     */
    public function allForRun(Query $query = null): Collection;

}