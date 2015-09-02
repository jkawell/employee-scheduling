<?php
namespace Spark\Project\Domain\Entity;

use Spot\EntityInterface as Entity;
use Spot\MapperInterface as Mapper;

class Shift extends \Spot\Entity
{
    protected static $table = 'shift';
    protected static $mapper = 'Spark\Project\Domain\Entity\Mapper\Shift';

    public static function fields()
    {
        return [
            'id'           => ['type' => 'integer', 'autoincrement' => true, 'primary' => true],
            'manager_id'   => ['type' => 'integer', 'required' => true],
            'employee_id'  => ['type' => 'integer', 'required' => true],
            'break'        => ['type' => 'float'],
            'start_time'   => ['type' => 'datetime', 'value' => new \DateTime()],
            'end_time'     => ['type' => 'datetime', 'value' => new \DateTime()],
            'created_at'   => ['type' => 'datetime', 'value' => new \DateTime()],
            'updated_at'   => ['type' => 'datetime', 'value' => new \DateTime()]
        ];
    }

    public static function relations(Mapper $mapper, Entity $entity)
    {
        return [
            'employee' => $mapper->belongsTo($entity, 'Spark\Project\Domain\Entity\User', 'employee_id'),
            'manager' => $mapper->belongsTo($entity, 'Spark\Project\Domain\Entity\User', 'manager_id')
        ];
    }
	
	public function getAPIOutput($return_attrs = [])
	{
        if (!is_array($return_attrs) || count($return_attrs) == 0)
        {
            $return_attrs = ['id','manager_id','employee_id','break','start_time','end_time'];
        }
        foreach($return_attrs as $attr)
        {
			if (in_array($attr, ['start_time', 'end_time']))
			{
                $return[$attr] = date(\DateTime::RFC2822, strtotime($this->$attr->date));
			}
            else
            {
                $return[$attr] = $this->$attr;
            }
        }
        return $return;
	}
}