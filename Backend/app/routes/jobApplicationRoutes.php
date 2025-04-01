<?php

require_once(__DIR__ . '/../controllers/JobApplicationController.php');
require_once(__DIR__ . '/../lib/Route.php');

$jobApplicationController = new JobApplicationController();

// ✅ Freelancer applies for a job
Route::add('/jobs/([0-9]+)/apply', function($id) use ($jobApplicationController) {
    $jobApplicationController->applyForJob($id);
}, 'post');

// ✅ Get all applications for a job (Client/Admin only)
Route::add('/jobs/([0-9]+)/applications', function($id) use ($jobApplicationController) {
    $jobApplicationController->getApplicationsForJob($id);
}, 'get');

Route::add('/applications/([0-9]+)/status', function($id) use ($jobApplicationController) {
    $jobApplicationController->updateApplicationStatus($id);
}, 'put');
Route::add('/jobs/([0-9]+)/accepted_application', function($job_id) use ($jobApplicationController) {
    $jobApplicationController->getAcceptedApplication($job_id);
}, 'get');
