<?php
/**
 * Virtual directory handler for post-type images
 * Serves images from /src/assets/images/post-types/ via /post-types/{filename}
 */

// Get the requested filename from the URL path
$requestUri = $_SERVER['REQUEST_URI'];
$path = parse_url($requestUri, PHP_URL_PATH);

// Extract filename from /post-types/{filename}
if (preg_match('#^/post-types/([^/]+)$#', $path, $matches)) {
    $filename = $matches[1];
} else {
    http_response_code(404);
    exit('Not Found');
}

// Security: Only allow specific image extensions
$allowedExtensions = ['png', 'jpg', 'jpeg', 'gif', 'svg', 'webp'];
$extension = strtolower(pathinfo($filename, PATHINFO_EXTENSION));

if (!in_array($extension, $allowedExtensions)) {
    http_response_code(403);
    exit('Forbidden file type');
}

// Security: Prevent directory traversal
if (strpos($filename, '..') !== false || strpos($filename, '/') !== false || strpos($filename, '\\') !== false) {
    http_response_code(403);
    exit('Invalid filename');
}

// Build the actual file path
$filePath = __DIR__ . '/../assets/post-types/' . $filename;

// Check if file exists
if (!file_exists($filePath) || !is_file($filePath)) {
    http_response_code(404);
    exit('File not found');
}

// Get file info
$fileSize = filesize($filePath);
$lastModified = filemtime($filePath);

// Set appropriate headers
header('Content-Type: ' . getMimeType($extension));
header('Content-Length: ' . $fileSize);
header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $lastModified) . ' GMT');
header('Cache-Control: public, max-age=31536000'); // Cache for 1 year
header('Expires: ' . gmdate('D, d M Y H:i:s', time() + 31536000) . ' GMT');

// Handle conditional requests
if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE'])) {
    $ifModifiedSince = strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']);
    if ($lastModified <= $ifModifiedSince) {
        http_response_code(304);
        exit();
    }
}

// Serve the file
readfile($filePath);

/**
 * Get MIME type for image extensions
 */
function getMimeType(string $extension): string
{
    return match($extension) {
        'png' => 'image/png',
        'jpg', 'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'webp' => 'image/webp',
        default => 'application/octet-stream'
    };
} 