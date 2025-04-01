<?php

require_once(__DIR__ . '/../models/ApplicationModel.php');
require_once(__DIR__ . '/../utils/ResponseHelper.php');
require_once(__DIR__ . '/../middleware/JwtMiddleware.php');

class ApplicationController {
    private $applicationModel;

    public function __construct() {
        $this->applicationModel = new ApplicationModel();
    }

    /**
     * Get all applications for a job
     * URL: GET /applications/job/{job_id}
     */
    public function getApplicationsByJob($job_id) {
        if (!is_numeric($job_id)) {
            ResponseHelper::sendError("Invalid job ID", 400);
            return;
        }

        try {
            $applications = $this->applicationModel->getApplicationsByJob((int) $job_id);
            ResponseHelper::sendJson(["applications" => $applications]);
        } catch (Exception $e) {
            ResponseHelper::sendError("Failed to fetch applications", 500);
        }
    }

    /**
     * Apply for a job (Only Freelancers)
     * URL: POST /applications
     * Body: { "job_id": int, "proposal": "string" }
     */
    public function applyForJob() {
        JwtMiddleware::requireRole("freelancer"); // ✅ Directly enforce role

        $authUser = JwtMiddleware::authenticate();
        if (!$authUser) return;

        $input = json_decode(file_get_contents("php://input"), true);

        // Validate input
        if (empty($input["job_id"]) || empty($input["proposal"])) {
            ResponseHelper::sendError("Job ID and proposal are required", 400);
            return;
        }

        $job_id = (int) $input["job_id"];
        $proposal = trim($input["proposal"]);
        $freelancer_id = $authUser["id"];

        try {
            $applicationId = $this->applicationModel->applyForJob($job_id, $freelancer_id, $proposal);
            ResponseHelper::sendJson(["message" => "Application submitted successfully", "application_id" => $applicationId], 201);
        } catch (Exception $e) {
            ResponseHelper::sendError("Failed to apply for job", 500);
        }
    }

    /**
     * Update application status (Only Clients & Admins)
     * URL: PUT /applications/{application_id}
     * Body: { "status": "string" }
     */
    public function updateApplicationStatus($application_id) {
        JwtMiddleware::requireRole(["client", "admin"]); // ✅ Allow only clients & admins

        $authUser = JwtMiddleware::authenticate();
        if (!$authUser) return;

        if (!is_numeric($application_id)) {
            ResponseHelper::sendError("Invalid application ID", 400);
            return;
        }

        $input = json_decode(file_get_contents("php://input"), true);
        if (empty($input["status"])) {
            ResponseHelper::sendError("Status is required", 400);
            return;
        }

        $status = trim($input["status"]);

        try {
            $success = $this->applicationModel->updateApplicationStatus((int) $application_id, $status);
            if ($success) {
                ResponseHelper::sendJson(["message" => "Application status updated successfully"]);
            } else {
                ResponseHelper::sendError("Failed to update application status", 500);
            }
        } catch (Exception $e) {
            ResponseHelper::sendError("An error occurred while updating application status", 500);
        }
    }
}
