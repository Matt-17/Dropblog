<?php
header('Content-Type: application/json');

if (isset($results)) {
    echo json_encode([
        'success' => true,
        'allUpToDate' => $allUpToDate,
        'results' => $results
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => $message ?? 'Unknown error',
        'code' => $code ?? 500
    ]);
}
