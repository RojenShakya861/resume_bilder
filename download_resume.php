<?php
// Prevent any output before PDF generation
ob_start();

require_once 'include/config.php';
require_once 'vendor/tecnickcom/tcpdf/tcpdf.php';

if (!is_logged_in()) {
    ob_end_clean();
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
    ob_end_clean();
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

try {
    // Create new PDF document
    class MYPDF extends TCPDF {
        public function Header() {
            // Logo
            // $this->Image('logo.png', 10, 10, 15);
            // Set font
            $this->SetFont('helvetica', 'B', 20);
            // Title
            $this->Cell(0, 15, '', 0, false, 'C', 0, '', 0, false, 'M', 'M');
        }
    }

    // Create new PDF document
    $pdf = new MYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

    // Set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor($resume['full_name']);
    $pdf->SetTitle($resume['title']);

    // Remove default header/footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);

    // Set margins
    $pdf->SetMargins(15, 15, 15);
    $pdf->SetHeaderMargin(5);
    $pdf->SetFooterMargin(10);

    // Set auto page breaks
    $pdf->SetAutoPageBreak(TRUE, 15);

    // Add a page
    $pdf->AddPage();

    // Set font
    $pdf->SetFont('helvetica', '', 11);

    // Name and Designation
    $pdf->SetFont('helvetica', 'B', 24);
    $pdf->Cell(0, 10, $resume['full_name'], 0, 1, 'C');
    $pdf->SetFont('helvetica', 'I', 14);
    $pdf->Cell(0, 10, $resume['designation'], 0, 1, 'C');
    $pdf->Ln(5);

    // Contact Information
    $pdf->SetFont('helvetica', '', 11);
    $contact_info = $resume['email'] . ' | ' . $resume['phone'] . ' | ' . $resume['address'];
    $pdf->Cell(0, 10, $contact_info, 0, 1, 'C');
    $pdf->Ln(5);

    // Professional Summary
    if ($resume['summary']) {
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, 'Professional Summary', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 11);
        $pdf->MultiCell(0, 10, $resume['summary'], 0, 'L');
        $pdf->Ln(5);
    }

    // Education
    if ($education) {
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, 'Education', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 11);
        
        foreach ($education as $edu) {
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 10, $edu['degree'], 0, 1, 'L');
            $pdf->SetFont('helvetica', '', 11);
            $pdf->Cell(0, 10, $edu['institution'], 0, 1, 'L');
            
            $date = date('M Y', strtotime($edu['start_date'])) . ' - ' . 
                    ($edu['end_date'] ? date('M Y', strtotime($edu['end_date'])) : 'Present');
            $pdf->Cell(0, 10, $date, 0, 1, 'L');
            
            if ($edu['description']) {
                $pdf->MultiCell(0, 10, $edu['description'], 0, 'L');
            }
            $pdf->Ln(5);
        }
    }

    // Experience
    if ($experience) {
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, 'Experience', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 11);
        
        foreach ($experience as $exp) {
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 10, $exp['position'], 0, 1, 'L');
            $pdf->SetFont('helvetica', '', 11);
            $pdf->Cell(0, 10, $exp['company'], 0, 1, 'L');
            
            $date = date('M Y', strtotime($exp['start_date'])) . ' - ' . 
                    ($exp['end_date'] ? date('M Y', strtotime($exp['end_date'])) : 'Present');
            $pdf->Cell(0, 10, $date, 0, 1, 'L');
            
            if ($exp['description']) {
                $pdf->MultiCell(0, 10, $exp['description'], 0, 'L');
            }
            $pdf->Ln(5);
        }
    }

    // Skills
    if ($skills) {
        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 10, 'Skills', 0, 1, 'L');
        $pdf->SetFont('helvetica', '', 11);
        
        $skill_text = '';
        foreach ($skills as $skill) {
            $skill_text .= $skill['skill_name'] . ' • ';
        }
        $skill_text = rtrim($skill_text, ' • ');
        $pdf->MultiCell(0, 10, $skill_text, 0, 'L');
    }

    // Clear any output buffer
    ob_end_clean();
    
    // Output the PDF
    $pdf->Output($resume['full_name'] . '_Resume.pdf', 'D');

} catch (Exception $e) {
    ob_end_clean();
    echo "Error: " . $e->getMessage();
}
?> 