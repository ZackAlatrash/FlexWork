<?php
require_once(__DIR__ . '/../utils/JwtHelper.php');
require_once(__DIR__ . '/../utils/ResponseHelper.php');

class JwtMiddleware
{
    public static function authenticate()
    {
        $headers = getallheaders();

        if (!isset($headers['Authorization'])) {
            ResponseHelper::sendError("Missing token", 401);
            return null;
        }

        $token = str_replace("Bearer ", "", $headers['Authorization']);
        $decoded = JwtHelper::verifyToken($token);

        if (!$decoded) {
            ResponseHelper::sendError("Invalid or expired token", 403);
            return null;
        }

        return $decoded; // ✅ Return decoded user data
    }

    /**
     * Require a specific role or multiple roles
     * @param array|string $roles
     */
    public static function requireRole($roles)
    {
        $decoded = self::authenticate();
        if (!$decoded) {
            return;
        }

        // Convert single role string to an array
        $roles = is_array($roles) ? $roles : [$roles];

        if (!in_array($decoded["role"], $roles)) {
            ResponseHelper::sendError("Forbidden: You do not have permission", 403);
            return;
        }
    }
}
