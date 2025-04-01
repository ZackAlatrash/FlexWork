<?php
// Manually set environment variables
putenv("DB_HOST=mysql_container");
putenv("DB_NAME=flexwork_db");
putenv("DB_USER=user");
putenv("DB_PASSWORD=password");
putenv("DB_PORT=3306");
putenv("DB_CHARSET=utf8mb4");

// Also add them to $_ENV and $_SERVER for compatibility
$_ENV["DB_HOST"] = "mysql_container";
$_ENV["DB_NAME"] = "flexwork_db";
$_ENV["DB_USER"] = "user";
$_ENV["DB_PASSWORD"] = "password";
$_ENV["DB_PORT"] = "3306";
$_ENV["DB_CHARSET"] = "utf8mb4";
