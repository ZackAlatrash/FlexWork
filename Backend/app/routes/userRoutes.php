<?php

require_once(__DIR__ . '/../controllers/UserController.php');

$userController = new UserController();

Route::add('/users', function() use ($userController) {
    $userController->getAllUsers();
}, 'get');

Route::add('/users/([0-9]+)', function($id) use ($userController) {
    $userController->getUserById($id);
}, 'get');

Route::add('/users/register', function() use ($userController) {
    $userController->registerUser();
}, 'post');

Route::add('/users/login', function() use ($userController) {
    $userController->loginUser();
}, 'post');

Route::add('/users/([0-9]+)', function($id) use ($userController) {
    $userController->deleteUser($id);
}, 'delete');
Route::add('/users/update', function () use ($userController) {
    $userController->updateUser();
}, 'put');

