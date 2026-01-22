<?php
require("auth_session.php");
require("../config/database.php");

$userId = $_SESSION['user_id'];
$success_msg = '';
$error_msg = '';

// Fetch current user data
$query = "SELECT * FROM users WHERE id = $userId";
$user = $conn->query($query)->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);

    // Update Profile Info
    $sql = "UPDATE users SET full_name='$full_name', username='$username' WHERE id=$userId";

    // Password Change Logic
    if (!empty($_POST['new_password'])) {
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        if ($new_password === $confirm_password) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $sql = "UPDATE users SET full_name='$full_name', username='$username', password='$hashed_password' WHERE id=$userId";
        } else {
            $error_msg = "Password baru dan konfirmasi tidak cocok.";
        }
    }

    if (empty($error_msg)) {
        if ($conn->query($sql)) {
            $success_msg = "Pengaturan berhasil disimpan.";
            // Refresh user data
            $user['full_name'] = $full_name;
            $user['username'] = $username;
            // Update session if username changed
            $_SESSION['username'] = $username;
        } else {
            $error_msg = "Gagal menyimpan perubahan: " . $conn->error;
        }
    }
}
?>
<!DOCTYPE html>
<html class="light" lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Pengaturan - Lapak Bangsawan</title>
    <link rel="icon" href="../assets/images/favicon-laba.png" type="image/x-icon">
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&amp;display=swap"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Round" rel="stylesheet" />
    <script>
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#0d59f2",
                        "surface-light": "#ffffff",
                        "surface-dark": "#1e293b",
                        "background-light": "#f5f6f8",
                        "background-dark": "#101622",
                    },
                    fontFamily: { "display": ["Inter", "sans-serif"] },
                },
            },
        }
    </script>
</head>

<body
    class="bg-background-light dark:bg-background-dark text-slate-600 dark:text-slate-300 font-display antialiased flex h-screen overflow-hidden">
    <?php include("sidebar.php"); ?>

    <main class="flex-1 flex flex-col h-full relative overflow-hidden">
        <?php $page_title = "Pengaturan";
        include("header.php"); ?>

        <div class="flex-1 overflow-auto p-6">
            <div class="w-full max-w-4xl mx-auto">
                <?php if ($success_msg): ?>
                    <div
                        class="auto-close-alert bg-green-100 text-green-700 p-4 rounded-lg mb-6 flex items-center gap-2 transition-opacity duration-500">
                        <span class="material-icons-round">check_circle</span>
                        <?php echo $success_msg; ?>
                    </div>
                <?php endif; ?>

                <?php if ($error_msg): ?>
                    <div
                        class="auto-close-alert bg-red-100 text-red-700 p-4 rounded-lg mb-6 flex items-center gap-2 transition-opacity duration-500">
                        <span class="material-icons-round">error</span>
                        <?php echo $error_msg; ?>
                    </div>
                <?php endif; ?>

                <div
                    class="bg-surface-light dark:bg-surface-dark rounded-xl border border-slate-200 dark:border-slate-800 shadow-sm p-6 md:p-8">
                    <h2 class="text-lg font-bold text-slate-900 dark:text-white mb-6">Profil Admin</h2>

                    <form action="" method="POST" class="space-y-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Nama
                                    Lengkap</label>
                                <input type="text" name="full_name"
                                    value="<?php echo htmlspecialchars($user['full_name'] ?? ''); ?>"
                                    class="w-full rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 focus:ring-primary focus:border-primary">
                            </div>
                            <div>
                                <label
                                    class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Username</label>
                                <input type="text" name="username"
                                    value="<?php echo htmlspecialchars($user['username']); ?>"
                                    class="w-full rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 focus:ring-primary focus:border-primary">
                            </div>
                        </div>

                        <div class="pt-6 border-t border-slate-200 dark:border-slate-800">
                            <h3 class="text-base font-bold text-slate-900 dark:text-white mb-4">Ganti Password</h3>
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <label
                                        class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Password
                                        Baru</label>
                                    <input type="password" name="new_password" placeholder="Kosongkan jika tidak diubah"
                                        class="w-full rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 focus:ring-primary focus:border-primary">
                                </div>
                                <div>
                                    <label
                                        class="block text-sm font-medium text-slate-700 dark:text-slate-300 mb-1">Konfirmasi
                                        Password</label>
                                    <input type="password" name="confirm_password" placeholder="Ulangi password baru"
                                        class="w-full rounded-lg border-slate-200 bg-slate-50 dark:bg-slate-800 dark:border-slate-700 focus:ring-primary focus:border-primary">
                                </div>
                            </div>
                        </div>

                        <div class="flex justify-end pt-4">
                            <button type="submit"
                                class="bg-primary hover:bg-blue-700 text-white font-bold py-2.5 px-6 rounded-lg transition-colors shadow-lg shadow-blue-500/20">
                                Simpan Perubahan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
            <?php include("footer.php"); ?>
        </div>
    </main>
</body>

</html>