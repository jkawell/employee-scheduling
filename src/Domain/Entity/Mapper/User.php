<?php
namespace Spark\Project\Domain\Entity\Mapper;

use Spot\Mapper;

class User extends Mapper
{
    /**
     * Get 10 most recent shifts for this employee
     *
     * @return \Spot\Query
     */
    public function getAllShifts()
    {
        return $this->where(['id' => $this->id])
            ->order(['date_created' => 'DESC'])
            ->limit(10);
    }
}