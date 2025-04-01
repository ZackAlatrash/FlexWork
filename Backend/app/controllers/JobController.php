<?php

require_once(__DIR__ . '/../models/JobModel.php');
require_once(__DIR__ . '/../utils/ResponseHelper.php');
require_once(__DIR__ . '/../middleware/JwtMiddleware.php');

class JobController {
    private $jobModel;

    public function __construct() {
        $this->jobModel = new JobModel();
    }

    /**
     * Get all jobs (With Pagination)
     * URL: GET /jobs
     */
    public function getAllJobs() {
        try {
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

            if ($page < 1) $page = 1;
            if ($limit < 1 || $limit > 50) $limit = 10;

            $jobs = $this->jobModel->getAllJobs($page, $limit);

            ResponseHelper::sendJson([
                "jobs" => $jobs,
                "page" => $page,
                "limit" => $limit
            ]);
        } catch (Exception $e) {
            ResponseHelper::sendError("Failed to fetch jobs", 500);
        }
    }

    /**
     * Get job details by ID (Expanded)
     * URL: GET /jobs/{id}
     */
    public function getJobById($id) {
        if (!is_numeric($id)) {
            ResponseHelper::sendError("Invalid job ID", 400);
            return;
        }

        try {
            $job = $this->jobModel->getJobById((int) $id);
            if ($job) {
                ResponseHelper::sendJson($job);
            } else {
                ResponseHelper::sendError("Job not found", 404);
            }
        } catch (Exception $e) {
            ResponseHelper::sendError("Failed to fetch job", 500);
        }
    }

    /**
     * Create a job (Only Clients)
     * URL: POST /jobs
     * Body: { "title": "string", "description": "string", "category_id": int, "budget": float, "deadline": "YYYY-MM-DD", "skills": "string", "location": "string", "job_type": "string", "additional_details": "string" }
     */
    public function createJob() {
        JwtMiddleware::requireRole("client");

        $authUser = JwtMiddleware::authenticate();
        if (!$authUser) return;

        $input = json_decode(file_get_contents("php://input"), true);

        // Validate input
        if (
            empty($input["title"]) ||
            empty($input["description"]) ||
            empty($input["category_id"]) ||
            empty($input["budget"]) ||
            empty($input["deadline"])
        ) {
            ResponseHelper::sendError("All required fields must be filled", 400);
            return;
        }

        $client_id = $authUser["id"];
        $title = trim($input["title"]);
        $description = trim($input["description"]);
        $category_id = (int) $input["category_id"];
        $budget = (float) $input["budget"];
        $deadline = $input["deadline"];
        $skills = !empty($input["skills"]) ? trim($input["skills"]) : null;
        $location = !empty($input["location"]) ? trim($input["location"]) : null;
        $job_type = !empty($input["job_type"]) ? trim($input["job_type"]) : null;
        $additional_details = !empty($input["additional_details"]) ? trim($input["additional_details"]) : null;

        try {
            $jobId = $this->jobModel->createJob($client_id, $title, $description, $category_id, $budget, $deadline, $skills, $location, $job_type, $additional_details);
            ResponseHelper::sendJson(["message" => "Job created successfully", "job_id" => $jobId], 201);
        } catch (Exception $e) {
            ResponseHelper::sendError("Failed to create job", 500);
        }
    }

    public function updateJobStatus($job_id)
    {
        JwtMiddleware::requireRole("client");
    
        $input = json_decode(file_get_contents("php://input"), true);
        if (empty($input["job_status"])) {
            ResponseHelper::sendError("Job status is required", 400);
            return;
        }
    
        $new_status = trim($input["job_status"]);
    
        if ($this->jobModel->updateJobStatus($job_id, $new_status)) {
            ResponseHelper::sendJson(["message" => "Job status updated successfully"]);
        } else {
            ResponseHelper::sendError("Invalid job status", 400);
        }
    }


    /**
     * Delete a job by ID (Only Admin or Job Owner)
     * URL: DELETE /jobs/{id}
     */
    public function deleteJob($id) {
        JwtMiddleware::requireRole(["client", "admin"]);

        $authUser = JwtMiddleware::authenticate();
        if (!$authUser) return;

        if (!is_numeric($id)) {
            ResponseHelper::sendError("Invalid job ID", 400);
            return;
        }

        $job = $this->jobModel->getJobById((int) $id);
        if (!$job) {
            ResponseHelper::sendError("Job not found", 404);
            return;
        }

        // Only the job creator OR an admin can delete the job
        if ($authUser["id"] !== $job["client_id"] && $authUser["role"] !== "admin") {
            ResponseHelper::sendError("Unauthorized", 403);
            return;
        }

        try {
            $success = $this->jobModel->deleteJob((int) $id);
            if ($success) {
                ResponseHelper::sendJson(["message" => "Job deleted successfully"]);
            } else {
                ResponseHelper::sendError("Failed to delete job", 500);
            }
        } catch (Exception $e) {
            ResponseHelper::sendError("An error occurred while deleting the job", 500);
        }
    }

    /**
     * Get filtered jobs with pagination
     * URL: GET /jobs?category=Design&min_budget=50&max_budget=500&page=1&limit=10&sort=budget_high
     */
    public function getFilteredJobs() {
        try {
            $category = $_GET['category'] ?? null;
            $min_budget = isset($_GET['min_budget']) ? (float)$_GET['min_budget'] : null;
            $max_budget = isset($_GET['max_budget']) ? (float)$_GET['max_budget'] : null;
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $sort = $_GET['sort'] ?? "newest";

            if ($page < 1) $page = 1;
            if ($limit < 1 || $limit > 50) $limit = 10;

            $result = $this->jobModel->getFilteredJobs($category, $min_budget, $max_budget, $page, $limit, $sort);

            ResponseHelper::sendJson([
                "jobs" => $result["jobs"],
                "total_jobs" => $result["total_jobs"],
                "total_pages" => ceil($result["total_jobs"] / $limit),
                "page" => $page,
                "limit" => $limit,
                "filters" => compact('category', 'min_budget', 'max_budget')
            ]);
        } catch (Exception $e) {
            ResponseHelper::sendError("Failed to fetch filtered jobs", 500);
        }
    }

    public function getClientJobs() {
        JwtMiddleware::requireRole("client");
    
        $authUser = JwtMiddleware::authenticate();
        if (!$authUser) return;
    
        $client_id = $authUser["id"];
        $jobs = $this->jobModel->getJobsByClientId($client_id);
    
        ResponseHelper::sendJson(["jobs" => $jobs]);
    }
    
    public function getFreelancerJobs() {
        JwtMiddleware::requireRole("freelancer");
    
        $authUser = JwtMiddleware::authenticate();
        if (!$authUser) return;
    
        $freelancer_id = $authUser["id"];
        $jobs = $this->jobModel->getJobsByFreelancerId($freelancer_id);
    
        ResponseHelper::sendJson(["jobs" => $jobs]);
    }
    
}
