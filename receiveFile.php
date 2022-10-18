<?php
header('Access-Control-Allow-Origin: *');

if (isset($_FILES["file"])) {
    $periodIndex = strpos(($_FILES["file"]["name"]), ".");
    $extension = substr($_FILES["file"]["name"], $periodIndex, strlen($_FILES["file"]["name"]));
    // $path = "../images/" . basename($file);

    $files = glob('./logo/*'); // get all file names
    foreach ($files as $file) { // iterate files
        if (is_file($file)) {
            unlink($file); // delete file
        }
    }

    $file = $_FILES['file']['name'];

    if ($extension === ".svg" || $extension === ".png" || $extension === ".jpg") {
        $targetPath = "./logo/" . "logo" . $extension;

        move_uploaded_file($_FILES["file"]["tmp_name"], $targetPath);
        error_log("File move: " . $file . " to path: " . $targetPath);
        echo json_encode(['success', $extension]);
    } else {
        error_log("Invalid file format for: " . $file);
        echo json_encode(['failure', '']);
    }
}
