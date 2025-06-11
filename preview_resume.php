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
        .preview-actions {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            z-index: 1000;
        }
        .btn-floating {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0.5rem;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        .btn-floating i {
            font-size: 1.5rem;
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
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="preview-container">
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
        <a href="edit_resume.php?id=<?php echo $resume_id; ?>" class="btn btn-primary btn-floating" title="Edit Resume">
            <i class="fas fa-edit"></i>
        </a>
        <button onclick="window.print()" class="btn btn-success btn-floating" title="Print Resume">
            <i class="fas fa-print"></i>
        </button>
        <a href="download_resume.php?id=<?php echo $resume_id; ?>" class="btn btn-info btn-floating" title="Download PDF">
            <i class="fas fa-download"></i>
        </a>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 