<?php
declare(strict_types=1);

header("Content-Type: application/json; charset=utf-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    http_response_code(405);
    echo json_encode(["error" => "Method not allowed."]);
    exit;
}

$input = json_decode(file_get_contents("php://input"), true);
if (!is_array($input)) {
    http_response_code(400);
    echo json_encode(["error" => "Invalid form data."]);
    exit;
}

$name = trim((string)($input["name"] ?? ""));
$email = trim((string)($input["email"] ?? ""));
$project = trim((string)($input["project"] ?? ""));
$message = trim((string)($input["message"] ?? ""));
$website = trim((string)($input["website"] ?? ""));

if ($website !== "") {
    echo json_encode(["success" => true]);
    exit;
}

if (mb_strlen($name) < 2 || mb_strlen($name) > 80) {
    http_response_code(422);
    echo json_encode(["error" => "Please enter a valid name."]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 120) {
    http_response_code(422);
    echo json_encode(["error" => "Please enter a valid email address."]);
    exit;
}

if (mb_strlen($project) < 2 || mb_strlen($project) > 120) {
    http_response_code(422);
    echo json_encode(["error" => "Please enter a project type."]);
    exit;
}

if (mb_strlen($message) < 10 || mb_strlen($message) > 3000) {
    http_response_code(422);
    echo json_encode(["error" => "Please provide at least 10 characters in your message."]);
    exit;
}

$submission = [
    "id" => bin2hex(random_bytes(8)),
    "created_at" => gmdate("Y-m-d\TH:i:s\Z"),
    "name" => $name,
    "email" => $email,
    "project" => $project,
    "message" => $message
];

$dataDirectory = __DIR__ . DIRECTORY_SEPARATOR . "data";
if (!is_dir($dataDirectory) && !mkdir($dataDirectory, 0755, true)) {
    http_response_code(500);
    echo json_encode(["error" => "Unable to create the local data folder."]);
    exit;
}

$file = $dataDirectory . DIRECTORY_SEPARATOR . "submissions.jsonl";
$line = json_encode($submission, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . PHP_EOL;

if (file_put_contents($file, $line, FILE_APPEND | LOCK_EX) === false) {
    http_response_code(500);
    echo json_encode(["error" => "Unable to save the enquiry locally."]);
    exit;
}

echo json_encode(["success" => true, "id" => $submission["id"]]);
