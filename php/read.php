<?php

// Endpoint odczytuje liste wydatkow oraz laczna sume dla karty podsumowania.
header('Content-Type: application/json; charset=utf-8');

require_once 'db.php';

// Najnowsze pozycje pokazujemy na gorze listy; ID rozstrzyga kolejnosc dla tej samej daty.
$sql = "SELECT * FROM expenses ORDER BY expense_date DESC, id DESC";
$result = $conn->query($sql);

$expenses = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $expenses[] = $row;
    }
}

// Suma jest liczona osobno, zeby frontend nie musial jej obliczac po stronie przegladarki.
$totalSql = "SELECT COALESCE(SUM(amount), 0) AS total FROM expenses";
$totalResult = $conn->query($totalSql);
$totalRow = $totalResult->fetch_assoc();

echo json_encode([
    'status' => 'success',
    'data' => $expenses,
    'total' => $totalRow['total']
]);

$conn->close();
