<?php
require_once(__DIR__ . '/../../vendor/autoload.php'); // Include Composer autoload
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtHelper
{
    private static $secretKey = "your_secret_key"; // Change this to a strong secret key

    public static function generateToken(int $userId, string $role): string
    {
        $payload = [
            "id" => $userId,
            "role" => $role,
            "iat" => time(), // Issued at time
            "exp" => time() + 3600 // Token expires in 1 hour
        ];

        return JWT::encode($payload, self::$secretKey, 'HS256');
    }

    public static function verifyToken(string $token): ?array
    {
        try {
            return (array)JWT::decode($token, new Key(self::$secretKey, 'HS256'));
        } catch (Exception $e) {
            return null; // Invalid token
        }
    }
}
