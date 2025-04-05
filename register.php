<?php
include("database.inc");

if(isset($_POST['submitButton'])) {
    try {
        // Get form data
        $name = $_POST['fName'];
        $destination = $_POST['destination'];
        $alamat = $_POST['alamat'];
        $noKenderaan = $_POST['noKenderaan'];
        
        // File upload handling - NOW USING THE COMPRESSED IMAGE
        $targetDir = "pictureFiles/";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        
        // Get the compressed file (now from $_FILES['compressedImage'])
        if(!isset($_FILES['compressedImage']) || $_FILES['compressedImage']['error'] !== UPLOAD_ERR_OK) {
            throw new Exception("Please upload a valid image file.");
        }
        
        $file = $_FILES['compressedImage'];
        $fileName = uniqid() . '_' . basename($file['name']);
        $targetFile = $targetDir . $fileName;
        $fileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));
        date_default_timezone_set('Asia/Kuala_Lumpur');
        $createdDate = date("Y-m-d H:i:s");
        
        // Validate file type
        $allowedTypes = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
        if(!in_array($fileType, $allowedTypes)) {
            throw new Exception("Only JPG, JPEG, PNG, PDF & GIF files are allowed.");
        }

        // Move uploaded file
        if(!move_uploaded_file($file["tmp_name"], $targetFile)) {
            throw new Exception("Failed to upload file.");
        }
        
        // Verify file size
        $maxFileSize = 1 * 1024 * 1024; // 1MB in bytes
        if(filesize($targetFile) > $maxFileSize) {
            unlink($targetFile);
            throw new Exception("File is still too large after compression. Please try another file.");
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
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register form</title>
    <link rel="stylesheet" href="register.css">
    <!-- Include CompressorJS library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/CompressorJS/1.2.1/compressor.min.js"></script>
    <style>
        .preview-container {
            margin: 15px 0;
        }
        .preview-image {
            max-width: 200px;
            max-height: 200px;
            display: block;
            margin-top: 10px;
        }
    </style>
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
        <form action="register.php" method="post" enctype="multipart/form-data" id="registrationForm">
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
            <input id="originalImage" type="file" accept="image/*,.pdf" required>
            <input type="file" name="compressedImage" id="compressedImageInput" style="display: none;">
            <small>Images will be automatically compressed to under 1MB</small>
            
            <div class="preview-container">
                <strong>Preview:</strong>
                <img id="imagePreview" class="preview-image" style="display: none;">
                <div id="fileInfo"></div>
            </div>
            
            <button name="submitButton" type="submit" id="submitButton">Submit</button>
        </form>
    </div>

    <script>
        document.getElementById('originalImage').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (!file) return;
            
            // Show preview
            const preview = document.getElementById('imagePreview');
            preview.src = URL.createObjectURL(file);
            preview.style.display = 'block';
            
            // Skip compression for PDFs
            if (file.type === 'application/pdf') {
                // Create a new file input with the original PDF
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(file);
                document.getElementById('compressedImageInput').files = dataTransfer.files;
                document.getElementById('fileInfo').textContent = `PDF file: ${file.name} (${(file.size/1024/1024).toFixed(2)}MB)`;
                return;
            }
            
            // Compress image
            new Compressor(file, {
                quality: 0.6,
                maxWidth: 1024,
                maxHeight: 1024,
                convertSize: 500000,
                success(result) {
                    const compressedFile = new File([result], file.name, {
                        type: result.type,
                        lastModified: Date.now()
                    });
                    
                    const dataTransfer = new DataTransfer();
                    dataTransfer.items.add(compressedFile);
                    document.getElementById('compressedImageInput').files = dataTransfer.files;
                    
                    // Show file info
                    const originalSize = (file.size / 1024 / 1024).toFixed(2);
                    const compressedSize = (result.size / 1024 / 1024).toFixed(2);
                    document.getElementById('fileInfo').textContent = 
                        `Original: ${originalSize}MB → Compressed: ${compressedSize}MB`;
                    
                    // Update preview
                    preview.src = URL.createObjectURL(result);
                },
                error(err) {
                    console.error('Compression error:', err);
                    alert('Error compressing image. Please try another image.');
                }
            });
        });
    </script>
</body>
</html>