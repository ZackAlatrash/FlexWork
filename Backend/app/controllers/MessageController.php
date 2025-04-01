<?php

require_once(__DIR__ . '/../models/MessageModel.php');
require_once(__DIR__ . '/../utils/ResponseHelper.php');
require_once(__DIR__ . '/../middleware/JwtMiddleware.php');

class MessageController {
    private $messageModel;

    public function __construct() {
        $this->messageModel = new MessageModel();
    }

    public function getMessagesByJob($job_id) {
        // Authenticate user
        $authUser = JwtMiddleware::authenticate();
        if (!$authUser) return;

        if (!is_numeric($job_id)) {
            ResponseHelper::sendError("Invalid job ID", 400);
            return;
        }

        try {
            $messages = $this->messageModel->getMessagesByJob((int) $job_id);
            ResponseHelper::sendJson(["messages" => $messages]);
        } catch (Exception $e) {
            ResponseHelper::sendError("Failed to fetch messages", 500);
        }
    }


    public function sendMessage() {
        $authUser = JwtMiddleware::authenticate();
        if (!$authUser) return;

        $input = json_decode(file_get_contents("php://input"), true);

        if (
            empty($input["receiver_id"]) || 
            empty($input["job_id"]) || 
            empty($input["message"])
        ) {
            ResponseHelper::sendError("All fields are required", 400);
            return;
        }

        $sender_id = $authUser["id"];
        $receiver_id = (int) $input["receiver_id"];
        $job_id = (int) $input["job_id"];
        $message = trim($input["message"]);

        if ($receiver_id === $sender_id) {
            ResponseHelper::sendError("You cannot send a message to yourself", 400);
            return;
        }

        try {
            $success = $this->messageModel->sendMessage($sender_id, $receiver_id, $job_id, $message);
            if ($success) {
                ResponseHelper::sendJson(["message" => "Message sent successfully"]);
            } else {
                ResponseHelper::sendError("Failed to send message", 500);
            }
        } catch (Exception $e) {
            ResponseHelper::sendError("An error occurred while sending the message", 500);
        }
    }
}
