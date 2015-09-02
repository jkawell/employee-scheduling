<?php
namespace Spark\Project\Action;

use Aura\Payload\Payload;
use Spark\Adr\DomainInterface;
use Spark\Project\Domain\Entity\User;
use Spark\Project\Domain\Authenticator;

class GetEmployeeContact implements DomainInterface
{

    public function __invoke(array $input)
    {
		$output['employee'] = [];
        $payload = new Payload();
        $payload->setStatus(Payload::FOUND);
        
        if (empty($input['token']) || !Authenticator::validateManager($input['token']))
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
		$user = $user_mapper->where(['id'=>$input['employee_id']])->first();
		if (!$user)
		{
			$output['error']['num'] = '811';
			$output['error']['msg'] = 'Invalid employee_id';
			$payload->setOutput($output);
			return $payload;
		}
		$return_attrs = ['id','name','email','phone'];
		foreach($return_attrs as $attr)
		{
			$employee[$attr] = $user->$attr;
		}
		
		$output['employee'] =  $employee;
		if (empty($output['error']['num']))
		{
			$output['error']['num'] = '000';
			$output['error']['msg'] = 'SUCCESS';
		}
		
        $payload->setOutput($output);
		
        return $payload;
    }
}