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
$title = trim($_POST['title'] ?? '');
$amount = $_POST['amount'] ?? '';
$category = trim($_POST['category'] ?? '');
$expense_date = $_POST['expense_date'] ?? '';
$note = trim($_POST['note'] ?? '');

if ($id === '' || $title === '' || $amount === '' || $category === '' || $expense_date === '') {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Uzupełnij wszystkie wymagane pola'
    ]);
    exit;
}

if (!filter_var($id, FILTER_VALIDATE_INT)) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Nieprawidłowe ID wydatku'
    ]);
    exit;
}

if (!is_numeric($amount) || (float)$amount <= 0) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Kwota musi być liczbą większą od zera'
    ]);
    exit;
}

$stmt = $conn->prepare(
    "UPDATE expenses
     SET title = ?, amount = ?, category = ?, expense_date = ?, note = ?
     WHERE id = ?"
);

$idValue = (int)$id;
$amountValue = (float)$amount;

$stmt->bind_param(
    "sdsssi",
    $title,
    $amountValue,
    $category,
    $expense_date,
    $note,
    $idValue
);

if ($stmt->execute()) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Wydatek został zaktualizowany'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Nie udało się zaktualizować wydatku'
    ]);
}

$stmt->close();
$conn->close();