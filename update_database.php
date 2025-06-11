<?php
require_once 'include/config.php';

try {
    // First create the templates table
    $conn->exec("CREATE TABLE IF NOT EXISTS templates (
        id INT PRIMARY KEY AUTO_INCREMENT,
        name VARCHAR(255) NOT NULL,
        html TEXT,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

    // Insert default template
    $stmt = $conn->query("SELECT COUNT(*) FROM templates");
    if ($stmt->fetchColumn() == 0) {
        $conn->exec("INSERT INTO templates (name, is_active) VALUES ('Default Template', 1)");
    }

    // Add template_id column to resumes table
    $conn->exec("ALTER TABLE resumes ADD COLUMN template_id INT DEFAULT 1");
    
    // Add designation column to resumes table
    $conn->exec("ALTER TABLE resumes ADD COLUMN designation VARCHAR(255)");
    
    echo "Database updated successfully! You can now try creating a resume again.";
    
} catch (PDOException $e) {
    if (strpos($e->getMessage(), "Duplicate column name") !== false) {
        echo "Columns already exist. You can proceed with creating a resume.";
    } else {
        echo "Error updating database: " . $e->getMessage();
    }
}
?> 