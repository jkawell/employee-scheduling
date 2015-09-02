<?php
require __DIR__ . '/../vendor/autoload.php';
$root = dirname(__DIR__);
require $root.'/src/Helpers/spot_helper.php';

$app = Spark\Application::boot();

$app->setMiddleware([
    'Relay\Middleware\ResponseSender',
    'Spark\Handler\ExceptionHandler',
    'Spark\Handler\RouteHandler',
    'Spark\Handler\ActionHandler',
]);

$app->addRoutes(function(Spark\Router $r) {
    $r->get('/employee/shifts', 'Spark\Project\Action\GetEmployeeShifts');
    $r->get('/employee/contact', 'Spark\Project\Action\GetEmployeeContact');
    $r->get('/employee/shift/coworkers', 'Spark\Project\Action\GetEmployeeShiftCoworkers');
    $r->get('/employee/hours/weekly', 'Spark\Project\Action\GetEmployeeWeeklyHours');
    $r->get('/manager/contact', 'Spark\Project\Action\GetManagerContact');
    $r->get('/shifts', 'Spark\Project\Action\GetShifts');
    $r->post('/shift', 'Spark\Project\Action\AddShift');
    $r->put('/shift/times', 'Spark\Project\Action\UpdateShiftTimes');
    $r->put('/shift/employee', 'Spark\Project\Action\UpdateShiftEmployee');
});

$app->run();
