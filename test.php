<?php
require 'vendor/autoload.php'; // Ensure you have Dompdf installed via Composer

use Dompdf\Dompdf;
use Dompdf\Options;

// Set up Dompdf options
$options = new Options();
$options->set('isHtml5ParserEnabled', true);  // Enable HTML5 support
$options->set('isRemoteEnabled', true);       // Enable fetching remote content (like images)

// Create a new instance of Dompdf with the options
$dompdf = new Dompdf($options);

// HTML content with absolute URLs for images
$html = "
<!DOCTYPE html>
<html lang='en'>
<head>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width, initial-scale=1.0'>
    <title>Certificate</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .certificate {
            text-align: center;
            background: url('https://ces-plp-d5e378ca4d4d.herokuapp.com/images/cert-bg.png') no-repeat center center;
            background-size: cover;
            height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            color: #333;
        }
        .logo {
            margin-bottom: 20px;
        }
        .title {
            font-size: 36px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .subtitle {
            font-size: 18px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class='certificate'>
        <div class='logo'>
            <img src='https://ces-plp-d5e378ca4d4d.herokuapp.com/images/logoicon.png' alt='Logo' width='100'>
        </div>
        <div class='title'>Certificate of Achievement</div>
        <div class='subtitle'>This certifies that the holder has successfully completed the program.</div>
    </div>
</body>
</html>
";

// Load the HTML content into Dompdf
$dompdf->loadHtml($html);

// Set paper size and orientation (optional)
$dompdf->setPaper('A4', 'landscape');

// Render the HTML as PDF
$dompdf->render();

// Output the generated PDF (stream to browser)
$dompdf->stream("certificate.pdf", ["Attachment" => false]); // Set to true for download, false to display in browser
