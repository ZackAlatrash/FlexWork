<?php

require_once(__DIR__ . '/../models/JobApplicationModel.php');
require_once(__DIR__ . '/../utils/ResponseHelper.php');
require_once(__DIR__ . '/../middleware/JwtMiddleware.php');

class JobApplicationController {
    private $jobApplicationModel;

    public function __construct() {
        $this->jobApplicationModel = new JobApplicationModel();
    }

    public function applyForJob($id) {
        JwtMiddleware::requireRole("freelancer");

        $authUser = JwtMiddleware::authenticate();
        if (!$authUser) return;

        if (!is_numeric($id)) {
            ResponseHelper::sendError("Invalid job ID", 400);
            return;
        }

        $input = json_decode(file_get_contents("php://input"), true);
        if (empty($input["proposal"]) || empty($input["bid_amount"])) {
            ResponseHelper::sendError("Proposal and bid amount are required", 400);
            return;
        }

        $success = $this->jobApplicationModel->applyForJob(
            (int) $id,
            $authUser["id"],
            trim($input["proposal"]),
            (float) $input["bid_amount"]
        );

        if ($success) {
            ResponseHelper::sendJson(["message" => "Job application submitted successfully"]);
        } else {
            ResponseHelper::sendError("Failed to apply for job", 500);
        }
    }

    public function getApplicationsForJob($id) {
        JwtMiddleware::requireRole(["client", "admin"]);

        if (!is_numeric($id)) {
            ResponseHelper::sendError("Invalid job ID", 400);
            return;
        }

        $applications = $this->jobApplicationModel->getApplicationsForJob((int) $id);
        ResponseHelper::sendJson(["applications" => $applications]);
    }

    public function updateApplicationStatus($id) {
        JwtMiddleware::requireRole("client");

        $authUser = JwtMiddleware::authenticate();
        if (!$authUser) return;

        if (!is_numeric($id)) {
            ResponseHelper::sendError("Invalid application ID", 400);
            return;
        }

        $input = json_decode(file_get_contents("php://input"), true);
        if (empty($input["status"])) {
            ResponseHelper::sendError("Status is required", 400);
            return;
        }

        $validStatuses = ["accepted", "rejected"];
        $newStatus = trim($input["status"]);

        if (!in_array($newStatus, $validStatuses)) {
            ResponseHelper::sendError("Invalid status. Allowed values: 'accepted' or 'rejected'", 400);
            return;
        }

        $application = $this->jobApplicationModel->getApplicationById((int) $id);
        if (!$application) {
            ResponseHelper::sendError("Application not found", 404);
            return;
        }

        $jobId = $application["job_id"];
        $jobModel = new JobModel(); 
        $job = $jobModel->getJobById($jobId);

        if (!$job || $job["client_id"] !== $authUser["id"]) {
            ResponseHelper::sendError("You are not authorized to update this job application", 403);
            return;
        }

        $success = $this->jobApplicationModel->updateApplicationStatus((int) $id, $newStatus);

        if ($success) {
            ResponseHelper::sendJson(["message" => "Application status updated successfully"]);
        } else {
            ResponseHelper::sendError("Failed to update application status", 500);
        }
    }
    public function getAcceptedApplication($job_id) {
        JwtMiddleware::requireRole(["client", "admin"]);

        if (!is_numeric($job_id)) {
            ResponseHelper::sendError("Invalid job ID", 400);
            return;
        }

        $acceptedApplication = $this->jobApplicationModel->getAcceptedApplication((int) $job_id);

        if ($acceptedApplication) {
            ResponseHelper::sendJson($acceptedApplication);
        } else {
            ResponseHelper::sendError("No accepted application found", 404);
        }
    }
}
