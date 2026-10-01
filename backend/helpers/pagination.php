<?php
/**
 * Pagination Helper
 */

function getPagination($page = null, $limit = null) {
    $page  = $page ?? max(1, (int)($_GET['page'] ?? 1));
    $limit = $limit ?? min(MAX_LIMIT, max(1, (int)($_GET['limit'] ?? DEFAULT_LIMIT)));
    $offset = ($page - 1) * $limit;

    return [
        'page'   => $page,
        'limit'  => $limit,
        'offset' => $offset
    ];
}

function paginateQuery($baseQuery, $countQuery, $params, $page, $limit) {
    $pdo = db();

    // Get total count
    $stmt = $pdo->prepare($countQuery);
    $stmt->execute($params);
    $total = (int)$stmt->fetchColumn();

    // Get paginated results
    $offset = ($page - 1) * $limit;
    $stmt = $pdo->prepare("$baseQuery LIMIT $limit OFFSET $offset");
    $stmt->execute($params);
    $items = $stmt->fetchAll();

    return [
        'items' => $items,
        'total' => $total,
        'page'  => $page,
        'limit' => $limit,
        'pages' => ceil($total / $limit)
    ];
}