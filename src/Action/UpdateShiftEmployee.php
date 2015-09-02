<?php
namespace Spark\Project\Action;

use Aura\Payload\Payload;
use Spark\Adr\DomainInterface;
use Spark\Project\Domain\Entity\User;
use Spark\Project\Domain\Authenticator;

class UpdateShiftEmployee implements DomainInterface
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
		
		$required_fields = ['shift_id','employee_id'];
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
		
		$user_mapper = spot()->mapper('Spark\Project\Domain\Entity\User');
		$user = $user_mapper->first(['id' => $input['employee_id']]);
		if (!$user)
		{
			$output['error']['num'] = '812';
			$output['error']['msg'] = 'Invalid employee_id';
			$payload->setOutput($output);
			return $payload;
		}
		
		try
		{
			$shift->employee_id = $input['employee_id'];
			$mapper->update($shift);
		}
		catch(\Exception $e)
		{
			$output['error']['num'] = '999';
			$output['error']['msg'] = $e->getMessage();
			$payload->setOutput($output);
			return $payload;
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