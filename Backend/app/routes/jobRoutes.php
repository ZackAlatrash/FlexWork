<?php

require_once(__DIR__ . '/../controllers/JobController.php');
require_once(__DIR__ . '/../lib/Route.php');

$jobController = new JobController();

Route::add('/jobs', function() use ($jobController) {
    $jobController->getFilteredJobs(); 
}, 'get');

// ✅ Get a single job by ID
Route::add('/jobs/([0-9]+)', function($id) use ($jobController) {
    $jobController->getJobById($id);
}, 'get');

// ✅ Create a new job
Route::add('/jobs', function() use ($jobController) {
    $jobController->createJob();
}, 'post');

// ✅ Delete a job by ID
Route::add('/jobs/([0-9]+)', function($id) use ($jobController) {
    $jobController->deleteJob($id);
}, 'delete');

Route::add('/jobs/([0-9]+)/status', function($id) use ($jobController) {
    $jobController->updateJobStatus($id);
}, 'put');

Route::add('/my-jobs/client', function() use ($jobController) {
    $jobController->getClientJobs();
}, 'get');

Route::add('/my-jobs/freelancer', function() use ($jobController) {
    $jobController->getFreelancerJobs();
}, 'get');



