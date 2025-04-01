<?php

require_once(__DIR__ . '/../models/UserModel.php');
require_once(__DIR__ . '/../utils/ResponseHelper.php');
require_once(__DIR__ . '/../middleware/JwtMiddleware.php');
require_once(__DIR__ . '/../utils/JwtHelper.php');

class UserController {
    private $userModel;

    public function __construct() {
        $this->userModel = new UserModel();
    }

    /**
     * Get all users (Admin only)
     * URL: GET /users
     */
    public function getAllUsers() {
        JwtMiddleware::requireRole("admin"); // ✅ Only admin can view all users

        try {
            $users = $this->userModel->getAllUsers();
            ResponseHelper::sendJson(["users" => $users]);
        } catch (Exception $e) {
            ResponseHelper::sendError("Failed to fetch users", 500);
        }
    }

    /**
     * Get a single user by ID
     * URL: GET /users/{id}
     */
    public function getUserById($id) {
        JwtMiddleware::authenticate(); // ✅ Requires authentication

        if (!is_numeric($id)) {
            ResponseHelper::sendError("Invalid user ID", 400);
            return;
        }

        try {
            $user = $this->userModel->getUserById((int) $id);
            if ($user) {
                unset($user["password"]); // ✅ Remove password from response
                ResponseHelper::sendJson($user);
            } else {
                ResponseHelper::sendError("User not found", 404);
            }
        } catch (Exception $e) {
            ResponseHelper::sendError("Failed to fetch user", 500);
        }
    }

    /**
     * User Registration
     * URL: POST /users/register
     * Body: { "name": "string", "email": "string", "password": "string", "role": "string" }
     */
    public function registerUser() {
        $input = json_decode(file_get_contents("php://input"), true);

        if (
            empty($input["name"]) ||
            empty($input["email"]) ||
            empty($input["password"]) ||
            empty($input["role"])
        ) {
            ResponseHelper::sendError("All fields are required", 400);
            return;
        }

        $name = trim($input["name"]);
        $email = trim($input["email"]);
        $password = password_hash($input["password"], PASSWORD_DEFAULT); // ✅ Hash password
        $role = in_array($input["role"], ["admin", "client", "freelancer"]) ? $input["role"] : "freelancer";

        try {
            $userId = $this->userModel->createUser($name, $email, $password, $role);
            ResponseHelper::sendJson(["message" => "User registered successfully", "user_id" => $userId], 201);
        } catch (Exception $e) {
            ResponseHelper::sendError("Failed to register user", 500);
        }
    }

    /**
     * User Login & JWT Token Generation
     * URL: POST /users/login
     * Body: { "email": "string", "password": "string" }
     */
    public function loginUser() {
        $input = json_decode(file_get_contents("php://input"), true);

        if (empty($input["email"]) || empty($input["password"])) {
            ResponseHelper::sendError("Email and password are required", 400);
            return;
        }

        $email = trim($input["email"]);
        $password = trim($input["password"]);

        try {
            $user = $this->userModel->getUserByEmail($email);
            if (!$user || !password_verify($password, $user["password"])) {
                ResponseHelper::sendError("Invalid credentials", 401);
                return;
            }

            $token = JwtHelper::generateToken($user["id"], $user["role"]);
            ResponseHelper::sendJson(["message" => "Login successful", "token" => $token]);
        } catch (Exception $e) {
            ResponseHelper::sendError("Failed to login", 500);
        }
    }

    /**
     * Delete a user (Admin only)
     * URL: DELETE /users/{id}
     */
    public function deleteUser($id) {
        JwtMiddleware::requireRole("admin"); // ✅ Only admin can delete users

        if (!is_numeric($id)) {
            ResponseHelper::sendError("Invalid user ID", 400);
            return;
        }

        try {
            $success = $this->userModel->deleteUser((int) $id);
            if ($success) {
                ResponseHelper::sendJson(["message" => "User deleted successfully"]);
            } else {
                ResponseHelper::sendError("Failed to delete user", 500);
            }
        } catch (Exception $e) {
            ResponseHelper::sendError("An error occurred while deleting the user", 500);
        }
    }

    public function updateUser()
    {
        $authUser = JwtMiddleware::authenticate();
        if (!$authUser) return;
    
        $input = json_decode(file_get_contents("php://input"), true);
    
        if (empty($input['name']) || empty($input['email'])) {
            ResponseHelper::sendError("Name and email are required", 400);
            return;
        }
    
        $name = trim($input['name']);
        $email = trim($input['email']);
    
        try {
            $success = $this->userModel->updateUser((int) $authUser['id'], $name, $email);
            if ($success) {
                ResponseHelper::sendJson(["message" => "Profile updated successfully"]);
            } else {
                ResponseHelper::sendError("Failed to update profile", 500);
            }
        } catch (Exception $e) {
            ResponseHelper::sendError("Server error", 500);
        }
    }

}
