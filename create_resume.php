<?php
require_once 'include/config.php';

if (!is_logged_in()) {
    redirect('auth/login.php');
}

$error = '';
$success = '';

// Get active templates
$stmt = $conn->query("SELECT * FROM templates WHERE is_active = 1");
$templates = $stmt->fetchAll();

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
                    sanitize_input($edu['field_of_study']),
                    sanitize_input($edu['start_date']),
                    sanitize_input($edu['end_date']),
                    sanitize_input($edu['description'])
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
                    sanitize_input($exp['end_date']),
                    sanitize_input($exp['description'])
                ]);
            }
        }
        
        // Insert skills
        if (isset($_POST['skills'])) {
            $stmt = $conn->prepare("INSERT INTO skills (resume_id, skill_name, proficiency_level) 
                                  VALUES (?, ?, ?)");
            
            foreach ($_POST['skills'] as $skill) {
                $stmt->execute([
                    $resume_id,
                    sanitize_input($skill['name']),
                    (int)$skill['level']
                ]);
            }
        }
        
        $conn->commit();
        $success = 'Resume created successfully!';
        
        // Redirect to preview page
        redirect("preview_resume.php?id=" . $resume_id);
        
    } catch (Exception $e) {
        $conn->rollBack();
        $error = 'Failed to create resume: ' . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Resume - Resume Builder</title>
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
            margin-bottom: 20px;
        }
        .btn-primary {
            background-color: #6c5ce7;
            border-color: #6c5ce7;
            color: white;
            padding: 0.5rem 1rem;
        }
        .btn-primary:hover {
            background-color: #5b4bc4;
            border-color: #5b4bc4;
        }
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .remove-section {
            color: #dc3545;
            cursor: pointer;
        }
        .remove-section:hover {
            color: #c82333;
        }
        .skills-container,
        .input-group,
        .selected-skills,
        .skill-tag,
        .remove-skill {
            /* Remove display: none */
        }
        .selected-skills {
            min-height: 50px;
            padding: 10px;
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            background-color: #f8f9fa;
            display: block !important;
        }
        .badge {
            font-size: 0.9rem;
            padding: 0.5em 0.8em;
            display: inline-flex;
            align-items: center;
        }
        .btn-close {
            padding: 0.25rem;
            margin-left: 0.25rem;
        }
        #roleSuggestions {
            margin-top: 1rem;
            padding: 1rem;
            border: 1px solid #dee2e6;
            border-radius: 0.25rem;
            background-color: #f8f9fa;
        }
        .list-group-item {
            border: none;
            border-bottom: 1px solid #dee2e6;
        }
        .list-group-item:last-child {
            border-bottom: none;
        }
        .input-group {
            display: flex !important;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        #skillInput {
            border-right: none;
            display: block !important;
        }
        .input-group .btn {
            display: inline-block !important;
            border-left: none;
            z-index: 0;
        }
        .form-check-input {
            width: 3em;
            height: 1.5em;
            margin-top: 0.25em;
            vertical-align: middle;
            background-color: #e9ecef;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='3' fill='rgba%280, 0, 0, 0.25%29'/%3e%3c/svg%3e");
            background-repeat: no-repeat;
            background-position: left center;
            border-radius: 2em;
            transition: background-position .15s ease-in-out;
        }
        .form-check-input:checked {
            background-color: #6c5ce7;
            border-color: #6c5ce7;
            background-position: right center;
        }
        .form-check-label {
            margin-left: 0.5rem;
            font-size: 0.9rem;
            color: #6c757d;
        }
        .alert-info {
            background-color: #e3f2fd;
            border-color: #bbdefb;
            color: #0d47a1;
        }
        .alert-info i {
            margin-right: 0.5rem;
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
                        <a class="nav-link" href="index.php">
                            <i class="fas fa-arrow-left"></i> Back to Dashboard
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card">
                    <div class="card-header bg-white">
                        <h4 class="mb-0">Create New Resume</h4>
                    </div>
                    <div class="card-body">
                        <?php if ($error): ?>
                            <div class="alert alert-danger"><?php echo $error; ?></div>
                        <?php endif; ?>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success"><?php echo $success; ?></div>
                        <?php endif; ?>
                        
                        <form action="save_resume.php" method="POST" class="needs-validation" id="resumeForm" novalidate>
                            <input type="hidden" name="template_id" value="1">
                            
                            <!-- Resume Title -->
                            <div class="form-group mb-4">
                                <label for="title">Resume Title</label>
                                <input type="text" class="form-control" id="title" name="title" placeholder="e.g., My Professional Resume" required>
                                <small class="text-muted">Give your resume a title to easily identify it in your dashboard</small>
                            </div>
                            
                            <!-- Personal Information -->
                            <div class="card mb-4">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0">Personal Information</h5>
                                </div>
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="full_name">Full Name</label>
                                                <input type="text" class="form-control" id="full_name" name="full_name" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="designation">Designation</label>
                                                <input type="text" class="form-control" id="designation" name="designation" placeholder="e.g., Software Developer, Project Manager" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="email">Email</label>
                                                <input type="email" class="form-control" id="email" name="email" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="phone">Phone</label>
                                                <input type="tel" class="form-control" id="phone" name="phone" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row">
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label for="address">Address</label>
                                                <input type="text" class="form-control" id="address" name="address" required>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group mt-3">
                                        <label for="summary">Professional Summary</label>
                                        <textarea class="form-control" id="summary" name="summary" rows="4" placeholder="Write a brief summary of your professional background and career objectives"></textarea>
                                    </div>
                                </div>
                            </div>

                            <!-- Education -->
                            <div class="card mb-4">
                                <div class="card-header bg-white">
                                    <div class="section-header">
                                        <h5 class="mb-0">Education</h5>
                                        <div class="d-flex align-items-center">
                                            <button type="button" class="btn btn-sm btn-primary me-3" onclick="addEducation()">
                                                <i class="fas fa-plus"></i> Add Education
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body" id="educationContainer">
                                    <!-- Education entries will be added here -->
                                </div>
                            </div>

                            <!-- Experience -->
                            <div class="card mb-4">
                                <div class="card-header bg-white">
                                    <div class="section-header">
                                        <h5 class="mb-0">Experience</h5>
                                        <div class="d-flex align-items-center">
                                            <div class="form-check form-switch me-3">
                                                <input class="form-check-input" type="checkbox" id="noExperience" onchange="toggleExperienceSection()">
                                                <label class="form-check-label" for="noExperience">I have no work experience</label>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-primary" id="addExperienceBtn" onclick="addExperience()">
                                                <i class="fas fa-plus"></i> Add Experience
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="card-body" id="experienceContainer">
                                    <!-- Experience entries will be added here -->
                                </div>
                            </div>

                            <!-- Skills -->
                            <div class="card mb-4">
                                <div class="card-header bg-white">
                                    <h5 class="mb-0">Skills</h5>
                                </div>
                                <div class="card-body">
                                    <div class="form-group">
                                        <div class="row mb-3">
                                            <div class="col-md-12">
                                                <label>Enter Your Skills</label>
                                                <div class="input-group">
                                                    <input type="text" class="form-control" id="skillInput" placeholder="Type a skill and press Enter">
                                                    <button class="btn btn-primary" type="button" id="addSkillBtn">
                                                        <i class="fas fa-plus"></i> Add Skill
                                                    </button>
                                                </div>
                                                <small class="text-muted">Press Enter or click Add Skill to add a skill</small>
                                            </div>
                                        </div>
                                        <div id="selectedSkills" class="selected-skills mb-3"></div>
                                        <div id="roleSuggestions" class="mt-3" style="display: none;">
                                            <h6>Suggested Roles Based on Your Skills:</h6>
                                            <div class="list-group" id="suggestedRoles"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between">
                                <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
                                <button type="submit" class="btn btn-primary" id="saveResumeBtn">Save Resume</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Education section
        function addEducation() {
            const container = document.getElementById('educationContainer');
            const index = container.children.length;
            
            const html = `
                <div class="education-entry mb-4">
                    <div class="section-header">
                        <h6>Education</h6>
                        <i class="fas fa-times remove-section" onclick="this.parentElement.parentElement.remove()"></i>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Institution</label>
                            <input type="text" class="form-control" name="education[${index}][institution]" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Degree</label>
                            <input type="text" class="form-control" name="education[${index}][degree]" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" class="form-control" name="education[${index}][start_date]" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">End Date</label>
                            <input type="date" class="form-control" name="education[${index}][end_date]" id="end_date_${index}">
                        </div>
                    </div>
                    <div class="row mb-3">
                        <div class="col-12">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="currently_studying_${index}" 
                                       name="education[${index}][currently_studying]" onchange="toggleEndDate(${index})">
                                <label class="form-check-label" for="currently_studying_${index}">
                                    Currently Studying
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="education[${index}][description]" rows="3"></textarea>
                    </div>
                </div>
            `;
            
            container.insertAdjacentHTML('beforeend', html);
        }

        // Function to toggle end date field
        function toggleEndDate(index) {
            const endDateInput = document.getElementById(`end_date_${index}`);
            const currentlyStudyingCheckbox = document.getElementById(`currently_studying_${index}`);
            
            if (currentlyStudyingCheckbox.checked) {
                endDateInput.disabled = true;
                endDateInput.value = ''; // Clear the value when disabled
            } else {
                endDateInput.disabled = false;
            }
        }

        // Experience section
        function toggleExperienceSection() {
            const noExperienceCheckbox = document.getElementById('noExperience');
            const experienceContainer = document.getElementById('experienceContainer');
            const addExperienceBtn = document.querySelector('.section-header .btn-primary');
            
            if (noExperienceCheckbox.checked) {
                // Clear all experience entries
                experienceContainer.innerHTML = '';
                // Disable the add experience button
                if (addExperienceBtn) {
                    addExperienceBtn.disabled = true;
                    addExperienceBtn.style.opacity = '0.5';
                }
                // Add a message
                experienceContainer.innerHTML = `
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> No work experience section will be shown in your resume.
                    </div>
                `;
            } else {
                // Re-enable the add experience button
                if (addExperienceBtn) {
                    addExperienceBtn.disabled = false;
                    addExperienceBtn.style.opacity = '1';
                }
                // Add initial experience entry
                addExperience();
            }
        }

        // Update the addExperience function to check if section is disabled
        function addExperience() {
            const noExperienceCheckbox = document.getElementById('noExperience');
            if (noExperienceCheckbox.checked) {
                return; // Don't add experience if section is disabled
            }

            const container = document.getElementById('experienceContainer');
            const index = container.children.length;
            
            const html = `
                <div class="experience-entry mb-4">
                    <div class="section-header">
                        <h6>Experience</h6>
                        <i class="fas fa-times remove-section" onclick="this.parentElement.parentElement.remove()"></i>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Company</label>
                            <input type="text" class="form-control" name="experience[${index}][company]" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Position</label>
                            <input type="text" class="form-control" name="experience[${index}][position]" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Start Date</label>
                            <input type="date" class="form-control" name="experience[${index}][start_date]" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">End Date</label>
                            <input type="date" class="form-control" name="experience[${index}][end_date]">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea class="form-control" name="experience[${index}][description]" rows="3"></textarea>
                    </div>
                </div>
            `;
            
            container.insertAdjacentHTML('beforeend', html);
        }

        // Add initial sections
        document.addEventListener('DOMContentLoaded', function() {
            addEducation();
            addExperience();

            // Fetch skills from the dataset
            fetch('get_skills.php')
                .then(response => response.json())
                .then(skills => {
                    const datalist = document.createElement('datalist');
                    datalist.id = 'skillsList';
                    
                    // Add all unique skills to the datalist
                    const uniqueSkills = [...new Set(skills)];
                    uniqueSkills.forEach(skill => {
                        const option = document.createElement('option');
                        option.value = skill;
                        datalist.appendChild(option);
                    });
                    
                    document.body.appendChild(datalist);
                })
                .catch(error => console.error('Error loading skills:', error));
        });

        // Skills handling
        document.addEventListener('DOMContentLoaded', function() {
            const skillInput = document.getElementById('skillInput');
            const addSkillBtn = document.getElementById('addSkillBtn');
            const selectedSkills = document.getElementById('selectedSkills');
            const roleSuggestions = document.getElementById('roleSuggestions');
            const suggestedRoles = document.getElementById('suggestedRoles');
            const selectedSkillsSet = new Set();

            // Add skill when Enter is pressed
            skillInput.addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    addSkill();
                }
            });

            // Add skill button click handler
            addSkillBtn.addEventListener('click', addSkill);

            function addSkill() {
                const skill = skillInput.value.trim();
                if (skill && !selectedSkillsSet.has(skill)) {
                    selectedSkillsSet.add(skill);
                    updateSelectedSkills();
                    suggestRoles();
                    skillInput.value = '';
                }
            }

            // Function to update the selected skills display
            function updateSelectedSkills() {
                selectedSkills.innerHTML = '';
                selectedSkillsSet.forEach(skill => {
                    const skillTag = document.createElement('span');
                    skillTag.className = 'badge bg-primary me-2 mb-2';
                    skillTag.innerHTML = `
                        ${skill}
                        <button type="button" class="btn-close btn-close-white ms-2" 
                                style="font-size: 0.5rem;" 
                                onclick="removeSkill('${skill}')"></button>
                    `;
                    selectedSkills.appendChild(skillTag);
                });
            }

            // Function to suggest roles based on skills
            function suggestRoles() {
                if (selectedSkillsSet.size > 0) {
                    fetch('suggest_roles.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            skills: Array.from(selectedSkillsSet)
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success && Object.keys(data.suggestions).length > 0) {
                            suggestedRoles.innerHTML = '';
                            let highestMatch = null;
                            let highestPercentage = 0;

                            Object.entries(data.suggestions).forEach(([role, percentage]) => {
                                const roleItem = document.createElement('a');
                                roleItem.href = '#';
                                roleItem.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center';
                                roleItem.innerHTML = `
                                    ${role}
                                    <span class="badge bg-primary rounded-pill">${Math.round(percentage)}% match</span>
                                `;
                                suggestedRoles.appendChild(roleItem);

                                // Track highest match
                                if (percentage > highestPercentage) {
                                    highestPercentage = percentage;
                                    highestMatch = role;
                                }
                            });

                            // Update designation field with highest match
                            if (highestMatch && highestPercentage >= 30) { // Only update if match is at least 30%
                                const designationInput = document.getElementById('designation');
                                if (designationInput && !designationInput.value) { // Only update if field is empty
                                    designationInput.value = highestMatch;
                                }
                            }

                            roleSuggestions.style.display = 'block';
                        } else {
                            roleSuggestions.style.display = 'none';
                        }
                    })
                    .catch(error => console.error('Error suggesting roles:', error));
                } else {
                    roleSuggestions.style.display = 'none';
                }
            }

            // Add click handler for role suggestions
            document.addEventListener('DOMContentLoaded', function() {
                const suggestedRoles = document.getElementById('suggestedRoles');
                if (suggestedRoles) {
                    suggestedRoles.addEventListener('click', function(e) {
                        if (e.target.tagName === 'A') {
                            e.preventDefault();
                            const role = e.target.textContent.trim();
                            const designationInput = document.getElementById('designation');
                            if (designationInput) {
                                designationInput.value = role;
                            }
                        }
                    });
                }
            });

            // Make removeSkill function global
            window.removeSkill = function(skill) {
                selectedSkillsSet.delete(skill);
                updateSelectedSkills();
                suggestRoles();
            };
        });

        document.getElementById('resumeForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Collect all skills
            const skills = [];
            document.querySelectorAll('#selectedSkills .badge').forEach(badge => {
                skills.push({
                    name: badge.textContent.trim(),
                    level: 5 // Default proficiency level
                });
            });
            
            // Add skills to form data
            const formData = new FormData(this);
            formData.append('skills', JSON.stringify(skills));
            
            // Submit form
            fetch('save_resume.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.href = 'preview_resume.php?id=' + data.resume_id;
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while saving the resume.');
            });
        });
    </script>
</body>
</html> 