<?php

header('Content-Type: application/json; charset=utf-8');

require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Dozwolona jest tylko metoda POST'
    ]);
    exit;
}

$id = $_POST['id'] ?? '';

if ($id === '' || !filter_var($id, FILTER_VALIDATE_INT)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Nieprawidłowe ID wydatku'
    ]);
    exit;
}

$stmt = $conn->prepare("DELETE FROM expenses WHERE id = ?");
$idValue = (int)$id;

$stmt->bind_param("i", $idValue);

if ($stmt->execute()) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Wydatek został usunięty'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Nie udało się usunąć wydatku'
    ]);
}

$stmt->close();
$conn->close();