<?php
error_log(print_r(getallheaders(), true));

// Hardcoded tokens
$validTokens = [
    "token1" => "example",  // Replace with your actual tokens
    "token2" => "exampe1",
    "token3" => "example2"
];

// Set the directory where files will be stored
$uploadDir = __DIR__ . '/uploads/';

// Create the directory if it doesn't exist
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Allowed file types
$allowedFileTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf', 'text/plain'];

// Function to verify the token
function isTokenValid($token, $validTokens) {
    return in_array($token, $validTokens);
}

// Establish DB connection - Insert your credentials
$pdo = new PDO('mysql:host=mysql.example.com;dbname=db', 'username', 'password');

// Endpoint for file upload
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if token is provided in the header
    $headers = getallheaders();
    if (!isset($headers['X-Authorization'])) {
        http_response_code(401);
        echo json_encode(['error' => 'Authorization token not provided.']);
        exit;
    }

    // Extract token from the 'X-Authorization' header
    $providedToken = str_replace('Bearer ', '', $headers['X-Authorization']);

    // Validate the token
    if (!isTokenValid($providedToken, $validTokens)) {
        http_response_code(403);
        echo json_encode(['error' => 'Invalid token.']);
        exit;
    }

    if (isset($_FILES['file'])) {
        $file = $_FILES['file'];

        // Check if the file type is allowed
        if (!in_array($file['type'], $allowedFileTypes)) {
            http_response_code(400);
            echo json_encode(['error' => 'File type not allowed.']);
            exit;
        }

        // Sanitize the file name
        $filename = basename($file['name']);
        $filename = preg_replace('/[^A-Za-z0-9.\\-_]/', '', $filename);

        // Prevent uploading dangerous file extensions
        $forbiddenExtensions = ['php', 'sql', 'exe', 'sh', 'bat','py'];
        $fileExtension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

        if (in_array($fileExtension, $forbiddenExtensions)) {
            http_response_code(400);
            echo json_encode(['error' => 'File type not allowed.']);
            exit;
        }

        // Create a unique name for the file to avoid overwriting existing files
        $newFileName = uniqid() . '_' . $filename;
        $targetFile = $uploadDir . $newFileName;

        // Move the file to the upload directory
        if (move_uploaded_file($file['tmp_name'], $targetFile)) {
            // Generate the URL to access the file
            $fileUrl = $_SERVER['REQUEST_SCHEME'] . '://' . $_SERVER['HTTP_HOST'] . '/uploads/' . $newFileName;

            // Insert file record into the database
            $stmt = $pdo->prepare("INSERT INTO image_api (url) VALUES (:url)");
            $stmt->bindParam(':url', $fileUrl);
            $stmt->execute();

            // Get the ID of the inserted row
            $lastInsertId = $pdo->lastInsertId();

            // Respond with the file ID and URL
            echo json_encode(['file_id' => $lastInsertId, 'file_url' => $fileUrl]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to upload file.']);
        }
    } else {
        http_response_code(400);
        echo json_encode(['error' => 'No file uploaded.']);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed.']);
}