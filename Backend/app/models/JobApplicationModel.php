<?php

require_once(__DIR__ . "/BaseModel.php");

class JobApplicationModel extends BaseModel {
    public function __construct() {
        parent::__construct();
    }

    // ✅ Apply for a job (default status: "pending")
    public function applyForJob(int $job_id, int $freelancer_id, string $proposal, float $bid_amount): bool {
        $sql = "INSERT INTO job_applications (job_id, freelancer_id, proposal, bid_amount, status) 
                VALUES (:job_id, :freelancer_id, :proposal, :bid_amount, 'pending')";
        $stmt = self::$pdo->prepare($sql);
        return $stmt->execute([
            ':job_id' => $job_id,
            ':freelancer_id' => $freelancer_id,
            ':proposal' => $proposal,
            ':bid_amount' => $bid_amount
        ]);
    }

    // ✅ Get applications for a specific job (now includes status)
    public function getApplicationsForJob(int $job_id): array {
        $sql = "SELECT job_applications.*, users.name AS freelancer_name 
                FROM job_applications
                JOIN users ON job_applications.freelancer_id = users.id
                WHERE job_applications.job_id = :job_id";
        $stmt = self::$pdo->prepare($sql);
        $stmt->bindParam(":job_id", $job_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // ✅ Update application status (accept/reject)
    public function updateApplicationStatus(int $application_id, string $status): bool {
        $validStatuses = ['pending', 'accepted', 'rejected'];
        if (!in_array($status, $validStatuses)) {
            return false;
        }

        $sql = "UPDATE job_applications SET status = :status WHERE id = :id";
        $stmt = self::$pdo->prepare($sql);
        return $stmt->execute([
            ":status" => $status,
            ":id" => $application_id
        ]);
    }

    // ✅ Get a single application by ID
    public function getApplicationById(int $application_id): ?array {
        $sql = "SELECT * FROM job_applications WHERE id = :id";
        $stmt = self::$pdo->prepare($sql);
        $stmt->bindParam(":id", $application_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }
    public function getAcceptedApplication(int $job_id): ?array {
        $sql = "SELECT job_applications.*, users.name AS freelancer_name
                FROM job_applications
                JOIN users ON job_applications.freelancer_id = users.id
                WHERE job_applications.job_id = :job_id AND job_applications.status = 'accepted'
                LIMIT 1";
        
        $stmt = self::$pdo->prepare($sql);
        $stmt->bindParam(":job_id", $job_id, PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }
}
