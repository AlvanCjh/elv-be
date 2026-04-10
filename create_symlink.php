<?php
// Enable errors for testing
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Define relative target and symlink paths dynamically based on subdirectory
$targetFolder = __DIR__ . '/storage/app/public';
$linkFolder = __DIR__ . '/public/storage';

if (file_exists($linkFolder) || is_link($linkFolder)) {
    die("Symlink or directory already exists at $linkFolder");
}

if (symlink($targetFolder, $linkFolder)) {
    echo "Success! The storage/app/public directory has been linked to public/storage.";
} else {
    echo "Symlink failed! Ensure the target directory exists and the server environment allows the symlink() function.";
}
?>
