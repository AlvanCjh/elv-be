<?php
header('Content-Type: text/html');
echo "<h3>PHP Upload Diagnostics</h3><pre>";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "post_max_size: " . ini_get('post_max_size') . "\n";
echo "max_execution_time: " . ini_get('max_execution_time') . "\n";
echo "memory_limit: " . ini_get('memory_limit') . "\n";
echo "sys_get_temp_dir: " . sys_get_temp_dir() . "\n";

$tmpDir = sys_get_temp_dir();
$storagePath = __DIR__ . '/storage/app/public';
echo "Storage path: " . $storagePath . "\n";
echo "Storage path permissions: " . (file_exists($storagePath) ? substr(sprintf('%o', fileperms($storagePath)), -4) : 'N/A') . "\n";

echo "\n--- Upload Test ---\n";
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['test_file'])) {
    $file = $_FILES['test_file'];
    echo "Upload Attempted:\n";
    echo "Name: " . $file['name'] . "\n";
    echo "Error Code: " . $file['error'] . "\n";
    echo "Size: " . $file['size'] . " bytes\n";
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        $dest = $storagePath . '/' . basename($file['name']);
        if (@move_uploaded_file($file['tmp_name'], $dest)) {
            echo "SUCCESS! File moved to: $dest\n";
            unlink($dest); // Clean up
        } else {
            echo "FAILED to move file. This usually means a permission error or disk quota issue.\n";
            $error = error_get_last();
            echo "Error details: " . ($error ? $error['message'] : 'Unknown') . "\n";
        }
    } else {
        echo "Upload failed with PHP error code: " . $file['error'] . "\n";
    }
}
echo "</pre><hr>";
?>
<h3>Test Direct File Upload</h3>
<p>If you select an image and click Test Upload, and the page <b>crashes or times out</b>, your server's ModSecurity firewall is blocking the upload.</p>
<form method="POST" enctype="multipart/form-data">
    <input type="file" name="test_file" required>
    <button type="submit">Test Upload</button>
</form>
