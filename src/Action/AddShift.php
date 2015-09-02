<?php
namespace Spark\Project\Action;

use Aura\Payload\Payload;
use Spark\Adr\DomainInterface;
use Spark\Project\Domain\Entity\User;
use Spark\Project\Domain\Authenticator;

class AddShift implements DomainInterface
{
    public function __invoke(array $input)
    {
		$output['shift'] = [];
        $payload = new Payload();
        $payload->setStatus(Payload::FOUND);
        
        if (empty($input['token']) || !Authenticator::validateManager($input['token']))
		{
			$output['error']['num'] = '101';
			$output['error']['msg'] = 'Invalid Token';
			$payload->setOutput($output);
			return $payload;
		}
		
		$required_fields = ['manager_id','employee_id','start_time','end_time'];
		foreach($required_fields as $field)
		{
			if (empty($input[$field]))
			{
				$output['error']['num'] = '801';
				$output['error']['msg'] = $field.' must be specified';
				$payload->setOutput($output);
				return $payload;
			}
		}
        $start_datetime = new \DateTime($input['start_time']);
        $end_datetime = new \DateTime($input['end_time']);
		$mapper = spot()->mapper('Spark\Project\Domain\Entity\Shift');
		
		//first check to see if any of employees current shifts overlap with requested start/end
		$conflicting_shifts = $mapper->getConflictingShifts($input['employee_id'], $start_datetime->date, $end_datetime->date);
		
		//return overlapping shift if it exists with error code, else add the shift
		if ($conflicting_shifts && count($conflicting_shifts) > 0)
		{
			$output['error']['num'] = '201';
			$output['error']['msg'] = 'Conflicting Shift';
			$shift = $conflicting_shifts[0];
		}
		else
		{
			$shift_to_add = [
				'manager_id' => $input['manager_id'],
				'employee_id' => $input['employee_id'],
				'start_time' => $start_datetime,
				'end_time' => $end_datetime
			];
			if (!empty($input['break'])) $shift_to_add['break'] = $input['break'];
			try
			{
				$shift = $mapper->create($shift_to_add);
			}
			catch(\Exception $e)
			{
				$output['error']['num'] = '999';
				$output['error']['msg'] = $e->getMessage();
				$payload->setOutput($output);
				return $payload;
			}
		}
		
		$output['shift'] = $shift->getAPIOutput();
		if (empty($output['error']['num']))
		{
			$output['error']['num'] = '000';
			$output['error']['msg'] = 'SUCCESS';
		}
        $payload->setOutput($output);
		
        return $payload;
    }
}