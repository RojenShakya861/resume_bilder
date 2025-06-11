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

// Get current template status
$stmt = $conn->prepare("SELECT is_active FROM templates WHERE id = ?");
$stmt->execute([$template_id]);
$template = $stmt->fetch();

if ($template) {
    // Toggle the status
    $new_status = !$template['is_active'];
    $stmt = $conn->prepare("UPDATE templates SET is_active = ? WHERE id = ?");
    $stmt->execute([$new_status, $template_id]);
}

// Redirect back to dashboard
redirect('dashboard.php');
?> 