<?php
require_once '../include/config.php';

// Check if user is admin
if (!is_admin()) {
    redirect('../index.php');
}

// Check if template ID is provided
if (!isset($_GET['id'])) {
    redirect('dashboard.php');
}

$template_id = (int)$_GET['id'];

// Delete the template
$stmt = $conn->prepare("DELETE FROM templates WHERE id = ?");
$stmt->execute([$template_id]);

// Redirect back to dashboard
redirect('dashboard.php');
?> 