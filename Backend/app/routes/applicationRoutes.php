<?php

require_once(__DIR__ . '/../controllers/ApplicationController.php');
require_once(__DIR__ . '/../lib/Route.php');

$applicationController = new ApplicationController();

Route::add('/applications/job/([0-9]+)', function($job_id) use ($applicationController) {
    $applicationController->getApplicationsByJob($job_id);
}, 'get');

Route::add('/applications', function() use ($applicationController) {
    $applicationController->applyForJob();
}, 'post');

Route::add('/applications/([0-9]+)', function($application_id) use ($applicationController) {
    $applicationController->updateApplicationStatus($application_id);
}, 'put');
