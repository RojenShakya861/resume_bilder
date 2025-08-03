<?php
require_once 'include/config.php';

// Check if user is logged in
if (!is_logged_in()) {
    redirect('auth/login.php');
}

// Check if resume ID is provided
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = 'Invalid resume ID.';
    redirect('index.php');
}

$resume_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

try {
    // Check if the resume belongs to the current user
    $stmt = $conn->prepare("SELECT id, title FROM resumes WHERE id = ? AND user_id = ?");
    $stmt->execute([$resume_id, $user_id]);
    $resume = $stmt->fetch();

    if (!$resume) {
        $_SESSION['error'] = 'Resume not found or you do not have permission to delete it.';
        redirect('index.php');
    }

    // Delete the resume
    $delete_stmt = $conn->prepare("DELETE FROM resumes WHERE id = ? AND user_id = ?");
    $result = $delete_stmt->execute([$resume_id, $user_id]);

    if ($result) {
        $_SESSION['success'] = 'Resume "' . htmlspecialchars($resume['title']) . '" has been deleted successfully.';
    } else {
        $_SESSION['error'] = 'Failed to delete resume. Please try again.';
    }

} catch (PDOException $e) {
    $_SESSION['error'] = 'Database error occurred. Please try again.';
    error_log('Delete resume error: ' . $e->getMessage());
}

// Redirect back to index page
redirect('index.php');
?> 