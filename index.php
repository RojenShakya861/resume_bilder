<?php
require_once 'include/config.php';

if (!is_logged_in()) {
    redirect('auth/login.php');
}

// Get user's resumes
$stmt = $conn->prepare("SELECT * FROM resumes WHERE user_id = ? ORDER BY created_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$resumes = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Resume Builder</title>
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
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .btn-primary {
            background: #6c5ce7;
            border: none;
            padding: 10px 20px;
        }
        .btn-primary:hover {
            background: #5b4bc4;
        }
        .resume-card {
            height: 100%;
        }
        .resume-actions {
            position: absolute;
            top: 10px;
            right: 10px;
        }
        .resume-date {
            color: #6c757d;
            font-size: 0.9rem;
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
                        <a class="nav-link" href="create_resume.php">
                            <i class="fas fa-plus"></i> New Resume
                        </a>
                    </li>
                    <?php if (is_admin()): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="admin/dashboard.php">
                            <i class="fas fa-cog"></i> Admin Panel
                        </a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link" href="auth/logout.php">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>My Resumes</h2>
            <a href="create_resume.php" class="btn btn-primary">
                <i class="fas fa-plus"></i> Create New Resume
            </a>
        </div>

        <?php if (empty($resumes)): ?>
            <div class="text-center py-5">
                <i class="fas fa-file-alt fa-3x text-muted mb-3"></i>
                <h4>No Resumes Yet</h4>
                <p class="text-muted">Create your first resume to get started!</p>
                <a href="create_resume.php" class="btn btn-primary mt-3">
                    <i class="fas fa-plus"></i> Create Resume
                </a>
            </div>
        <?php else: ?>
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php foreach ($resumes as $resume): ?>
                    <div class="col">
                        <div class="card resume-card">
                            <div class="card-body">
                                <div class="resume-actions">
                                    <div class="dropdown">
                                        <button class="btn btn-link text-dark" type="button" data-bs-toggle="dropdown">
                                            <i class="fas fa-ellipsis-v"></i>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li>
                                                <a class="dropdown-item" href="edit_resume.php?id=<?php echo $resume['id']; ?>">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="preview_resume.php?id=<?php echo $resume['id']; ?>">
                                                    <i class="fas fa-eye"></i> Preview
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item" href="export_pdf.php?id=<?php echo $resume['id']; ?>">
                                                    <i class="fas fa-file-pdf"></i> Export PDF
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <a class="dropdown-item text-danger" href="delete_resume.php?id=<?php echo $resume['id']; ?>" 
                                                   onclick="return confirm('Are you sure you want to delete this resume?')">
                                                    <i class="fas fa-trash"></i> Delete
                                                </a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                                <h5 class="card-title"><?php echo htmlspecialchars($resume['title']); ?></h5>
                                <p class="card-text">
                                    <strong><?php echo htmlspecialchars($resume['full_name']); ?></strong><br>
                                    <?php echo htmlspecialchars($resume['email']); ?>
                                </p>
                                <p class="resume-date">
                                    Created: <?php echo date('M d, Y', strtotime($resume['created_at'])); ?>
                                </p>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 