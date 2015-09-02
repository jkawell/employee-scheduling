<?php
namespace Spark\Project\Action;

use Aura\Payload\Payload;
use Spark\Adr\DomainInterface;
use Spark\Project\Domain\Entity\User;
use Spark\Project\Domain\Authenticator;

class UpdateShiftTimes implements DomainInterface
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
		
		$required_fields = ['shift_id','start_time','end_time'];
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
		$mapper = spot()->mapper('Spark\Project\Domain\Entity\Shift');
		
		$shift = $mapper->first(['id' => $input['shift_id']]);
		if (!$shift)
		{
			$output['error']['num'] = '811';
			$output['error']['msg'] = 'Invalid shift_id';
			$payload->setOutput($output);
			return $payload;
		}
		
		$start_datetime= new \DateTime($input['start_time']);
		$end_datetime = new \DateTime($input['end_time']);
		
		//first check to see if any of employees current shifts overlap with requested start/end
		$conflicting_shift = false;
		$conflicting_shifts = $mapper->getConflictingShifts($shift->employee_id, $start_datetime->date, $end_datetime->date);
		if ($conflicting_shifts && count($conflicting_shifts) > 0) foreach($conflicting_shifts as $tmp_shift)
		{
			if ($tmp_shift->id != $input['shift_id'])
			{
				$conflicting_shift = $tmp_shift;
				break;
			}
		}
		
		//return overlapping shift if it exists with error code, else add the shift
		if (false !== $conflicting_shift)
		{
			$output['error']['num'] = '201';
			$output['error']['msg'] = 'Conflicting Shift';
			$shift = $conflicting_shift;
		}
		else
		{
			try
			{
				$shift->start_time = $start_datetime;
				$shift->end_time = $end_datetime;
				$mapper->update($shift);
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