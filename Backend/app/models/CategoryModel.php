<?php

require_once(__DIR__ . '/BaseModel.php');

class CategoryModel extends BaseModel
{
    public function __construct()
    {
        parent::__construct();
    }

    // Get all categories
    public function getAllCategories(): array
    {
        $sql = "SELECT id, name FROM categories ORDER BY name ASC";
        $stmt = self::$pdo->query($sql);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Get category by ID
    public function getCategoryById(int $category_id): ?array
    {
        $sql = "SELECT id, name FROM categories WHERE id = :category_id";
        $stmt = self::$pdo->prepare($sql);
        $stmt->bindParam(":category_id", $category_id, PDO::PARAM_INT);
        $stmt->execute();
        $category = $stmt->fetch(PDO::FETCH_ASSOC);
        return $category ?: null;
    }

    // Create a new category
    public function createCategory(string $name): int
    {
        $sql = "INSERT INTO categories (name) VALUES (:name)";
        $stmt = self::$pdo->prepare($sql);
        $stmt->execute([':name' => $name]);
        return self::$pdo->lastInsertId();
    }

    // Delete a category by ID
    public function deleteCategory(int $category_id): bool
    {
        $sql = "DELETE FROM categories WHERE id = :category_id";
        $stmt = self::$pdo->prepare($sql);
        return $stmt->execute([':category_id' => $category_id]);
    }
}
