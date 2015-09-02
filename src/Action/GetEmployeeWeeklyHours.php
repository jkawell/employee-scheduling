<?php
namespace Spark\Project\Action;

use Aura\Payload\Payload;
use Spark\Adr\DomainInterface;
use Spark\Project\Domain\Entity\User;
use Spark\Project\Domain\Authenticator;

global $root;
require $root.'/src/Helpers/date_helper.php';

class GetEmployeeWeeklyHours implements DomainInterface
{

    public function __invoke(array $input)
    {
		$output['weekly_hours'] = [];
        $payload = new Payload();
        $payload->setStatus(Payload::FOUND);
		
        if (empty($input['token']) || !Authenticator::validateEmployee($input['token']))
		{
			$output['error']['num'] = '101';
			$output['error']['msg'] = 'Invalid Token';
			$payload->setOutput($output);
			return $payload;
		}
		
        $user_mapper = spot()->mapper('Spark\Project\Domain\Entity\User');
		$employee = $user_mapper->first(['id' => $input['employee_id']]);
		if (!$employee)
		{
			$output['error']['num'] = '811';
			$output['error']['msg'] = 'Invalid employee_id';
			$payload->setOutput($output);
			return $payload;
		}
        
		$weeks_back = 5;
		if (!empty($input['weeks_back']) && $input['weeks_back'] > 0)
		{
			$weeks_back = $input['weeks_back'];
		}
        $mapper = spot()->mapper('Spark\Project\Domain\Entity\Shift');
        $output['weekly_hours'] = $mapper->getWeeklyHours($input['employee_id'], $weeks_back);
		
        $payload->setOutput($output);
		
        return $payload;
    }
}