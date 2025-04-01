<?php

require_once(__DIR__ . '/BaseModel.php');

class ReviewModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
    }

    public function submitReview(int $client_id, int $freelancer_id, int $job_id, int $rating, string $review): bool
    {
        $sql = "INSERT INTO reviews (client_id, freelancer_id, job_id, rating, review)
                VALUES (:client_id, :freelancer_id, :job_id, :rating, :review)";
        $stmt = self::$pdo->prepare($sql);
        return $stmt->execute([
            ':client_id' => $client_id,
            ':freelancer_id' => $freelancer_id,
            ':job_id' => $job_id,
            ':rating' => $rating,
            ':review' => $review
        ]);
    }

    public function getReviewsForFreelancer(int $freelancer_id): array
    {
        $sql = "SELECT reviews.*, users.name AS client_name
                FROM reviews
                JOIN users ON reviews.client_id = users.id
                WHERE freelancer_id = :freelancer_id
                ORDER BY created_at DESC";
        $stmt = self::$pdo->prepare($sql);
        $stmt->bindParam(':freelancer_id', $freelancer_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getAverageRating(int $freelancer_id): float
    {
        $sql = "SELECT AVG(rating) AS avg_rating FROM reviews WHERE freelancer_id = :freelancer_id";
        $stmt = self::$pdo->prepare($sql);
        $stmt->bindParam(':freelancer_id', $freelancer_id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return round((float) $result['avg_rating'], 2);
    }
    
    public function hasUserReviewed(int $client_id, int $freelancer_id): bool {
        $sql = "SELECT COUNT(*) FROM reviews WHERE client_id = :client_id AND freelancer_id = :freelancer_id";
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute([
            ":client_id" => $client_id,
            ":freelancer_id" => $freelancer_id
        ]);
        return $stmt->fetchColumn() > 0;
    }
    public function getAcceptedApplication(int $job_id): ?array
    {
        $stmt = self::$pdo->prepare("
            SELECT * FROM job_applications
            WHERE job_id = :job_id AND status = 'accepted'
            LIMIT 1
        ");
        $stmt->bindParam(':job_id', $job_id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
    
        return $result ?: null;
    }

    
}
