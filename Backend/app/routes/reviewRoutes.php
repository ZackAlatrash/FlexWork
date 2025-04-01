<?php

require_once(__DIR__ . '/../controllers/ReviewController.php');
require_once(__DIR__ . '/../lib/Route.php');

$reviewController = new ReviewController();

Route::add('/reviews', function () use ($reviewController) {
    $reviewController->submitReview();
}, 'post');

Route::add('/reviews/([0-9]+)', function ($freelancer_id) use ($reviewController) {
    $reviewController->getFreelancerReviews($freelancer_id);
}, 'get');
Route::add('/reviews/([0-9]+)/can-review', function ($freelancer_id) use ($reviewController) {
    $reviewController->checkIfUserHasReviewed($freelancer_id);
}, 'get');
Route::add('/reviews/([0-9]+)/average', function ($freelancer_id) use ($reviewController) {
    $reviewController->getAverageRatingOnly($freelancer_id);
}, 'get');


