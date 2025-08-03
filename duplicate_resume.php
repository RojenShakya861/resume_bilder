<?php
require_once 'include/config.php';

if (!is_logged_in()) {
    redirect('auth/login.php');
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error'] = 'Invalid resume ID.';
    redirect('index.php');
}

$resume_id = (int)$_GET['id'];
$user_id = $_SESSION['user_id'];

try {
    $conn->beginTransaction();
    
    // Get original resume data
    $stmt = $conn->prepare("SELECT * FROM resumes WHERE id = ? AND user_id = ?");
    $stmt->execute([$resume_id, $user_id]);
    $resume = $stmt->fetch();
    
    if (!$resume) {
        $_SESSION['error'] = 'Resume not found or you do not have permission to duplicate it.';
        redirect('index.php');
    }
    
    // Create duplicate resume
    $stmt = $conn->prepare("INSERT INTO resumes (user_id, title, full_name, email, phone, address, summary, template_id, designation) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->execute([
        $user_id,
        sanitize_input($resume['title'] . ' (Copy)'),
        sanitize_input($resume['full_name']),
        sanitize_input($resume['email']),
        sanitize_input($resume['phone']),
        sanitize_input($resume['address']),
        sanitize_input($resume['summary']),
        $resume['template_id'],
        sanitize_input($resume['designation'])
    ]);
    
    $new_resume_id = $conn->lastInsertId();
    
    // Duplicate education
    $stmt = $conn->prepare("INSERT INTO education (resume_id, institution, degree, field_of_study, start_date, end_date, description) 
                           SELECT ?, institution, degree, field_of_study, start_date, end_date, description 
                           FROM education WHERE resume_id = ?");
    $stmt->execute([$new_resume_id, $resume_id]);
    
    // Duplicate experience
    $stmt = $conn->prepare("INSERT INTO experience (resume_id, company, position, start_date, end_date, description) 
                           SELECT ?, company, position, start_date, end_date, description 
                           FROM experience WHERE resume_id = ?");
    $stmt->execute([$new_resume_id, $resume_id]);
    
    // Duplicate skills
    $stmt = $conn->prepare("INSERT INTO skills (resume_id, skill_name, proficiency_level) 
                           SELECT ?, skill_name, proficiency_level 
                           FROM skills WHERE resume_id = ?");
    $stmt->execute([$new_resume_id, $resume_id]);
    
    $conn->commit();
    $_SESSION['success'] = 'Resume duplicated successfully!';
    
} catch (Exception $e) {
    $conn->rollBack();
    $_SESSION['error'] = 'Failed to duplicate resume: ' . $e->getMessage();
    error_log('Duplicate resume error: ' . $e->getMessage());
}

redirect('index.php');
?> 