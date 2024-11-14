<?php
// Check if the file name is provided
if (isset($_GET['file'])) {
    $file = basename($_GET['file']); // Sanitize the file input to avoid directory traversal
    $filePath = 'uploadsfile/' . $file;

    // Check if the file exists
    if (file_exists($filePath)) {
        $fileExtension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));

        // Define content types and disposition
        $contentTypes = [
            'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'pdf' => 'application/pdf',
            'xls' => 'application/vnd.ms-excel',
            'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
        ];

        // Check if the file is a PDF or other types
        if ($fileExtension === 'pdf') {
            // Output an HTML page with the file displayed in an iframe
            ?>
            
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="UTF-8">

                <meta name="viewport" content="width=device-width, initial-scale=1.0">

                <title>CES PLP</title>

                <link rel="icon" href="images/logoicon.png">
                <style>
                    body, html {
                        margin: 0;
                        padding: 0;
                        height: 100%;
                    }
                    iframe {
                        width: 100%;
                        height: 100vh;
                        border: none;
                    }
                </style>
            </head>
            <body>
                <iframe src="<?php echo $filePath; ?>" frameborder="0"></iframe>
            </body>
            </html>
            <?php
        } else {
            // Handle other file types by forcing a download
            $contentType = $contentTypes[$fileExtension] ?? 'application/octet-stream';
            $disposition = 'attachment';

            // Set headers for download
            header('Content-Type: ' . $contentType);
            header('Content-Disposition: ' . $disposition . '; filename="' . basename($file) . '"');
            header('Content-Length: ' . filesize($filePath));

            // Output the file for download
            readfile($filePath);
            exit;
        }
    } else {
        echo "File not found!";
    }
} else {
    echo "No file specified!";
}
?>
