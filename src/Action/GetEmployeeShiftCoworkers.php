<?php
namespace Spark\Project\Action;

use Aura\Payload\Payload;
use Spark\Adr\DomainInterface;
use Spark\Project\Domain\Entity\User;
use Spark\Project\Domain\Authenticator;

class GetEmployeeShiftCoworkers implements DomainInterface
{

    public function __invoke(array $input)
    {
		$output['coworkers_shifts'] = [];
        $payload = new Payload();
        $payload->setStatus(Payload::FOUND);
        
        if (empty($input['token']) || !Authenticator::validateEmployee($input['token']))
		{
			$output['error']['num'] = '101';
			$output['error']['msg'] = 'Invalid Token';
			$payload->setOutput($output);
			return $payload;
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
        
        $coworkers_shifts = $mapper->getCoworkerShifts($shift->id);
		$return_attrs = ['user_id','name','start_time','end_time'];
        foreach($coworkers_shifts as $shift)
        {
			$output['coworkers_shifts'][] = $shift->getAPIOutput($return_attrs);
        }
		
		if (empty($output['error']['num']))
		{
			$output['error']['num'] = '000';
			$output['error']['msg'] = 'SUCCESS';
		}
        $payload->setOutput($output);
		
        return $payload;
    }
}