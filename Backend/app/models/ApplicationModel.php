<?php

require_once(__DIR__ . "/BaseModel.php");

class ApplicationModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getApplicationsByJob(int $job_id): array
    {
        $sql = "SELECT applications.*, users.name AS freelancer_name 
                FROM applications 
                JOIN users ON applications.freelancer_id = users.id
                WHERE job_id = :job_id";
        $stmt = self::$pdo->prepare($sql);
        $stmt->bindParam(":job_id", $job_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function applyForJob(int $job_id, int $freelancer_id, string $proposal): int
    {
        $sql = "INSERT INTO applications (job_id, freelancer_id, proposal) VALUES (:job_id, :freelancer_id, :proposal)";
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute([
            ':job_id' => $job_id,
            ':freelancer_id' => $freelancer_id,
            ':proposal' => $proposal
        ]);

        return self::$pdo->lastInsertId();
    }

    public function updateApplicationStatus(int $application_id, string $status): bool
    {
        $sql = "UPDATE applications SET status = :status WHERE id = :id";
        $stmt = self::$pdo->prepare($sql);
        return $stmt->execute([
            ':status' => $status,
            ':id' => $application_id
        ]);
    }
}
