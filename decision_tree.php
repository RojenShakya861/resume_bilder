<?php
class DecisionTree {
    private $jobRoles = [
        'Software Developer' => ['HTML', 'CSS', 'JavaScript', 'Python', 'Java', 'C++', 'Git', 'SQL', 'React', 'Node.js'],
        'Web Developer' => ['HTML', 'CSS', 'JavaScript', 'PHP', 'MySQL', 'React', 'Angular', 'Vue.js', 'Git', 'REST API'],
        'Data Scientist' => ['Python', 'R', 'SQL', 'Machine Learning', 'Statistics', 'Data Analysis', 'Pandas', 'NumPy', 'TensorFlow', 'Jupyter'],
        'DevOps Engineer' => ['Linux', 'Docker', 'Kubernetes', 'AWS', 'Azure', 'Jenkins', 'Git', 'Python', 'Shell Scripting', 'CI/CD'],
        'UI/UX Designer' => ['Figma', 'Adobe XD', 'Photoshop', 'Illustrator', 'HTML', 'CSS', 'JavaScript', 'User Research', 'Wireframing', 'Prototyping'],
        'Project Manager' => ['Project Management', 'Agile', 'Scrum', 'JIRA', 'Leadership', 'Communication', 'Risk Management', 'Budgeting', 'Team Management'],
        'Business Analyst' => ['SQL', 'Excel', 'Tableau', 'Power BI', 'Requirements Gathering', 'Documentation', 'Process Modeling', 'Stakeholder Management'],
        'Mobile Developer' => ['Swift', 'Kotlin', 'Java', 'React Native', 'Flutter', 'iOS', 'Android', 'REST API', 'Git', 'Mobile UI/UX'],
        'Database Administrator' => ['SQL', 'MySQL', 'PostgreSQL', 'MongoDB', 'Database Design', 'Performance Tuning', 'Backup/Recovery', 'Security'],
        'Network Engineer' => ['Cisco', 'Network Security', 'TCP/IP', 'Routing', 'Switching', 'Firewall', 'VPN', 'Wireshark', 'Linux', 'Troubleshooting'],
        'Cloud Architect' => ['AWS', 'Azure', 'GCP', 'Cloud Security', 'Infrastructure as Code', 'Docker', 'Kubernetes', 'CI/CD', 'Networking'],
        'QA Engineer' => ['Testing', 'Selenium', 'JUnit', 'TestNG', 'API Testing', 'Performance Testing', 'Bug Tracking', 'Test Planning', 'Automation'],
        'Security Engineer' => ['Network Security', 'Penetration Testing', 'Firewall', 'VPN', 'Security Tools', 'Incident Response', 'Risk Assessment'],
        'System Administrator' => ['Linux', 'Windows Server', 'Active Directory', 'PowerShell', 'Bash', 'Networking', 'Security', 'Backup/Recovery'],
        'Data Engineer' => ['Python', 'SQL', 'ETL', 'Data Warehousing', 'Big Data', 'Hadoop', 'Spark', 'Airflow', 'AWS', 'Data Modeling']
    ];

    public function suggestRoles($skills) {
        $scores = [];
        
        // Convert input skills to lowercase for case-insensitive matching
        $inputSkills = array_map('strtolower', $skills);
        
        foreach ($this->jobRoles as $role => $roleSkills) {
            $score = 0;
            $roleSkillsLower = array_map('strtolower', $roleSkills);
            
            // Calculate match score
            foreach ($inputSkills as $skill) {
                if (in_array($skill, $roleSkillsLower)) {
                    $score++;
                }
            }
            
            // Calculate percentage match
            $percentage = ($score / count($roleSkills)) * 100;
            if ($percentage > 0) {
                $scores[$role] = $percentage;
            }
        }
        
        // Sort roles by match percentage
        arsort($scores);
        
        // Return top 3 matches
        return array_slice($scores, 0, 3, true);
    }
}
?> 