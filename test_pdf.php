<?php
// Prevent any output before PDF generation
ob_start();

require_once 'include/config.php';
require_once 'vendor/tecnickcom/tcpdf/tcpdf.php';

try {
    // Create new PDF document
    $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
    
    // Set document information
    $pdf->SetCreator(PDF_CREATOR);
    $pdf->SetAuthor('Test User');
    $pdf->SetTitle('Test PDF');
    
    // Remove default header/footer
    $pdf->setPrintHeader(false);
    $pdf->setPrintFooter(false);
    
    // Add a page
    $pdf->AddPage();
    
    // Set font
    $pdf->SetFont('helvetica', '', 12);
    
    // Add content
    $pdf->Cell(0, 10, 'TCPDF is working correctly!', 0, 1, 'C');
    $pdf->Ln(10);
    $pdf->Cell(0, 10, 'You can now generate PDF resumes.', 0, 1, 'C');
    
    // Clear any output buffer
    ob_end_clean();
    
    // Output the PDF
    $pdf->Output('test.pdf', 'D');
    
} catch (Exception $e) {
    ob_end_clean();
    echo "Error: " . $e->getMessage();
}
?> 