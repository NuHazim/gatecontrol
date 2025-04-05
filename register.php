<?php
include("database.inc");

function compressImage($source, $destination, $maxFileSize = 2000000) {
    // Get image info
    $imgInfo = getimagesize($source);
    if (!$imgInfo) {
        return false; // Not a valid image
    }
    
    $mime = $imgInfo['mime'];
    
    // Create a new image from file
    switch($mime){
        case 'image/jpeg':
            $image = imagecreatefromjpeg($source);
            break;
        case 'image/png':
            $image = imagecreatefrompng($source);
            // Preserve transparency
            imagealphablending($image, false);
            imagesavealpha($image, true);
            break;
        default:
            return false; // Only process JPEG and PNG
    }
    
    // Initial quality settings
    $quality = 75;
    $success = false;
    
    // Try to compress with decreasing quality until under max size
    for ($i = 0; $i < 5; $i++) {
        if ($mime == 'image/jpeg') {
            $success = imagejpeg($image, $destination, $quality);
        } elseif ($mime == 'image/png') {
            // PNG quality is 0-9 (higher means more compression)
            $pngQuality = 9 - round($quality / 11.11); // Map 0-100 to 9-0
            $success = imagepng($image, $destination, $pngQuality);
        }
        
        if (!$success) {
            break;
        }
        
        $currentSize = filesize($destination);
        if ($currentSize <= $maxFileSize) {
            break;
        }
        
        $quality -= 15; // Reduce quality for next attempt
        if ($quality < 10) $quality = 10;
    }
    
    imagedestroy($image);
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
        
        $originalFileName = basename($_FILES['gambarKenderaan']['name']);
        $fileType = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));
        $fileName = uniqid() . '_' . $originalFileName;
        $targetFile = $targetDir . $fileName;
        
        date_default_timezone_set('Asia/Kuala_Lumpur');
        $createdDate = date("Y-m-d H:i:s");
        
        // Validate file upload
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
        if(!in_array($fileType, $allowedTypes)) {
            throw new Exception("Only JPG, JPEG, PNG, PDF & GIF files are allowed.");
        }

        // Handle image upload differently from PDF
        $tempFile = $_FILES['gambarKenderaan']['tmp_name'];
        
        if (in_array($fileType, ['jpg', 'jpeg', 'png'])) {
            // Compress image to under 100KB
            if (!compressImage($tempFile, $targetFile)) {
                throw new Exception("Failed to compress image or image is still too large after compression.");
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
            unlink($targetFile);
        }
    }
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
            <small>Note: Images will be automatically compressed to under 100KB</small>
            
            <button name="submitButton" type="submit">Submit</button>
        </form>
    </div>
</body>
</html>