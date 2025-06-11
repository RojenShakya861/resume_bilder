<?php
require_once 'includes/functions.php';
require_once 'includes/db.php';

// Get the raw POST data
$json = file_get_contents('php://input');
$data = json_decode($json, true);

// Check if skills were provided
if (!isset($data['skills']) || !is_array($data['skills'])) {
    http_response_code(400);
    echo json_encode(['error' => 'No skills provided']);
    exit;
}

// Get job recommendations
$recommendations = getJobRecommendations($data['skills']);

if ($recommendations === null) {
    http_response_code(500);
    echo json_encode(['error' => 'Failed to get recommendations']);
    exit;
}

// Return the recommendations
echo json_encode($recommendations); 