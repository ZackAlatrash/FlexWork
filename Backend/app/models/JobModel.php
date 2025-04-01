<?php

require_once(__DIR__ . "/BaseModel.php");

class JobModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getAllJobs(int $page = 1, int $limit = 10): array {
        $offset = ($page - 1) * $limit;
    
        $sql = "SELECT jobs.*, users.name AS client_name, categories.name AS category 
                FROM jobs 
                JOIN users ON jobs.client_id = users.id
                JOIN categories ON jobs.category_id = categories.id
                LIMIT :limit OFFSET :offset";
    
        $stmt = self::$pdo->prepare($sql);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
        $stmt->execute();
    
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getJobById(int $id): ?array
    {
        $sql = "SELECT jobs.*, users.name AS client_name, categories.name AS category
                FROM jobs
                JOIN users ON jobs.client_id = users.id
                JOIN categories ON jobs.category_id = categories.id
                WHERE jobs.id = :id";
                
        $stmt = self::$pdo->prepare($sql);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function createJob($client_id, $title, $description, $category_id, $budget, $deadline, $skills, $location, $job_type, $additional_details)
    {
        $sql = "INSERT INTO jobs (client_id, title, description, category_id, budget, deadline, skills, location, job_type, additional_details, job_status) 
                VALUES (:client_id, :title, :description, :category_id, :budget, :deadline, :skills, :location, :job_type, :additional_details, 'open')";
    
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute([
            ':client_id' => $client_id,
            ':title' => $title,
            ':description' => $description,
            ':category_id' => $category_id,
            ':budget' => $budget,
            ':deadline' => $deadline,
            ':skills' => $skills,
            ':location' => $location,
            ':job_type' => $job_type,
            ':additional_details' => $additional_details
        ]);
    
        return self::$pdo->lastInsertId();
    }

    public function updateJobStatus(int $job_id, string $new_status): bool
    {
        $allowedStatuses = ['open', 'in_progress', 'completed', 'closed'];
        if (!in_array($new_status, $allowedStatuses)) {
            return false; // Invalid status
        }
    
        $sql = "UPDATE jobs SET job_status = :job_status WHERE id = :job_id";
        $stmt = self::$pdo->prepare($sql);
        return $stmt->execute([
            ':job_status' => $new_status,
            ':job_id' => $job_id
        ]);
    }

    public function getJobsByClientId(int $client_id): array {
        $sql = "SELECT * FROM jobs WHERE client_id = :client_id ORDER BY created_at DESC";
        $stmt = self::$pdo->prepare($sql);
        $stmt->bindParam(":client_id", $client_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getJobsByFreelancerId(int $freelancer_id): array {
        $sql = "SELECT jobs.* FROM jobs
                JOIN job_applications ON jobs.id = job_applications.job_id
                WHERE job_applications.freelancer_id = :freelancer_id
                ORDER BY jobs.created_at DESC";
        $stmt = self::$pdo->prepare($sql);
        $stmt->bindParam(":freelancer_id", $freelancer_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    

    

    public function deleteJob(int $id): bool
    {
        $sql = "DELETE FROM jobs WHERE id = :id";
        $stmt = self::$pdo->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        return $stmt->execute();
    }

    public function getFilteredJobs(?string $category = null, ?float $min_budget = null, ?float $max_budget = null, int $page = 1, int $limit = 10, ?string $sort = "newest"): array {
        $offset = ($page - 1) * $limit;
    
        $sql = "SELECT jobs.*, users.name AS client_name, categories.name AS category 
                FROM jobs 
                JOIN users ON jobs.client_id = users.id
                JOIN categories ON jobs.category_id = categories.id
                WHERE jobs.job_status != 'completed'
                ";
    
        // ✅ Apply filters only if values are set
        if (!empty($category)) {
            $sql .= " AND categories.name = :category";
        }
        if (!empty($min_budget)) {
            $sql .= " AND jobs.budget >= :min_budget";
        }
        if (!empty($max_budget)) {
            $sql .= " AND jobs.budget <= :max_budget";
        }
    
        // ✅ Sorting logic
        if ($sort === "budget_high") {
            $sql .= " ORDER BY jobs.budget DESC";
        } elseif ($sort === "budget_low") {
            $sql .= " ORDER BY jobs.budget ASC";
        } else {
            $sql .= " ORDER BY jobs.created_at DESC"; // Default: Newest first
        }
    
        // ✅ Get the total job count before applying pagination
        $countSql = "SELECT COUNT(*) as total FROM jobs WHERE 1=1";
        if (!empty($category)) {
            $countSql .= " AND category_id = (SELECT id FROM categories WHERE name = :category)";
        }
        if (!empty($min_budget)) {
            $countSql .= " AND budget >= :min_budget";
        }
        if (!empty($max_budget)) {
            $countSql .= " AND budget <= :max_budget";
        }
    
        $countStmt = self::$pdo->prepare($countSql);
        if (!empty($category)) $countStmt->bindParam(":category", $category, PDO::PARAM_STR);
        if (!empty($min_budget)) $countStmt->bindParam(":min_budget", $min_budget, PDO::PARAM_INT);
        if (!empty($max_budget)) $countStmt->bindParam(":max_budget", $max_budget, PDO::PARAM_INT);
        $countStmt->execute();
        $totalJobs = $countStmt->fetch(PDO::FETCH_ASSOC)["total"];
    
        // ✅ Add pagination
        $sql .= " LIMIT :limit OFFSET :offset";
    
        $stmt = self::$pdo->prepare($sql);
    
        if (!empty($category)) $stmt->bindParam(":category", $category, PDO::PARAM_STR);
        if (!empty($min_budget)) $stmt->bindParam(":min_budget", $min_budget, PDO::PARAM_INT);
        if (!empty($max_budget)) $stmt->bindParam(":max_budget", $max_budget, PDO::PARAM_INT);
        $stmt->bindParam(":limit", $limit, PDO::PARAM_INT);
        $stmt->bindParam(":offset", $offset, PDO::PARAM_INT);
    
        $stmt->execute();
        $jobs = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        return [
            "jobs" => $jobs,
            "total_jobs" => $totalJobs
        ];
    }
}
