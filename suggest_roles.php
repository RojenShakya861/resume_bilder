<?php
require_once 'include/decision_tree.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    
    if (isset($data['skills']) && is_array($data['skills'])) {
        $decisionTree = new DecisionTree();
        $suggestions = $decisionTree->suggestRoles($data['skills']);
        
        echo json_encode([
            'success' => true,
            'suggestions' => $suggestions
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => 'Invalid input'
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Invalid request method'
    ]);
}
?> 