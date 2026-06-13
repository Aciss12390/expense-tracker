<?php

header('Content-Type: application/json; charset=utf-8');

require_once 'db.php';

$sql = "SELECT * FROM expenses ORDER BY expense_date DESC, id DESC";
$result = $conn->query($sql);

$expenses = [];

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $expenses[] = $row;
    }
}

$totalSql = "SELECT COALESCE(SUM(amount), 0) AS total FROM expenses";
$totalResult = $conn->query($totalSql);
$totalRow = $totalResult->fetch_assoc();

echo json_encode([
    'status' => 'success',
    'data' => $expenses,
    'total' => $totalRow['total']
]);

$conn->close();