<?php

require_once(__DIR__ . '/BaseModel.php');

class MessageModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getMessagesByJob(int $job_id): array
    {
        $sql = "SELECT messages.*, sender.name AS sender_name, receiver.name AS receiver_name
        FROM messages
        JOIN users AS sender ON messages.sender_id = sender.id
        JOIN users AS receiver ON messages.receiver_id = receiver.id
        WHERE messages.job_id = :job_id
        ORDER BY messages.sent_at ASC;
        ";
        $stmt = self::$pdo->prepare($sql);
        $stmt->bindParam(":job_id", $job_id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function sendMessage(int $sender_id, int $receiver_id, int $job_id, string $message): bool
    {
        $sql = "INSERT INTO messages (sender_id, receiver_id, job_id, message) VALUES (:sender_id, :receiver_id, :job_id, :message)";
        $stmt = self::$pdo->prepare($sql);
        return $stmt->execute([
            ':sender_id' => $sender_id,
            ':receiver_id' => $receiver_id,
            ':job_id' => $job_id,
            ':message' => $message
        ]);
    }
}
