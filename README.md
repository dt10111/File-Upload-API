# Secure File Upload API

A PHP-based secure file upload API that handles file uploads with authentication, stores files securely, and maintains a database record of uploaded files.

## Features

- Token-based authentication using Bearer tokens
- File type validation and sanitization
- Secure file storage with unique filenames
- MySQL database integration for file tracking
- Protection against dangerous file uploads
- RESTful API endpoint

## Requirements

- PHP 7.0 or higher
- MySQL database
- Write permissions for the uploads directory
- PDO PHP extension

## Installation

1. Clone this repository to your web server
2. Create a MySQL database and update the connection details in `index.php`:
   ```php
   $pdo = new PDO('mysql:host=your_host;dbname=your_database', 'username', 'password');
   ```
3. Create the required database and table:
   ```sql
   -- Create database (if not exists)
   CREATE DATABASE IF NOT EXISTS playlist_app 
   DEFAULT CHARACTER SET utf8mb4 
   DEFAULT COLLATE utf8mb4_unicode_ci;

   USE playlist_app;

   -- Create the image_api table
   CREATE TABLE `image_api` (
     `id` int NOT NULL AUTO_INCREMENT,
     `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
     `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
     `url` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
     `playlist_id` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
     PRIMARY KEY (`id`)
   ) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
   ```
4. Ensure the `uploads` directory exists and has proper write permissions:
   ```bash
   mkdir uploads
   chmod 755 uploads
   ```

## Configuration

### Authentication Tokens

Update the `$validTokens` array in `index.php` with your secure tokens:

```php
$validTokens = [
    "token1" => "your_secure_token_1",
    "token2" => "your_secure_token_2"
];
```

### Allowed File Types

Modify the `$allowedFileTypes` array to specify which MIME types are accepted:

```php
$allowedFileTypes = [
    'image/jpeg',
    'image/png',
    'image/gif',
    'application/pdf',
    'text/plain'
];
```

## Usage

### Endpoint

`POST /index.php`

### Headers

- `X-Authorization: Bearer your_token_here`
- `Content-Type: multipart/form-data`

### Request Body

- `file`: The file to upload (form-data)

### Example Request

```bash
curl -X POST \
  -H "X-Authorization: Bearer your_token_here" \
  -F "file=@/path/to/your/file.jpg" \
  https://your-domain.com/index.php
```

### Response

Successful upload:
```json
{
    "file_id": "123",
    "file_url": "https://your-domain.com/uploads/unique_filename.jpg"
}
```

Error response:
```json
{
    "error": "Error message here"
}
```

## Security Features

- Token-based authentication
- File extension validation
- MIME type checking
- Filename sanitization
- Prevention of executable file uploads
- Unique filename generation
- Separate upload directory

## Error Codes

- 400: Bad Request (invalid file type or no file uploaded)
- 401: Unauthorized (missing authentication token)
- 403: Forbidden (invalid token)
- 405: Method Not Allowed (non-POST requests)
- 500: Internal Server Error (upload failure)
