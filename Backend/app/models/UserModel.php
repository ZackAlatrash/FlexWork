<?php

require_once(__DIR__ . "/BaseModel.php");

class UserModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getAllUsers(): array
    {
        $sql = "SELECT id, name, email, role FROM users";
        $stmt = self::$pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getUserById(int $id): ?array
    {
        $sql = "SELECT id, name, email, password, role FROM users WHERE id = :id";
        $stmt = self::$pdo->prepare($sql);
        $stmt->bindParam(":id", $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function getUserByEmail(string $email): ?array
    {
        $sql = "SELECT id, name, email, password, role FROM users WHERE email = :email";
        $stmt = self::$pdo->prepare($sql);
        $stmt->bindParam(":email", $email, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function createUser(string $name, string $email, string $password, string $role): int
    {
        $sql = "INSERT INTO users (name, email, password, role) VALUES (:name, :email, :password, :role)";
        $stmt = self::$pdo->prepare($sql);

        $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':password' => $password, 
            ':role' => $role
        ]);

        return self::$pdo->lastInsertId();
    }

    public function deleteUser(int $userId): bool
    {
        $sql = "DELETE FROM users WHERE id = :id";
        $stmt = self::$pdo->prepare($sql);
        $stmt->bindParam(':id', $userId, PDO::PARAM_INT);
        return $stmt->execute();
    }
    public function updateUser(int $id, string $name, string $email): bool
    {
        $sql = "UPDATE users SET name = :name, email = :email WHERE id = :id";
        $stmt = self::$pdo->prepare($sql);
    
        return $stmt->execute([
            ':name' => $name,
            ':email' => $email,
            ':id' => $id
        ]);
    }

}
