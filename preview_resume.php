<?php
require_once 'include/config.php';

if (!is_logged_in()) {
    redirect('auth/login.php');
}

$resume_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Get resume data
$stmt = $conn->prepare("SELECT r.*, t.name as template_name 
                       FROM resumes r 
                       LEFT JOIN templates t ON r.template_id = t.id 
                       WHERE r.id = ? AND r.user_id = ?");
$stmt->execute([$resume_id, $_SESSION['user_id']]);
$resume = $stmt->fetch();

if (!$resume) {
    redirect('dashboard.php');
}

// Get all active templates for selection
$stmt = $conn->query("SELECT * FROM templates WHERE is_active = 1 ORDER BY name");
$templates = $stmt->fetchAll();

// Get selected template (from URL parameter or default to resume's template)
$selected_template_id = isset($_GET['template']) ? (int)$_GET['template'] : $resume['template_id'];

// Get education
$stmt = $conn->prepare("SELECT * FROM education WHERE resume_id = ? ORDER BY start_date DESC");
$stmt->execute([$resume_id]);
$education = $stmt->fetchAll();

// Get experience
$stmt = $conn->prepare("SELECT * FROM experience WHERE resume_id = ? ORDER BY start_date DESC");
$stmt->execute([$resume_id]);
$experience = $stmt->fetchAll();

// Get skills
$stmt = $conn->prepare("SELECT * FROM skills WHERE resume_id = ?");
$stmt->execute([$resume_id]);
$skills = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Preview Resume - Resume Builder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: #f8f9fa;
            min-height: 100vh;
        }
        .navbar {
            background: #6c5ce7;
            padding: 1rem;
        }
        .navbar-brand {
            color: white !important;
            font-weight: 600;
        }
        .nav-link {
            color: rgba(255,255,255,0.8) !important;
        }
        .nav-link:hover {
            color: white !important;
        }
        .preview-container {
            background: white;
            padding: 2rem;
            margin: 2rem auto;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            border-radius: 15px;
            max-width: 800px;
        }
        
        /* Template-specific styles */
        .template-professional {
            font-family: 'Times New Roman', serif;
        }
        
        .template-modern {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .template-modern .section h3 {
            color: #fff;
            border-bottom: 2px solid #fff;
        }
        
        .template-modern .badge {
            background-color: rgba(255,255,255,0.2);
            color: white;
        }
        
        .template-creative {
            font-family: 'Georgia', serif;
            background: #f8f9fa;
        }
        
        .template-creative .section h3 {
            color: #e74c3c;
            border-bottom: 2px solid #e74c3c;
        }
        
        .template-creative .badge {
            background-color: #e74c3c;
        }
        .preview-actions {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            z-index: 1000;
        }
        .btn-floating {
            width: 65px;
            height: 65px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0.5rem;
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            transition: all 0.3s ease;
            border: none;
            font-weight: 600;
            text-decoration: none;
            position: relative;
            overflow: hidden;
        }
        
        .btn-floating::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.5s;
        }
        
        .btn-floating:hover::before {
            left: 100%;
        }
        
        .btn-floating:hover {
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 12px 35px rgba(0,0,0,0.25);
        }
        
        .btn-floating:active {
            transform: translateY(-1px) scale(1.02);
        }
        
        .btn-floating i {
            font-size: 1.6rem;
            z-index: 1;
        }
        
        .btn-edit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .btn-edit:hover {
            background: linear-gradient(135deg, #5a6fd8 0%, #6a4190 100%);
            color: white;
        }
        
        .btn-print {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            color: white;
        }
        
        .btn-print:hover {
            background: linear-gradient(135deg, #0f8a7d 0%, #2dd66a 100%);
            color: white;
        }
        
        .btn-download {
            background: linear-gradient(135deg, #fc466b 0%, #3f5efb 100%);
            color: white;
        }
        
        .btn-download:hover {
            background: linear-gradient(135deg, #e63d5a 0%, #3654e8 100%);
            color: white;
        }
        
        .template-selector {
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }
        
        .template-selector:hover {
            border-color: rgba(255,255,255,0.6) !important;
            background: rgba(255,255,255,0.2) !important;
            transform: translateY(-1px);
        }
        
        .template-selector:focus {
            border-color: rgba(255,255,255,0.8) !important;
            background: rgba(255,255,255,0.25) !important;
            box-shadow: 0 0 15px rgba(255,255,255,0.3);
        }
        
        .template-selector option {
            background: #6c5ce7;
            color: white;
        }
        .section {
            margin-bottom: 2rem;
        }
        .section h3 {
            color: #6c5ce7;
            border-bottom: 2px solid #6c5ce7;
            padding-bottom: 0.5rem;
            margin-bottom: 1rem;
        }
        .contact-info i {
            color: #6c5ce7;
            margin-right: 0.5rem;
        }
        .badge {
            background-color: #6c5ce7;
            font-size: 0.9rem;
            padding: 0.5em 1em;
            margin: 0.25rem;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">Resume Builder</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <div class="d-flex align-items-center">
                            <label for="template-selector" class="text-white me-2 fw-bold">
                                <i class="fas fa-palette me-1"></i>Template:
                            </label>
                            <select id="template-selector" class="form-select form-select-sm template-selector" style="width: auto; border-radius: 20px; border: 2px solid rgba(255,255,255,0.3); background: rgba(255,255,255,0.1); color: white; font-weight: 500;">
                                <?php foreach ($templates as $template): ?>
                                    <option value="<?php echo $template['id']; ?>" 
                                            <?php if ($selected_template_id == $template['id']) echo 'selected'; ?>>
                                        <?php echo htmlspecialchars($template['name']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <?php 
        $template_name = 'professional';
        foreach ($templates as $template) {
            if ($template['id'] == $selected_template_id) {
                $template_name = strtolower($template['name']);
                break;
            }
        }
        ?>
        <div class="preview-container template-<?php echo $template_name; ?>">
            <div class="text-center mb-4">
                <h1><?php echo htmlspecialchars($resume['full_name']); ?></h1>
                <p class="lead"><?php echo htmlspecialchars($resume['designation']); ?></p>
                <div class="contact-info">
                    <p>
                        <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($resume['email']); ?> |
                        <i class="fas fa-phone"></i> <?php echo htmlspecialchars($resume['phone']); ?> |
                        <i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($resume['address']); ?>
                    </p>
                </div>
            </div>

            <?php if ($resume['summary']): ?>
            <div class="section">
                <h3>Professional Summary</h3>
                <p><?php echo nl2br(htmlspecialchars($resume['summary'])); ?></p>
            </div>
            <?php endif; ?>

            <?php if ($education): ?>
            <div class="section">
                <h3>Education</h3>
                <?php foreach ($education as $edu): ?>
                <div class="mb-3">
                    <h5><?php echo htmlspecialchars($edu['degree']); ?></h5>
                    <p class="mb-1"><?php echo htmlspecialchars($edu['institution']); ?></p>
                    <p class="text-muted">
                        <?php echo date('M Y', strtotime($edu['start_date'])); ?> - 
                        <?php echo $edu['end_date'] ? date('M Y', strtotime($edu['end_date'])) : 'Present'; ?>
                    </p>
                    <?php if ($edu['description']): ?>
                    <p><?php echo nl2br(htmlspecialchars($edu['description'])); ?></p>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if ($experience): ?>
            <div class="section">
                <h3>Experience</h3>
                <?php foreach ($experience as $exp): ?>
                <div class="mb-3">
                    <h5><?php echo htmlspecialchars($exp['position']); ?></h5>
                    <p class="mb-1"><?php echo htmlspecialchars($exp['company']); ?></p>
                    <p class="text-muted">
                        <?php echo date('M Y', strtotime($exp['start_date'])); ?> - 
                        <?php echo $exp['end_date'] ? date('M Y', strtotime($exp['end_date'])) : 'Present'; ?>
                    </p>
                    <?php if ($exp['description']): ?>
                    <p><?php echo nl2br(htmlspecialchars($exp['description'])); ?></p>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if ($skills): ?>
            <div class="section">
                <h3>Skills</h3>
                <div class="skills-list">
                    <?php foreach ($skills as $skill): ?>
                    <span class="badge"><?php echo htmlspecialchars($skill['skill_name']); ?></span>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="preview-actions">
        <a href="edit_resume.php?id=<?php echo $resume_id; ?>" class="btn btn-floating btn-edit" title="Edit Resume">
            <i class="fas fa-edit"></i>
        </a>
        <button onclick="window.print()" class="btn btn-floating btn-print" title="Print Resume">
            <i class="fas fa-print"></i>
        </button>
        <a href="download_resume.php?id=<?php echo $resume_id; ?>" class="btn btn-floating btn-download" title="Download PDF">
            <i class="fas fa-download"></i>
        </a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Template switching functionality
        document.getElementById('template-selector').addEventListener('change', function() {
            const selectedTemplate = this.value;
            const currentUrl = new URL(window.location);
            currentUrl.searchParams.set('template', selectedTemplate);
            window.location.href = currentUrl.toString();
        });
        
        // Update download and print buttons to use selected template
        document.addEventListener('DOMContentLoaded', function() {
            const templateId = document.getElementById('template-selector').value;
            
            // Update download link
            const downloadBtn = document.querySelector('a[href*="download_resume.php"]');
            if (downloadBtn) {
                const currentUrl = new URL(downloadBtn.href);
                currentUrl.searchParams.set('template', templateId);
                downloadBtn.href = currentUrl.toString();
            }
        });
    </script>
</body>
</html> 