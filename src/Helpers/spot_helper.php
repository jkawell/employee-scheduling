<?php
function spot()
{
    static $spot;
    if($spot === null) {
        $config = new \Spot\Config();
        $config->addConnection('mysql', 'mysql://spot:$p0^@$p0^^3D@localhost/employee_scheduling');
        $spot = new \Spot\Locator($config);
    }
    return $spot;
}

function create_schema()
{
    $spot = spot();
    $mapper = $spot->mapper('Spark\Project\Domain\Entity\User');
    $mapper->migrate();
    $mapper = $spot->mapper('Spark\Project\Domain\Entity\Shift');
    $mapper->migrate();
}