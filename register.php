<?php
include("database.inc");

// Increase PHP limits for large files (adjust these values as needed)
ini_set('upload_max_filesize', '10M');
ini_set('post_max_size', '12M');
ini_set('memory_limit', '256M');
ini_set('max_execution_time', '300');

function compressImage($source, $destination, $maxFileSize = 1000000) {
    // Get image info
    $imgInfo = @getimagesize($source);
    if (!$imgInfo) {
        return false; // Not a valid image
    }
    
    list($width, $height) = $imgInfo;
    $mime = $imgInfo['mime'];
    
    // Calculate new dimensions to reduce size (max 1200px on the longest side)
    $maxDimension = 1200;
    if ($width > $height && $width > $maxDimension) {
        $newWidth = $maxDimension;
        $newHeight = intval($height * ($maxDimension / $width));
    } elseif ($height > $maxDimension) {
        $newHeight = $maxDimension;
        $newWidth = intval($width * ($maxDimension / $height));
    } else {
        $newWidth = $width;
        $newHeight = $height;
    }
    
    // Create a new image from file
    switch($mime){
        case 'image/jpeg':
            $image = @imagecreatefromjpeg($source);
            break;
        case 'image/png':
            $image = @imagecreatefrompng($source);
            // Preserve transparency
            imagealphablending($image, false);
            imagesavealpha($image, true);
            break;
        default:
            return false; // Only process JPEG and PNG
    }
    
    if (!$image) return false;
    
    // Create new image with adjusted dimensions
    $newImage = imagecreatetruecolor($newWidth, $newHeight);
    
    // Preserve transparency for PNG
    if ($mime == 'image/png') {
        imagealphablending($newImage, false);
        imagesavealpha($newImage, true);
        $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
        imagefilledrectangle($newImage, 0, 0, $newWidth, $newHeight, $transparent);
    }
    
    // Resize the image
    imagecopyresampled($newImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    
    // Compression settings
    $quality = 80; // Start with decent quality
    $success = false;
    
    // Try to compress with decreasing quality until under max size
    for ($i = 0; $i < 5; $i++) {
        if ($mime == 'image/jpeg') {
            $success = imagejpeg($newImage, $destination, $quality);
        } elseif ($mime == 'image/png') {
            // PNG quality is 0-9 (higher means more compression)
            $pngQuality = 9 - round($quality / 11.11); // Map 0-100 to 9-0
            $success = imagepng($newImage, $destination, $pngQuality);
        }
        
        if (!$success) break;
        
        clearstatcache(); // Clear cached filesize
        $currentSize = filesize($destination);
        if ($currentSize <= $maxFileSize) break;
        
        $quality -= 15; // Reduce quality for next attempt
        if ($quality < 10) $quality = 10;
    }
    
    imagedestroy($image);
    imagedestroy($newImage);
    
    clearstatcache();
    return $success && filesize($destination) <= $maxFileSize;
}

if(isset($_POST['submitButton'])) {
    try {
        // Get form data
        $name = $_POST['fName'];
        $destination = $_POST['destination'];
        $alamat = $_POST['alamat'];
        $noKenderaan = $_POST['noKenderaan'];
        
        // File upload handling
        $targetDir = "pictureFiles/";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        
        // Validate file upload
        if (!isset($_FILES['gambarKenderaan']) || $_FILES['gambarKenderaan']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("File upload failed. Error code: " . $_FILES['gambarKenderaan']['error']);
        }
        
        $originalFileName = basename($_FILES['gambarKenderaan']['name']);
        $fileType = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));
        $fileName = uniqid() . '_' . $originalFileName;
        $targetFile = $targetDir . $fileName;
        $tempFile = $_FILES['gambarKenderaan']['tmp_name'];
        
        date_default_timezone_set('Asia/Kuala_Lumpur');
        $createdDate = date("Y-m-d H:i:s");
        
        // Validate file type
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
        if(!in_array($fileType, $allowedTypes)) {
            throw new Exception("Only JPG, JPEG, PNG, PDF & GIF files are allowed.");
        }

        // Handle image upload differently from PDF/GIF
        if (in_array($fileType, ['jpg', 'jpeg', 'png'])) {
            // Compress image to under 100KB
            if (!compressImage($tempFile, $targetFile)) {
                throw new Exception("Failed to process image. Please try with a smaller file or different format.");
            }
        } else {
            // For PDF and GIF, just move the file (no compression)
            if (!move_uploaded_file($tempFile, $targetFile)) {
                throw new Exception("Failed to upload file.");
            }
        }
        
        // Prepare SQL with PDO
        $sql = "INSERT INTO registeredlist (name, destination, alamat, noKenderaan, gambarDir, createdDate) 
                VALUES (:name, :destination, :alamat, :noKenderaan, :gambarDir, :createdDate)";
        
        $stmt = $pdo->prepare($sql);
        
        // Bind parameters
        $stmt->bindParam(':name', $name);
        $stmt->bindParam(':destination', $destination);
        $stmt->bindParam(':alamat', $alamat);
        $stmt->bindParam(':noKenderaan', $noKenderaan);
        $stmt->bindParam(':gambarDir', $targetFile);
        $stmt->bindParam(':createdDate', $createdDate);
        
        // Execute the statement
        $stmt->execute();
        
        // Success message
        $success = "Registration successful!";
        
    } catch (Exception $e) {
        // Handle errors
        $error = "Error: " . $e->getMessage();
        
        // Clean up if there was an error after file upload but before DB insertion
        if (isset($targetFile) && file_exists($targetFile)) {
            @unlink($targetFile);
        }
    }
}
?>