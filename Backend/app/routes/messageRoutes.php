<?php

require_once(__DIR__ . '/../controllers/MessageController.php');
require_once(__DIR__ . '/../lib/Route.php');

$messageController = new MessageController();

Route::add('/messages/([0-9]+)', function($job_id) use ($messageController) {
    $messageController->getMessagesByJob($job_id);
}, 'get');

Route::add('/messages/send', function() use ($messageController) {
    $messageController->sendMessage();
}, 'post');
