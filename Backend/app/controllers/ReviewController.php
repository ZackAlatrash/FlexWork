<?php

require_once(__DIR__ . '/../models/ReviewModel.php');
require_once(__DIR__ . '/../middleware/JwtMiddleware.php');
require_once(__DIR__ . '/../utils/ResponseHelper.php');

class ReviewController
{
    private $reviewModel;

    public function __construct()
    {
        $this->reviewModel = new ReviewModel();
    }

    public function submitReview()
{
    JwtMiddleware::requireRole('client');
    $authUser = JwtMiddleware::authenticate();
    if (!$authUser) return;

    $input = json_decode(file_get_contents("php://input"), true);

    if (
        empty($input['freelancer_id']) ||
        empty($input['job_id']) ||
        empty($input['rating']) ||
        empty($input['review'])
    ) {
        ResponseHelper::sendError("All fields are required", 400);
        return;
    }

    // ✅ Check if this job has an accepted application with this freelancer
    $job_id = (int)$input['job_id'];
    $freelancer_id = (int)$input['freelancer_id'];

    $acceptedApplication = $this->reviewModel->getAcceptedApplication($job_id);

    if (!$acceptedApplication || $acceptedApplication['freelancer_id'] !== $freelancer_id) {
        ResponseHelper::sendError("No accepted application exists between this client and freelancer for this job.", 403);
        return;
    }

    // ✅ Proceed with review
    $success = $this->reviewModel->submitReview(
        $authUser['id'],
        $freelancer_id,
        $job_id,
        (int) $input['rating'],
        trim($input['review'])
    );

    if ($success) {
        ResponseHelper::sendJson(["message" => "Review submitted successfully"]);
    } else {
        ResponseHelper::sendError("Failed to submit review", 500);
    }
}


    public function getFreelancerReviews($freelancer_id)
    {
        if (!is_numeric($freelancer_id)) {
            ResponseHelper::sendError("Invalid freelancer ID", 400);
            return;
        }

        $reviews = $this->reviewModel->getReviewsForFreelancer((int)$freelancer_id);

        ResponseHelper::sendJson([
            "reviews" => $reviews,
        ]);
    }
    public function checkIfUserHasReviewed($freelancer_id) {
        JwtMiddleware::requireRole("client");
        $authUser = JwtMiddleware::authenticate();
        if (!$authUser) return;
    
        $client_id = $authUser["id"];
    
        try {
            $hasReviewed = $this->reviewModel->hasUserReviewed($client_id, (int)$freelancer_id);
            ResponseHelper::sendJson(["hasReviewed" => $hasReviewed]);
        } catch (Exception $e) {
            ResponseHelper::sendError("Error checking review status", 500);
        }
    }
    public function getAverageRatingOnly($freelancer_id)
    {
        if (!is_numeric($freelancer_id)) {
            ResponseHelper::sendError("Invalid freelancer ID", 400);
            return;
        }

        try {
            $average = $this->reviewModel->getAverageRating((int)$freelancer_id);
            ResponseHelper::sendJson(["average_rating" => $average]);
        } catch (Exception $e) {
            ResponseHelper::sendError("Error fetching average rating", 500);
        }
    }

    
}
