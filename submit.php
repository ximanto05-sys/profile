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

if (mb_strlen($name) < 2 || mb_strlen($name) > 20) {
    http_response_code(422);
    echo json_encode(["error" => "Please enter a valid name."]);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL) || mb_strlen($email) > 50) {
    http_response_code(422);
    echo json_encode(["error" => "Please enter a valid email address."]);
    exit;
}

if (mb_strlen($project) < 2 || mb_strlen($project) > 300) {
    http_response_code(422);
    echo json_encode(["error" => "Please enter a project type."]);
    exit;
}

if (mb_strlen($message) < 10 || mb_strlen($message) > 300) {
    http_response_code(422);
    echo json_encode(["error" => "Please provide a message between 10 and 300 characters."]);
    exit;
}

try {
    $database = new PDO(
        "mysql:host=localhost;dbname=contact;charset=utf8mb4",
        "root",
        "",
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
    $statement = $database->prepare(
        "INSERT INTO cont (name, email, project, message, website)
         VALUES (:name, :email, :project, :message, :website)"
    );
    $statement->execute([
        "name" => $name,
        "email" => $email,
        "project" => $project,
        "message" => $message,
        "website" => ""
    ]);
} catch (PDOException $error) {
    error_log("Contact form database insert failed: " . $error->getMessage());
    http_response_code(500);
    echo json_encode(["error" => "Unable to save your enquiry right now. Please try again later."]);
    exit;
}

echo json_encode(["success" => true]);
