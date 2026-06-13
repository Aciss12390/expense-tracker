<?php

// Endpoint dodaje nowy wydatek i zwraca wynik w formacie JSON dla frontendu.
header('Content-Type: application/json; charset=utf-8');

require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    // Tworzenie rekordu zmienia dane, dlatego dopuszczamy tylko zadania POST.
    http_response_code(405);
    echo json_encode([
        'status' => 'error',
        'message' => 'Dozwolona jest tylko metoda POST'
    ]);
    exit;
}

// Pobieramy wartosci z formularza i usuwamy przypadkowe spacje z pol tekstowych.
$title = trim($_POST['title'] ?? '');
$amount = $_POST['amount'] ?? '';
$category = trim($_POST['category'] ?? '');
$expense_date = $_POST['expense_date'] ?? '';
$note = trim($_POST['note'] ?? '');

if ($title === '' || $amount === '' || $category === '' || $expense_date === '') {
    // Pola wymagane musza byc obecne przed zapisem do bazy.
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Uzupełnij wszystkie wymagane pola'
    ]);
    exit;
}

if (!is_numeric($amount) || (float)$amount <= 0) {
    // Kwota musi byc dodatnia liczba, bo rekord reprezentuje realny wydatek.
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Kwota musi być liczbą większą od zera'
    ]);
    exit;
}

// Prepared statement oddziela tresc zapytania SQL od danych podanych przez uzytkownika.
$stmt = $conn->prepare(
    "INSERT INTO expenses (title, amount, category, expense_date, note)
     VALUES (?, ?, ?, ?, ?)"
);

$amountValue = (float)$amount;

$stmt->bind_param(
    "sdsss",
    $title,
    $amountValue,
    $category,
    $expense_date,
    $note
);

if ($stmt->execute()) {
    echo json_encode([
        'status' => 'success',
        'message' => 'Wydatek został dodany'
    ]);
} else {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Nie udało się dodać wydatku'
    ]);
}

$stmt->close();
$conn->close();
