<?php
include("database.inc");

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
        
        $fileName = uniqid() . '_' . basename($_FILES['gambarKenderaan']['name']);
        $targetFile = $targetDir . $fileName;
        $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        date_default_timezone_set('Asia/Kuala_Lumpur');
        $createdDate = date("Y-m-d H:i:s");
        
        // Validate file type
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
        if(!in_array($fileType, $allowedTypes)) {
            throw new Exception("Only JPG, JPEG, PNG, PDF & GIF files are allowed.");
        }

        // Move uploaded file first
        if(!move_uploaded_file($_FILES["gambarKenderaan"]["tmp_name"], $targetFile)) {
            throw new Exception("Failed to upload image.");
        }
        
        // Compress/resize image if it's an image file (not PDF)
        $maxFileSize = 1 * 1024 * 1024; // 1MB in bytes
        if(in_array($fileType, ['jpg', 'jpeg', 'png', 'gif']) && $fileType != 'pdf') {
            $targetFile = compressAndResizeImage($targetFile, $maxFileSize);
        }
        
        // Check final file size
        if(filesize($targetFile) > $maxFileSize) {
            throw new Exception("Unable to reduce file size below 1MB. Please try with a different image.");
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
        // Clean up if file was uploaded but something else failed
        if(isset($targetFile) && file_exists($targetFile)) {
            unlink($targetFile);
        }
        // Handle errors
        $error = "Error: " . $e->getMessage();
    }
}

/**
 * Automatically compress and resize image to be under maxFileSize
 */
function compressAndResizeImage($sourcePath, $maxFileSize) {
    $quality = 85; // Start with this quality
    $minQuality = 30; // Don't go below this quality
    $maxWidth = 2000; // Maximum width to prevent excessive processing
    
    // Get original image info
    $info = getimagesize($sourcePath);
    $mime = $info['mime'];
    
    // Create image based on mime type
    switch($mime) {
        case 'image/jpeg':
            $image = imagecreatefromjpeg($sourcePath);
            break;
        case 'image/png':
            $image = imagecreatefrompng($sourcePath);
            break;
        case 'image/gif':
            $image = imagecreatefromgif($sourcePath);
            break;
        default:
            return $sourcePath;
    }
    
    // Get original dimensions
    $originalWidth = $info[0];
    $originalHeight = $info[1];
    
    // Temporary file for testing size
    $tempFile = tempnam(sys_get_temp_dir(), 'img') . '.jpg';
    
    // Try different quality levels
    while ($quality >= $minQuality) {
        // Save with current quality
        imagejpeg($image, $tempFile, $quality);
        
        // Check if file size is acceptable
        if(filesize($tempFile) <= $maxFileSize) {
            // If yes, replace original with compressed version
            rename($tempFile, $sourcePath);
            imagedestroy($image);
            return $sourcePath;
        }
        
        // If still too big, try reducing dimensions
        if($quality <= 70) {
            $ratio = sqrt($maxFileSize / filesize($tempFile));
            $newWidth = min($maxWidth, floor($originalWidth * $ratio));
            $newHeight = floor($originalHeight * ($newWidth / $originalWidth));
            
            // Create resized image
            $resizedImage = imagecreatetruecolor($newWidth, $newHeight);
            imagecopyresampled($resizedImage, $image, 0, 0, 0, 0, $newWidth, $newHeight, $originalWidth, $originalHeight);
            
            // Save resized image
            imagejpeg($resizedImage, $tempFile, $quality);
            imagedestroy($resizedImage);
            
            if(filesize($tempFile) <= $maxFileSize) {
                rename($tempFile, $sourcePath);
                imagedestroy($image);
                return $sourcePath;
            }
        }
        
        // Reduce quality for next iteration
        $quality -= 5;
    }
    
    // Clean up
    if(file_exists($tempFile)) {
        unlink($tempFile);
    }
    imagedestroy($image);
    
    // If we get here, we couldn't reduce enough
    throw new Exception("Unable to reduce image below 1MB while maintaining acceptable quality.");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register form</title>
    <link rel="stylesheet" href="register.css">
</head>
<body>
    <div class="container">
        <h1>Register Form</h1>
        <?php if(isset($success)): ?>
            <div style="background-color: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>
        <?php if(isset($error)): ?>
            <div style="background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin-bottom: 20px;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>
        <form action="register.php" method="post" enctype="multipart/form-data">
            <label>Full name</label>
            <input name="fName" type="text" required>
            
            <label>Alamat Kediaman penerima</label>
            <select name="destination" required>
                <option value="">Select an option</option>
                <option value="TERES SEKSYEN 9">TERES SEKSYEN 9</option>
                <option value="RAJAWALI">RAJAWALI</option>
                <option value="MERPATI">MERPATI</option>
            </select>
            
            <label>No. Alamat Penuh Kediaman</label>
            <input name="alamat" type="text" required>
            
            <label>Nombor Kenderaan</label>
            <input name="noKenderaan" type="text" required>
            
            <label>Gambar Kad Pengenalan</label>
            <input name="gambarKenderaan" type="file" required accept="image/*,.pdf">
            <small>Images will be automatically resized to under 1MB</small>
            
            <button name="submitButton" type="submit">Submit</button>
        </form>
    </div>
</body>
</html>