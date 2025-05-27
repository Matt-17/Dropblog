<?php
header('Content-Type: application/json');
echo json_encode([
    'success' => false,
    'message' => $message ?? 'Unknown error',
    'code' => $code ?? 500
]);
