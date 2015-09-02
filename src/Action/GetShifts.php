<?php
namespace Spark\Project\Action;

use Aura\Payload\Payload;
use Spark\Adr\DomainInterface;
use Spark\Project\Domain\Entity\User;
use Spark\Project\Domain\Authenticator;

class GetShifts implements DomainInterface
{

    public function __invoke(array $input)
    {
		$output['shifts'] = [];
        $payload = new Payload();
        $payload->setStatus(Payload::FOUND);
        
        if (empty($input['token']) || !Authenticator::validateManager($input['token']))
		{
			$output['error']['num'] = '101';
			$output['error']['msg'] = 'Invalid Token';
			$payload->setOutput($output);
			return $payload;
		}
		
		$required_fields = ['start_time','end_time'];
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
		try
		{
			$shifts = $mapper->getShiftsBetween($input['start_time'], $input['end_time']);
		}
		catch(\Exception $e)
		{
			$output['error']['num'] = '999';
			$output['error']['msg'] = $e->getMessage();
			$payload->setOutput($output);
			return $payload;
		}
		$return_attrs = ['manager_id','break','start_time','end_time'];
        foreach($shifts as $shift)
        {
            $output['shifts'][] = $shift->getAPIOutput($return_attrs);;
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