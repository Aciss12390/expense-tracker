<?php

$host = 'localhost';
$user = 'root';
$password = '';
$database = 'expense_tracker';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Błąd połączenia z bazą danych'
    ]);
    exit;
}

$conn->set_charset('utf8mb4');