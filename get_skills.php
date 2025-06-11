<?php
header('Content-Type: application/json');

// Read the skills dataset
$skills = [];
$dataset_path = __DIR__ . '/data/skills_dataset.csv';

if (file_exists($dataset_path) && ($handle = fopen($dataset_path, "r")) !== FALSE) {
    // Skip the header row
    fgetcsv($handle);
    
    while (($data = fgetcsv($handle)) !== FALSE) {
        // Split the skills string into an array
        $jobSkills = explode(',', $data[1]);
        // Trim whitespace from each skill
        $jobSkills = array_map('trim', $jobSkills);
        // Add to our skills array
        $skills = array_merge($skills, $jobSkills);
    }
    fclose($handle);
} else {
    // Fallback to default skills if dataset not found
    $skills = [
        'HTML', 'CSS', 'JavaScript', 'PHP', 'Python', 'Java', 'C++', 'C#',
        'React', 'Angular', 'Vue.js', 'Node.js', 'Express.js', 'Django', 'Laravel',
        'MySQL', 'PostgreSQL', 'MongoDB', 'Git', 'Docker', 'AWS', 'Azure',
        'Project Management', 'Agile', 'Scrum', 'Communication', 'Leadership',
        'Problem Solving', 'Teamwork', 'Time Management', 'Critical Thinking'
    ];
}

// Remove duplicates and sort
$skills = array_unique($skills);
sort($skills);

// Return as JSON
echo json_encode(array_values($skills));
?> 