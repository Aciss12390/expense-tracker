<?php

// Wspolna konfiguracja polaczenia z baza danych dla wszystkich endpointow PHP.
$host = 'localhost';
$user = 'root';
$password = '';
$database = 'expense_tracker';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    // Frontend oczekuje JSON-a takze wtedy, gdy nie udalo sie polaczyc z baza.
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Błąd połączenia z bazą danych'
    ]);
    exit;
}

// utf8mb4 pozwala poprawnie zapisywac polskie znaki oraz inne znaki Unicode.
$conn->set_charset('utf8mb4');
