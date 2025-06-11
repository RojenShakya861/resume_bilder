<?php
require_once 'include/config.php';

try {
    // Check if resumes table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'resumes'");
    if ($stmt->rowCount() == 0) {
        // Create resumes table if it doesn't exist
        $conn->exec("CREATE TABLE resumes (
            id INT PRIMARY KEY AUTO_INCREMENT,
            user_id INT NOT NULL,
            title VARCHAR(255) NOT NULL,
            full_name VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL,
            phone VARCHAR(50) NOT NULL,
            address TEXT NOT NULL,
            summary TEXT,
            designation VARCHAR(255),
            template_id INT DEFAULT 1,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "Created resumes table<br>";
    }

    // Check if templates table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'templates'");
    if ($stmt->rowCount() == 0) {
        // Create templates table
        $conn->exec("CREATE TABLE templates (
            id INT PRIMARY KEY AUTO_INCREMENT,
            name VARCHAR(255) NOT NULL,
            html TEXT,
            is_active BOOLEAN DEFAULT TRUE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");
        echo "Created templates table<br>";
    }

    // Check if template_id column exists in resumes table
    $stmt = $conn->query("SHOW COLUMNS FROM resumes LIKE 'template_id'");
    if ($stmt->rowCount() == 0) {
        // Add template_id column
        $conn->exec("ALTER TABLE resumes ADD COLUMN template_id INT DEFAULT 1");
        echo "Added template_id column to resumes table<br>";
    }

    // Check if designation column exists in resumes table
    $stmt = $conn->query("SHOW COLUMNS FROM resumes LIKE 'designation'");
    if ($stmt->rowCount() == 0) {
        // Add designation column
        $conn->exec("ALTER TABLE resumes ADD COLUMN designation VARCHAR(255)");
        echo "Added designation column to resumes table<br>";
    }

    // Check if default template exists
    $stmt = $conn->query("SELECT COUNT(*) FROM templates");
    if ($stmt->fetchColumn() == 0) {
        // Insert default template
        $conn->exec("INSERT INTO templates (name, is_active) VALUES ('Default Template', 1)");
        echo "Added default template<br>";
    }

    echo "<br>Database structure is now correct. You can try creating a resume again.";

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 