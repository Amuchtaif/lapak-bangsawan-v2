<?php
header('Content-Type: application/json');
require_once dirname(__DIR__, 2) . "/config/init.php";
require_once ROOT_PATH . "helpers/BiteshipService.php";

$query = $_GET['q'] ?? '';

if (strlen($query) < 3) {
    echo json_encode(['success' => false, 'message' => 'Query too short']);
    exit;
}

$biteship = new BiteshipService();
$result = $biteship->searchArea($query);

if (isset($result['success']) && $result['success']) {
    echo json_encode(['success' => true, 'areas' => $result['data']['areas'] ?? []]);
} else {
    echo json_encode(['success' => false, 'message' => $result['message'] ?? 'Search failed']);
}
