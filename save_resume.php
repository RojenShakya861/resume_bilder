<?php
require_once 'include/config.php';

if (!is_logged_in()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->beginTransaction();
        
        // Insert resume
        $stmt = $conn->prepare("INSERT INTO resumes (user_id, title, full_name, email, phone, address, summary, template_id, designation) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_SESSION['user_id'],
            sanitize_input($_POST['title']),
            sanitize_input($_POST['full_name']),
            sanitize_input($_POST['email']),
            sanitize_input($_POST['phone']),
            sanitize_input($_POST['address']),
            sanitize_input($_POST['summary']),
            (int)$_POST['template_id'],
            sanitize_input($_POST['designation'])
        ]);
        
        $resume_id = $conn->lastInsertId();
        
        // Insert education
        if (isset($_POST['education'])) {
            $stmt = $conn->prepare("INSERT INTO education (resume_id, institution, degree, field_of_study, start_date, end_date, description) 
                                  VALUES (?, ?, ?, ?, ?, ?, ?)");
            
            foreach ($_POST['education'] as $edu) {
                $stmt->execute([
                    $resume_id,
                    sanitize_input($edu['institution']),
                    sanitize_input($edu['degree']),
                    sanitize_input($edu['field_of_study'] ?? ''),
                    sanitize_input($edu['start_date']),
                    sanitize_input($edu['end_date'] ?? null),
                    sanitize_input($edu['description'] ?? '')
                ]);
            }
        }
        
        // Insert experience
        if (isset($_POST['experience'])) {
            $stmt = $conn->prepare("INSERT INTO experience (resume_id, company, position, start_date, end_date, description) 
                                  VALUES (?, ?, ?, ?, ?, ?)");
            
            foreach ($_POST['experience'] as $exp) {
                $stmt->execute([
                    $resume_id,
                    sanitize_input($exp['company']),
                    sanitize_input($exp['position']),
                    sanitize_input($exp['start_date']),
                    sanitize_input($exp['end_date'] ?? null),
                    sanitize_input($exp['description'] ?? '')
                ]);
            }
        }
        
        // Insert skills
        if (isset($_POST['skills'])) {
            $skills = json_decode($_POST['skills'], true);
            if ($skills) {
                $stmt = $conn->prepare("INSERT INTO skills (resume_id, skill_name, proficiency_level) 
                                      VALUES (?, ?, ?)");
                
                foreach ($skills as $skill) {
                    $stmt->execute([
                        $resume_id,
                        sanitize_input($skill['name']),
                        (int)$skill['level']
                    ]);
                }
            }
        }
        
        $conn->commit();
        
        // Return success response
        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'resume_id' => $resume_id,
            'message' => 'Resume saved successfully'
        ]);
        
    } catch (Exception $e) {
        $conn->rollBack();
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Failed to create resume: ' . $e->getMessage()
        ]);
    }
} else {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request method'
    ]);
}
?> 