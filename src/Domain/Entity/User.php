<?php
namespace Spark\Project\Domain\Entity;

use Spot\EntityInterface as Entity;
use Spot\MapperInterface as Mapper;

class User extends \Spot\Entity
{
    protected static $table = 'user';
    protected static $mapper = 'Spark\Project\Domain\Entity\Mapper\User';

    public static function fields()
    {
        return [
            'id'           => ['type' => 'integer', 'autoincrement' => true, 'primary' => true],
            'name'         => ['type' => 'string', 'required' => true],
            'role'         => ['type' => 'string', 'required' => true],
            'email'        => ['type' => 'string', 'required' => true],
            'phone'        => ['type' => 'string', 'required' => true],
            'created_at'   => ['type' => 'datetime', 'value' => new \DateTime()],
            'updated_at'   => ['type' => 'datetime', 'value' => new \DateTime()]
        ];
    }

    public static function relations(Mapper $mapper, Entity $entity)
    {
        return [];
    }
}