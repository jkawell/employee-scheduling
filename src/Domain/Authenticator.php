<?php
namespace Spark\Project\Domain;

class Authenticator
{
    public static function validateEmployee($token)
    {
        return (in_array($token, ['EMPLOYEE_ROLE', 'MANAGER_ROLE']));
    }
    public static function validateManager($token)
    {
        return ('MANAGER_ROLE' === $token);
    }
}