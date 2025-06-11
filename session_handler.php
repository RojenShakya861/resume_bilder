<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Function to check if user is logged in
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Function to redirect user
function redirect($url) {
    header("Location: $url");
    exit();
}

// Function to sanitize input
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

// Function to set flash message
function set_flash_message($type, $message) {
    $_SESSION['flash'] = [
        'type' => $type,
        'message' => $message
    ];
}

// Function to get and clear flash message
function get_flash_message() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Function to check if user has access to a resume
function has_resume_access($resume_id) {
    if (!is_logged_in()) {
        return false;
    }
    
    global $pdo;
    $stmt = $pdo->prepare("SELECT id FROM resumes WHERE id = ? AND user_id = ?");
    $stmt->execute([$resume_id, $_SESSION['user_id']]);
    return $stmt->rowCount() > 0;
}
?> 