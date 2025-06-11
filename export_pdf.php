<?php
require_once 'include/config.php';
require_once 'vendor/autoload.php'; // Make sure to install TCPDF via Composer

use TCPDF;

// Check if user is logged in
if (!is_logged_in()) {
    redirect('auth/login.php');
}

// Check if resume ID is provided
if (!isset($_GET['id'])) {
    redirect('index.php');
}

$resume_id = (int)$_GET['id'];

// Get resume details
$stmt = $conn->prepare("SELECT r.*, t.html_content, t.css_content 
                       FROM resumes r 
                       LEFT JOIN templates t ON r.template_id = t.id 
                       WHERE r.id = ? AND r.user_id = ?");
$stmt->execute([$resume_id, $_SESSION['user_id']]);
$resume = $stmt->fetch();

if (!$resume) {
    redirect('index.php');
}

// Get education details
$stmt = $conn->prepare("SELECT * FROM education WHERE resume_id = ? ORDER BY end_date DESC");
$stmt->execute([$resume_id]);
$education = $stmt->fetchAll();

// Get experience details
$stmt = $conn->prepare("SELECT * FROM experience WHERE resume_id = ? ORDER BY end_date DESC");
$stmt->execute([$resume_id]);
$experience = $stmt->fetchAll();

// Get skills
$stmt = $conn->prepare("SELECT * FROM skills WHERE resume_id = ?");
$stmt->execute([$resume_id]);
$skills = $stmt->fetchAll();

// Create new PDF document
$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// Set document information
$pdf->SetCreator(PDF_CREATOR);
$pdf->SetAuthor($resume['full_name']);
$pdf->SetTitle($resume['title']);

// Remove default header/footer
$pdf->setPrintHeader(false);
$pdf->setPrintFooter(false);

// Set margins
$pdf->SetMargins(15, 15, 15);

// Add a page
$pdf->AddPage();

// Set font
$pdf->SetFont('helvetica', '', 12);

// Add content
$html = '<h1 style="text-align:center;color:#6c5ce7;">' . htmlspecialchars($resume['title']) . '</h1>';
$html .= '<h2 style="color:#2d3436;">' . htmlspecialchars($resume['full_name']) . '</h2>';
$html .= '<p>' . htmlspecialchars($resume['email']) . ' | ' . htmlspecialchars($resume['phone']) . '</p>';
$html .= '<p>' . htmlspecialchars($resume['address']) . '</p>';

// Summary
$html .= '<h3 style="color:#6c5ce7;">Professional Summary</h3>';
$html .= '<p>' . nl2br(htmlspecialchars($resume['summary'])) . '</p>';

// Education
$html .= '<h3 style="color:#6c5ce7;">Education</h3>';
foreach ($education as $edu) {
    $html .= '<h4>' . htmlspecialchars($edu['institution']) . '</h4>';
    $html .= '<p>' . htmlspecialchars($edu['degree']) . ' in ' . htmlspecialchars($edu['field_of_study']) . '</p>';
    $html .= '<p>' . date('M Y', strtotime($edu['start_date'])) . ' - ' . 
             ($edu['end_date'] ? date('M Y', strtotime($edu['end_date'])) : 'Present') . '</p>';
    if ($edu['description']) {
        $html .= '<p>' . nl2br(htmlspecialchars($edu['description'])) . '</p>';
    }
}

// Experience
$html .= '<h3 style="color:#6c5ce7;">Experience</h3>';
foreach ($experience as $exp) {
    $html .= '<h4>' . htmlspecialchars($exp['company']) . '</h4>';
    $html .= '<p>' . htmlspecialchars($exp['position']) . '</p>';
    $html .= '<p>' . date('M Y', strtotime($exp['start_date'])) . ' - ' . 
             ($exp['end_date'] ? date('M Y', strtotime($exp['end_date'])) : 'Present') . '</p>';
    if ($exp['description']) {
        $html .= '<p>' . nl2br(htmlspecialchars($exp['description'])) . '</p>';
    }
}

// Skills
$html .= '<h3 style="color:#6c5ce7;">Skills</h3>';
$html .= '<p>';
foreach ($skills as $skill) {
    $html .= '<span style="background-color:#6c5ce7;color:white;padding:5px 10px;margin:2px;display:inline-block;border-radius:15px;">' . 
             htmlspecialchars($skill['skill_name']) . '</span> ';
}
$html .= '</p>';

// Output the HTML content
$pdf->writeHTML($html, true, false, true, false, '');

// Close and output PDF document
$pdf->Output($resume['title'] . '.pdf', 'D');
?> 