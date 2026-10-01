<?php
/**
 * JSON Response Helper
 */

function jsonResponse($success, $message = '', $data = null, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode([
        'success' => $success,
        'message' => $message,
        'data'    => $data
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

function success($message = 'OK', $data = null, $code = 200) {
    jsonResponse(true, $message, $data, $code);
}

function error($message = 'Something went wrong', $code = 400, $data = null) {
    jsonResponse(false, $message, $data, $code);
}

function paginated($items, $total, $page, $limit, $message = 'OK') {
    success($message, [
        'items' => $items,
        'pagination' => [
            'total' => (int)$total,
            'page'  => (int)$page,
            'limit' => (int)$limit,
            'pages' => (int)ceil($total / $limit)
        ]
    ]);
}