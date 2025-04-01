<?php
require_once __DIR__ . '/../models/CategoryModel.php';
require_once __DIR__ . '/../utils/ResponseHelper.php';

class CategoryController {
    private $categoryModel;

    public function __construct() {
        $this->categoryModel = new CategoryModel();
    }

    // Get all categories
    public function getAllCategories() {
        try {
            $categories = $this->categoryModel->getAllCategories();
            ResponseHelper::sendJson($categories);
        } catch (Exception $e) {
            ResponseHelper::sendError("Failed to fetch categories", 500);
        }
    }

    // Get a single category by ID
    public function getCategoryById($id) {
        try {
            $category = $this->categoryModel->getCategoryById($id);
            if (!$category) {
                ResponseHelper::sendError("Category not found", 404);
                return;
            }
            ResponseHelper::sendJson($category);
        } catch (Exception $e) {
            ResponseHelper::sendError("Failed to fetch category", 500);
        }
    }

    // Create a new category
    public function createCategory() {
        $input = json_decode(file_get_contents("php://input"), true);

        if (empty($input["name"])) {
            ResponseHelper::sendError("Category name is required", 400);
            return;
        }

        try {
            $categoryId = $this->categoryModel->createCategory($input["name"]);
            ResponseHelper::sendJson(["message" => "Category created successfully", "category_id" => $categoryId], 201);
        } catch (Exception $e) {
            ResponseHelper::sendError("Failed to create category", 500);
        }
    }

    // Delete a category
    public function deleteCategory($id) {
        try {
            $category = $this->categoryModel->getCategoryById($id);
            if (!$category) {
                ResponseHelper::sendError("Category not found", 404);
                return;
            }

            $this->categoryModel->deleteCategory($id);
            ResponseHelper::sendJson(["message" => "Category deleted successfully"]);
        } catch (Exception $e) {
            ResponseHelper::sendError("Failed to delete category", 500);
        }
    }
}
