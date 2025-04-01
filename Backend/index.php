<?php
require_once(__DIR__ . '/app/lib/Route.php');
require_once(__DIR__ . '/app/lib/env.php'); 


header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once(__DIR__ . '/app/routes/userRoutes.php');
require_once(__DIR__ . '/app/routes/jobRoutes.php');
require_once(__DIR__ . '/app/routes/applicationRoutes.php');
require_once(__DIR__ . '/app/routes/messageRoutes.php');
require_once(__DIR__ . '/app/routes/reviewRoutes.php');
require_once(__DIR__ . '/app/routes/jobApplicationRoutes.php');
require_once(__DIR__ . '/app/routes/categoryRoutes.php');

Route::pathNotFound(function() {
    http_response_code(404);
    echo json_encode(["error" => "Route not found"]);
});

Route::methodNotAllowed(function() {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed"]);
});

Route::run('/api');


