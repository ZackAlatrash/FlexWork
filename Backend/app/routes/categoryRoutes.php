<?php
require_once(__DIR__ . '/../controllers/CategoryController.php');
require_once(__DIR__ . '/../lib/Route.php');

$categoryController = new CategoryController();

// Get all categories
Route::add('/categories', function() use ($categoryController) {
    $categoryController->getAllCategories();
}, 'get');

// Get a single category by ID
Route::add('/categories/([0-9]+)', function($id) use ($categoryController) {
    $categoryController->getCategoryById($id);
}, 'get');

// Create a new category
Route::add('/categories', function() use ($categoryController) {
    $categoryController->createCategory();
}, 'post');

// Delete a category by ID
Route::add('/categories/([0-9]+)', function($id) use ($categoryController) {
    $categoryController->deleteCategory($id);
}, 'delete');
