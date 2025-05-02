<?php
require_once __DIR__ . '/url_helpers.php';

function debug_log($message, $data = null) {
    $log_file = dirname(__DIR__) . '/debug.log';
    $timestamp = date('Y-m-d H:i:s');
    $log_message = "[{$timestamp}] {$message}";
    if ($data !== null) {
        $log_message .= ": " . print_r($data, true);
    }
    $log_message .= "\n";
    file_put_contents($log_file, $log_message, FILE_APPEND);
}

function isValidImage($file) {
    debug_log("Checking if image is valid", $file);
    
    // Check if file was uploaded successfully
    if (!is_uploaded_file($file['tmp_name'])) {
        debug_log("File is not an uploaded file");
        return false;
    }

    // Get image info
    $image_info = getimagesize($file['tmp_name']);
    debug_log("Image info", $image_info);
    
    // Check if it's a valid image
    if ($image_info === false) {
        debug_log("Not a valid image");
        return false;
    }

    // Validate MIME type
    $allowed_types = [
        'image/jpeg',
        'image/png',
        'image/gif',
        'image/webp'
    ];
    
    if (!in_array($image_info['mime'], $allowed_types)) {
        debug_log("Invalid MIME type: " . $image_info['mime']);
        return false;
    }

    // Validate file size (max 10MB)
    if ($file['size'] > 10 * 1024 * 1024) {
        debug_log("File too large: " . $file['size']);
        return false;
    }

    debug_log("Image is valid");
    return true;
}

function resizeImage($source_path, $max_width = 1200, $max_height = 800) {
    debug_log("Resizing image", $source_path);
    
    // Check if GD is available
    if (!extension_loaded('gd')) {
        debug_log("GD extension not available, skipping resize");
        return true; // Return true to allow upload without resize
    }
    
    // Determine image type
    $image_info = getimagesize($source_path);
    if ($image_info === false) {
        debug_log("Could not get image info");
        return false;
    }
    
    $mime = $image_info['mime'];
    debug_log("Image type", $mime);

    // Create image resource based on type
    try {
        switch ($mime) {
            case 'image/jpeg':
                $source = @imagecreatefromjpeg($source_path);
                break;
            case 'image/png':
                $source = @imagecreatefrompng($source_path);
                break;
            case 'image/gif':
                $source = @imagecreatefromgif($source_path);
                break;
            case 'image/webp':
                $source = @imagecreatefromwebp($source_path);
                break;
            default:
                debug_log("Unsupported image type");
                return false;
        }
    } catch (Exception $e) {
        debug_log("Error creating image resource: " . $e->getMessage());
        return true; // Allow upload without resize if there's an error
    }

    if (!$source) {
        debug_log("Failed to create image resource");
        return true; // Allow upload without resize if resource creation fails
    }

    // Get original dimensions
    $width = imagesx($source);
    $height = imagesy($source);
    debug_log("Original dimensions", ["width" => $width, "height" => $height]);

    // Calculate new dimensions while maintaining aspect ratio
    $ratio = $width / $height;
    if ($width > $max_width || $height > $max_height) {
        if ($width / $max_width > $height / $max_height) {
            $new_width = $max_width;
            $new_height = $new_width / $ratio;
        } else {
            $new_height = $max_height;
            $new_width = $new_height * $ratio;
        }
    } else {
        $new_width = $width;
        $new_height = $height;
    }
    debug_log("New dimensions", ["width" => $new_width, "height" => $new_height]);

    // Create new image
    $new_image = imagecreatetruecolor($new_width, $new_height);
    if (!$new_image) {
        debug_log("Failed to create new image resource");
        imagedestroy($source);
        return true; // Allow upload without resize
    }

    // Handle transparency for PNG and GIF
    if ($mime == 'image/png' || $mime == 'image/gif') {
        imagecolortransparent($new_image, imagecolorallocatealpha($new_image, 0, 0, 0, 127));
        imagealphablending($new_image, false);
        imagesavealpha($new_image, true);
    }

    // Resize
    if (!imagecopyresampled(
        $new_image, $source, 
        0, 0, 0, 0, 
        $new_width, $new_height, 
        $width, $height
    )) {
        debug_log("Failed to resize image");
        imagedestroy($source);
        imagedestroy($new_image);
        return true; // Allow upload without resize
    }

    // Save resized image
    $result = false;
    try {
        switch ($mime) {
            case 'image/jpeg':
                $result = imagejpeg($new_image, $source_path, 85);
                break;
            case 'image/png':
                $result = imagepng($new_image, $source_path, 9);
                break;
            case 'image/gif':
                $result = imagegif($new_image, $source_path);
                break;
            case 'image/webp':
                $result = imagewebp($new_image, $source_path, 85);
                break;
        }
    } catch (Exception $e) {
        debug_log("Error saving image: " . $e->getMessage());
        $result = false;
    }

    // Clean up
    imagedestroy($source);
    imagedestroy($new_image);

    if (!$result) {
        debug_log("Failed to save resized image");
        return true; // Allow upload without resize if save fails
    }

    debug_log("Image resized successfully");
    return true;
}

function saveImageToDatabase($pdo, $post_id, $file) {
    debug_log("Starting image save process", ["post_id" => $post_id, "file" => $file]);
    
    // Create uploads directory if it doesn't exist
    $uploads_dir = dirname(__DIR__) . '/uploads';
    if (!file_exists($uploads_dir)) {
        debug_log("Creating uploads directory", $uploads_dir);
        if (!mkdir($uploads_dir, 0777, true)) {
            debug_log("Failed to create uploads directory");
            return false;
        }
        chmod($uploads_dir, 0777);
    }

    // Generate unique filename
    $file_extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $unique_filename = uniqid('post_image_') . '.' . $file_extension;
    $upload_path = 'uploads/' . $unique_filename;  // Store relative path in database
    $full_path = $uploads_dir . '/' . $unique_filename;  // Use absolute path for file operations
    debug_log("File paths", ["upload_path" => $upload_path, "full_path" => $full_path]);

    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $full_path)) {
        debug_log("Failed to move uploaded file", ["from" => $file['tmp_name'], "to" => $full_path]);
        return false;
    }
    debug_log("File moved successfully");

    // Try to resize image, but continue even if resize fails
    resizeImage($full_path);

    try {
        // Delete existing image if there is one
        deletePostImage($pdo, $post_id);

        // Insert image record into database
        $stmt = $pdo->prepare("
            INSERT INTO images (post_id, filename, original_filename, file_path, file_size, mime_type) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        
        $params = [
            $post_id, 
            $unique_filename, 
            $file['name'], 
            $upload_path,  // Store the relative path
            filesize($full_path), 
            mime_content_type($full_path)
        ];
        debug_log("Inserting into database", $params);
        
        $stmt->execute($params);
        debug_log("Database insert successful");

        return true;
    } catch (Exception $e) {
        debug_log("Database error", $e->getMessage());
        // Clean up the file if database insert fails
        if (file_exists($full_path)) {
            unlink($full_path);
        }
        return false;
    }
}

function deletePostImage($pdo, $post_id) {
    debug_log("Deleting post image", $post_id);
    
    // Find and delete the image from the database and file system
    $stmt = $pdo->prepare("SELECT file_path FROM images WHERE post_id = ?");
    $stmt->execute([$post_id]);
    $image = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($image) {
        debug_log("Found image to delete", $image);
        // Delete file from filesystem
        $full_path = dirname(__DIR__) . '/' . $image['file_path'];
        if (file_exists($full_path)) {
            unlink($full_path);
            debug_log("Deleted file", $full_path);
        }

        // Delete record from database
        $stmt = $pdo->prepare("DELETE FROM images WHERE post_id = ?");
        $stmt->execute([$post_id]);
        debug_log("Deleted database record");

        return true;
    }

    debug_log("No image found to delete");
    return false;
}

function getPostImage($pdo, $post_id) {
    debug_log("Getting post image", $post_id);
    
    // Ensure the function is defined with explicit error handling
    if (!$pdo || !$post_id) {
        debug_log("Invalid parameters");
        return false;
    }

    try {
        $stmt = $pdo->prepare("SELECT * FROM images WHERE post_id = ? LIMIT 1");
        $stmt->execute([$post_id]);
        $image = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($image) {
            debug_log("Found image", $image);
            // Verify file exists
            $full_path = dirname(__DIR__) . '/' . $image['file_path'];
            if (!file_exists($full_path)) {
                debug_log("Image file not found", $full_path);
                // Remove database entry if file doesn't exist
                $stmt = $pdo->prepare("DELETE FROM images WHERE post_id = ?");
                $stmt->execute([$post_id]);
                return false;
            }
        } else {
            debug_log("No image found");
        }

        return $image ?: false;
    } catch (PDOException $e) {
        debug_log("Database error", $e->getMessage());
        return false;
    } catch (Exception $e) {
        debug_log("Unexpected error", $e->getMessage());
        return false;
    }
}
