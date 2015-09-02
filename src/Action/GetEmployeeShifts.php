<?php
namespace Spark\Project\Action;

use Aura\Payload\Payload;
use Spark\Adr\DomainInterface;
use Spark\Project\Domain\Entity\User;
use Spark\Project\Domain\Authenticator;

class GetEmployeeShifts implements DomainInterface
{

    public function __invoke(array $input)
    {
		$output['shifts'] = [];
        $payload = new Payload();
        $payload->setStatus(Payload::FOUND);
        
        if (empty($input['token']) || !Authenticator::validateEmployee($input['token']))
		{
			$output['error']['num'] = '101';
			$output['error']['msg'] = 'Invalid Token';
			$payload->setOutput($output);
			return $payload;
		}
		
		if (empty($input['employee_id']))
		{
			$output['error']['num'] = '801';
			$output['error']['msg'] = 'employee_id must be specified';
			$payload->setOutput($output);
			return $payload;
		}
        
        $user_mapper = spot()->mapper('Spark\Project\Domain\Entity\User');
		
		$user = $user_mapper->first(['id' => $input['employee_id']]);
		if (!$user)
		{
			$output['error']['num'] = '811';
			$output['error']['msg'] = 'Invalid employee_id';
			$payload->setOutput($output);
			return $payload;
		}
		
        $mapper = spot()->mapper('Spark\Project\Domain\Entity\Shift');
        $shifts = $mapper->where(['employee_id'=>$input['employee_id']])
						->order(['start_time' => 'ASC']);
		$return_attrs = ['manager_id','break','start_time','end_time'];
        foreach($shifts as $shift)
        {
			$output['shifts'][] = $shift->getAPIOutput($return_attrs);
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