<?php
require_once "../../config/init.php";
require_once "../auth_session.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $conn->begin_transaction();
    try {
        // 1. Handle Text Inputs
        foreach ($_POST as $key => $value) {
            if ($key === 'update_settings' || strpos($key, 'existing_') === 0) continue;
            
            $clean_value = mysqli_real_escape_string($conn, $value);
            // Insert or Update (Upsert)
            $sql = "INSERT INTO site_settings (setting_key, setting_value) VALUES ('$key', '$clean_value') ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)";
            $conn->query($sql);
        }

        // 2. Handle File Uploads
        if (!empty($_FILES)) {
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            $target_dir = "../../assets/uploads/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }

            foreach ($_FILES as $key => $file) {
                if ($file['error'] === UPLOAD_ERR_OK) {
                    $filename = $file['name'];
                    $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                    
                    if (in_array($ext, $allowed)) {
                        $new_name = $key . '-' . time() . '.' . $ext;
                        $target_file = $target_dir . $new_name;
                        
                        if (move_uploaded_file($file['tmp_name'], $target_file)) {
                            $db_path = "assets/uploads/" . $new_name;
                            $conn->query("UPDATE site_settings SET setting_value='$db_path' WHERE setting_key='$key'");
                        }
                    }
                }
            }
        }

        $conn->commit();
        $_SESSION['status_msg'] = "Konten berhasil diperbarui.";
        $_SESSION['status_type'] = "success";
        
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['status_msg'] = "Gagal memperbarui konten: " . $e->getMessage();
        $_SESSION['status_type'] = "error";
    }
    
    header("Location: index.php");
    exit();
}
?>
