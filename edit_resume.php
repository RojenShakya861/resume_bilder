<?php
require_once 'include/config.php';

if (!is_logged_in()) {
    redirect('auth/login.php');
}

$resume_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch resume data
$stmt = $conn->prepare("SELECT * FROM resumes WHERE id = ? AND user_id = ?");
$stmt->execute([$resume_id, $_SESSION['user_id']]);
$resume = $stmt->fetch();
if (!$resume) {
    redirect('index.php');
}

// Fetch education
$stmt = $conn->prepare("SELECT * FROM education WHERE resume_id = ? ORDER BY start_date DESC");
$stmt->execute([$resume_id]);
$education = $stmt->fetchAll();

// Fetch experience
$stmt = $conn->prepare("SELECT * FROM experience WHERE resume_id = ? ORDER BY start_date DESC");
$stmt->execute([$resume_id]);
$experience = $stmt->fetchAll();

// Fetch skills
$stmt = $conn->prepare("SELECT * FROM skills WHERE resume_id = ?");
$stmt->execute([$resume_id]);
$skills = $stmt->fetchAll();

// Get active templates
$stmt = $conn->query("SELECT * FROM templates WHERE is_active = 1");
$templates = $stmt->fetchAll();

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->beginTransaction();
        // Update resume
        $stmt = $conn->prepare("UPDATE resumes SET title=?, full_name=?, email=?, phone=?, address=?, summary=?, template_id=?, designation=? WHERE id=? AND user_id=?");
        $stmt->execute([
            sanitize_input($_POST['title']),
            sanitize_input($_POST['full_name']),
            sanitize_input($_POST['email']),
            sanitize_input($_POST['phone']),
            sanitize_input($_POST['address']),
            sanitize_input($_POST['summary']),
            (int)$_POST['template_id'],
            sanitize_input($_POST['designation']),
            $resume_id,
            $_SESSION['user_id']
        ]);
        // Remove old education, experience, skills
        $conn->prepare("DELETE FROM education WHERE resume_id = ?")->execute([$resume_id]);
        $conn->prepare("DELETE FROM experience WHERE resume_id = ?")->execute([$resume_id]);
        $conn->prepare("DELETE FROM skills WHERE resume_id = ?")->execute([$resume_id]);
        // Insert new education
        if (isset($_POST['education'])) {
            $stmt = $conn->prepare("INSERT INTO education (resume_id, institution, degree, field_of_study, start_date, end_date, description) VALUES (?, ?, ?, ?, ?, ?, ?)");
            foreach ($_POST['education'] as $edu) {
                $stmt->execute([
                    $resume_id,
                    sanitize_input($edu['institution']),
                    sanitize_input($edu['degree']),
                    sanitize_input($edu['field_of_study']),
                    sanitize_input($edu['start_date']),
                    sanitize_input($edu['end_date']),
                    sanitize_input($edu['description'])
                ]);
            }
        }
        // Insert new experience
        if (isset($_POST['experience'])) {
            $stmt = $conn->prepare("INSERT INTO experience (resume_id, company, position, start_date, end_date, description) VALUES (?, ?, ?, ?, ?, ?)");
            foreach ($_POST['experience'] as $exp) {
                $stmt->execute([
                    $resume_id,
                    sanitize_input($exp['company']),
                    sanitize_input($exp['position']),
                    sanitize_input($exp['start_date']),
                    sanitize_input($exp['end_date']),
                    sanitize_input($exp['description'])
                ]);
            }
        }
        // Insert new skills
        if (isset($_POST['skills'])) {
            foreach ($_POST['skills'] as $skill) {
                $conn->prepare("INSERT INTO skills (resume_id, skill_name, proficiency_level) VALUES (?, ?, ?)")
                    ->execute([
                        $resume_id,
                        sanitize_input($skill['name']),
                        (int)$skill['level']
                    ]);
            }
        }
        $conn->commit();
        $success = 'Resume updated successfully!';
        redirect("preview_resume.php?id=" . $resume_id);
    } catch (Exception $e) {
        $conn->rollBack();
        $error = 'Failed to update resume: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Resume - Resume Builder</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body { background: #f8f9fa; min-height: 100vh; }
        .navbar { background: #6c5ce7; padding: 1rem; }
        .navbar-brand { color: white !important; font-weight: 600; }
        .nav-link { color: rgba(255,255,255,0.8) !important; }
        .nav-link:hover { color: white !important; }
        .card { border: none; border-radius: 15px; box-shadow: 0 0 20px rgba(0,0,0,0.1); margin-bottom: 20px; }
        .btn-primary { background-color: #6c5ce7; border-color: #6c5ce7; color: white; padding: 0.5rem 1rem; }
        .btn-primary:hover { background-color: #5b4bc4; border-color: #5b4bc4; }
        .section-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .remove-section { color: #dc3545; cursor: pointer; }
        .remove-section:hover { color: #c82333; }
        .skills-container, .input-group, .selected-skills, .skill-tag, .remove-skill { }
        .selected-skills { min-height: 50px; padding: 10px; border: 1px solid #dee2e6; border-radius: 0.25rem; background-color: #f8f9fa; display: block !important; }
        .badge { font-size: 0.9rem; padding: 0.5em 0.8em; display: inline-flex; align-items: center; }
        .btn-close { padding: 0.25rem; margin-left: 0.25rem; }
        #roleSuggestions { margin-top: 1rem; padding: 1rem; border: 1px solid #dee2e6; border-radius: 0.25rem; background-color: #f8f9fa; }
        .list-group-item { border: none; border-bottom: 1px solid #dee2e6; }
        .list-group-item:last-child { border-bottom: none; }
        .input-group { display: flex !important; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        #skillInput { border-right: none; display: block !important; }
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
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Edit Resume</h2>
        </div>
        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>
        <?php if ($success): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>
        <form method="post">
            <div class="card p-4 mb-4">
                <div class="mb-3">
                    <label for="title" class="form-label">Resume Title</label>
                    <input type="text" class="form-control" id="title" name="title" value="<?php echo htmlspecialchars($resume['title']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="full_name" class="form-label">Full Name</label>
                    <input type="text" class="form-control" id="full_name" name="full_name" value="<?php echo htmlspecialchars($resume['full_name']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="designation" class="form-label">Designation</label>
                    <input type="text" class="form-control" id="designation" name="designation" value="<?php echo htmlspecialchars($resume['designation'] ?? ''); ?>">
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($resume['email']); ?>" required>
                </div>
                <div class="mb-3">
                    <label for="phone" class="form-label">Phone</label>
                    <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($resume['phone']); ?>">
                </div>
                <div class="mb-3">
                    <label for="address" class="form-label">Address</label>
                    <input type="text" class="form-control" id="address" name="address" value="<?php echo htmlspecialchars($resume['address']); ?>">
                </div>
                <div class="mb-3">
                    <label for="summary" class="form-label">Professional Summary</label>
                    <textarea class="form-control" id="summary" name="summary" rows="3"><?php echo htmlspecialchars($resume['summary']); ?></textarea>
                </div>
                <div class="mb-3">
                    <label for="template_id" class="form-label">Template</label>
                    <select class="form-select" id="template_id" name="template_id" required>
                        <?php foreach ($templates as $template): ?>
                            <option value="<?php echo $template['id']; ?>" <?php if ($resume['template_id'] == $template['id']) echo 'selected'; ?>><?php echo htmlspecialchars($template['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <!-- Education Section -->
            <div class="card p-4 mb-4">
                <div class="section-header">
                    <h4>Education</h4>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addEducation()"><i class="fas fa-plus"></i> Add</button>
                </div>
                <div id="educationSection">
                    <?php if ($education): foreach ($education as $i => $edu): ?>
                        <div class="row mb-3 education-entry">
                            <div class="col-md-4 mb-2"><input type="text" class="form-control" name="education[<?php echo $i; ?>][institution]" placeholder="Institution" value="<?php echo htmlspecialchars($edu['institution']); ?>" required></div>
                            <div class="col-md-3 mb-2"><input type="text" class="form-control" name="education[<?php echo $i; ?>][degree]" placeholder="Degree" value="<?php echo htmlspecialchars($edu['degree']); ?>" required></div>
                            <div class="col-md-2 mb-2"><input type="text" class="form-control" name="education[<?php echo $i; ?>][field_of_study]" placeholder="Field of Study" value="<?php echo htmlspecialchars($edu['field_of_study']); ?>"></div>
                            <div class="col-md-1 mb-2"><input type="date" class="form-control" name="education[<?php echo $i; ?>][start_date]" value="<?php echo htmlspecialchars($edu['start_date']); ?>" required></div>
                            <div class="col-md-1 mb-2"><input type="date" class="form-control" name="education[<?php echo $i; ?>][end_date]" value="<?php echo htmlspecialchars($edu['end_date']); ?>"></div>
                            <div class="col-md-1 mb-2"><button type="button" class="btn btn-danger btn-sm remove-section" onclick="removeSection(this)"><i class="fas fa-trash"></i></button></div>
                            <div class="col-12 mb-2"><textarea class="form-control" name="education[<?php echo $i; ?>][description]" placeholder="Description" rows="2"><?php echo htmlspecialchars($edu['description']); ?></textarea></div>
                        </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>
            <!-- Experience Section -->
            <div class="card p-4 mb-4">
                <div class="section-header">
                    <h4>Experience</h4>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addExperience()"><i class="fas fa-plus"></i> Add</button>
                </div>
                <div id="experienceSection">
                    <?php if ($experience): foreach ($experience as $i => $exp): ?>
                        <div class="row mb-3 experience-entry">
                            <div class="col-md-4 mb-2"><input type="text" class="form-control" name="experience[<?php echo $i; ?>][company]" placeholder="Company" value="<?php echo htmlspecialchars($exp['company']); ?>" required></div>
                            <div class="col-md-3 mb-2"><input type="text" class="form-control" name="experience[<?php echo $i; ?>][position]" placeholder="Position" value="<?php echo htmlspecialchars($exp['position']); ?>" required></div>
                            <div class="col-md-2 mb-2"><input type="date" class="form-control" name="experience[<?php echo $i; ?>][start_date]" value="<?php echo htmlspecialchars($exp['start_date']); ?>" required></div>
                            <div class="col-md-2 mb-2"><input type="date" class="form-control" name="experience[<?php echo $i; ?>][end_date]" value="<?php echo htmlspecialchars($exp['end_date']); ?>"></div>
                            <div class="col-md-1 mb-2"><button type="button" class="btn btn-danger btn-sm remove-section" onclick="removeSection(this)"><i class="fas fa-trash"></i></button></div>
                            <div class="col-12 mb-2"><textarea class="form-control" name="experience[<?php echo $i; ?>][description]" placeholder="Description" rows="2"><?php echo htmlspecialchars($exp['description']); ?></textarea></div>
                        </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>
            <!-- Skills Section -->
            <div class="card p-4 mb-4">
                <div class="section-header">
                    <h4>Skills</h4>
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addSkill()"><i class="fas fa-plus"></i> Add</button>
                </div>
                <div id="skillsSection">
                    <?php if ($skills): foreach ($skills as $i => $skill): ?>
                        <div class="row mb-3 skill-entry">
                            <div class="col-md-8 mb-2"><input type="text" class="form-control" name="skills[<?php echo $i; ?>][name]" placeholder="Skill Name" value="<?php echo htmlspecialchars($skill['skill_name']); ?>" required></div>
                            <div class="col-md-3 mb-2"><input type="number" class="form-control" name="skills[<?php echo $i; ?>][level]" placeholder="Proficiency (1-5)" min="1" max="5" value="<?php echo htmlspecialchars($skill['proficiency_level']); ?>" required></div>
                            <div class="col-md-1 mb-2"><button type="button" class="btn btn-danger btn-sm remove-section" onclick="removeSection(this)"><i class="fas fa-trash"></i></button></div>
                        </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>
            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Update Resume
                </button>
                <a href="delete_resume.php?id=<?php echo $resume_id; ?>" 
                   class="btn btn-danger" 
                   onclick="return confirm('Are you sure you want to delete this resume? This action cannot be undone.')">
                    <i class="fas fa-trash"></i> Delete Resume
                </a>
            </div>
        </form>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function removeSection(btn) {
            btn.closest('.row').remove();
        }
        function addEducation() {
            var idx = document.querySelectorAll('#educationSection .education-entry').length;
            var html = `<div class="row mb-3 education-entry">
                <div class="col-md-4 mb-2"><input type="text" class="form-control" name="education[${idx}][institution]" placeholder="Institution" required></div>
                <div class="col-md-3 mb-2"><input type="text" class="form-control" name="education[${idx}][degree]" placeholder="Degree" required></div>
                <div class="col-md-2 mb-2"><input type="text" class="form-control" name="education[${idx}][field_of_study]" placeholder="Field of Study"></div>
                <div class="col-md-1 mb-2"><input type="date" class="form-control" name="education[${idx}][start_date]" required></div>
                <div class="col-md-1 mb-2"><input type="date" class="form-control" name="education[${idx}][end_date]"></div>
                <div class="col-md-1 mb-2"><button type="button" class="btn btn-danger btn-sm remove-section" onclick="removeSection(this)"><i class="fas fa-trash"></i></button></div>
                <div class="col-12 mb-2"><textarea class="form-control" name="education[${idx}][description]" placeholder="Description" rows="2"></textarea></div>
            </div>`;
            document.getElementById('educationSection').insertAdjacentHTML('beforeend', html);
        }
        function addExperience() {
            var idx = document.querySelectorAll('#experienceSection .experience-entry').length;
            var html = `<div class="row mb-3 experience-entry">
                <div class="col-md-4 mb-2"><input type="text" class="form-control" name="experience[${idx}][company]" placeholder="Company" required></div>
                <div class="col-md-3 mb-2"><input type="text" class="form-control" name="experience[${idx}][position]" placeholder="Position" required></div>
                <div class="col-md-2 mb-2"><input type="date" class="form-control" name="experience[${idx}][start_date]" required></div>
                <div class="col-md-2 mb-2"><input type="date" class="form-control" name="experience[${idx}][end_date]"></div>
                <div class="col-md-1 mb-2"><button type="button" class="btn btn-danger btn-sm remove-section" onclick="removeSection(this)"><i class="fas fa-trash"></i></button></div>
                <div class="col-12 mb-2"><textarea class="form-control" name="experience[${idx}][description]" placeholder="Description" rows="2"></textarea></div>
            </div>`;
            document.getElementById('experienceSection').insertAdjacentHTML('beforeend', html);
        }
        function addSkill() {
            var idx = document.querySelectorAll('#skillsSection .skill-entry').length;
            var html = `<div class="row mb-3 skill-entry">
                <div class="col-md-8 mb-2"><input type="text" class="form-control" name="skills[${idx}][name]" placeholder="Skill Name" required></div>
                <div class="col-md-3 mb-2"><input type="number" class="form-control" name="skills[${idx}][level]" placeholder="Proficiency (1-5)" min="1" max="5" required></div>
                <div class="col-md-1 mb-2"><button type="button" class="btn btn-danger btn-sm remove-section" onclick="removeSection(this)"><i class="fas fa-trash"></i></button></div>
            </div>`;
            document.getElementById('skillsSection').insertAdjacentHTML('beforeend', html);
        }
    </script>
</body>
</html> 