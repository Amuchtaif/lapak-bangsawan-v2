<?php
require_once dirname(__DIR__) . '/config/init.php';
// session_start() occurs in init.php
$error = '';
if (isset($_POST['username'])) {
    $username = stripslashes($_REQUEST['username']);
    $username = mysqli_real_escape_string($conn, $username);
    $password = stripslashes($_REQUEST['password']);
    $password = mysqli_real_escape_string($conn, $password);
    $query = "SELECT * FROM `users` WHERE username='$username'";
    $result = mysqli_query($conn, $query);
    if (mysqli_num_rows($result) == 1) {
        $user = mysqli_fetch_assoc($result);
        if (password_verify($password, $user['password'])) {
            $_SESSION['username'] = $username;
            $_SESSION['user_id'] = $user['id'];
            
            // Log the activity
            log_activity("LOGIN", "Admin berhasil masuk ke sistem.");
            
            header("Location: dashboard");
            exit();
        } else {
            $error = "Incorrect Password.";
            // Log the failed attempt if needed, but for now just success
        }
    } else {
        $error = "Username does not exist.";
    }
}
?>
<!DOCTYPE html>
<html class="light" lang="en">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Lapak Bangsawan - Login</title>
    <link rel="icon" href="<?= BASE_URL ?>assets/images/favicon-laba.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com" rel="preconnect" />
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#0d59f2",
                        "primary-dark": "#0a47c2",
                        "background-light": "#f1f3f7",
                        "background-dark": "#0f172a",
                    },
                    fontFamily: {
                        "display": ["Inter", "sans-serif"]
                    },
                    borderRadius: { "DEFAULT": "0.5rem", "lg": "0.75rem", "xl": "1rem", "2xl": "1.5rem", "3xl": "2.5rem", "full": "9999px" },
                },
            },
        }
    </script>
    <style>
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.8s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }

        .glass-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .dark .glass-card {
            background: rgba(15, 23, 42, 0.9);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.05);
        }

        .bg-pattern {
            background-color: #f1f3f7;
            background-image: radial-gradient(#0d59f2 0.5px, #f1f3f7 0.5px);
            background-size: 20px 20px;
        }

        .dark .bg-pattern {
            background-color: #0f172a;
            background-image: radial-gradient(#1e293b 0.5px, #0f172a 0.5px);
            background-size: 20px 20px;
        }
    </style>
</head>

<body class="bg-pattern dark:bg-background-dark font-display text-slate-900 dark:text-slate-100 antialiased selection:bg-primary/20 selection:text-primary">
    
    <!-- Outer Container -->
    <div class="min-h-screen w-full flex items-center justify-center p-4 md:p-8 lg:p-12 relative overflow-hidden">
        
        <!-- Desktop Layout: Contained Card -->
        <div class="relative z-10 w-full max-w-6xl bg-white dark:bg-slate-900 rounded-3xl shadow-[0_32px_96px_-12px_rgba(0,0,0,0.15)] dark:shadow-[0_32px_96px_-12px_rgba(0,0,0,0.4)] overflow-hidden flex flex-col lg:flex-row animate-fade-in-up">
            
            <!-- 1. Mobile Logo Content (Visible only on small screens) -->
            <div class="lg:hidden w-full p-8 flex flex-col items-center justify-center bg-slate-50 dark:bg-slate-800 border-b border-slate-200 dark:border-slate-700">
                <div class="flex h-20 w-20 items-center justify-center rounded-3xl bg-white dark:bg-slate-700 p-1 shadow-2xl ring-1 ring-slate-200 dark:ring-slate-600">
                    <img src="../assets/images/logo.jpeg" alt="Logo" class="w-full h-full object-cover rounded-2xl">
                </div>
                <h1 class="mt-4 text-xl font-black tracking-tight text-slate-900 dark:text-white uppercase">Lapak Bangsawan</h1>
            </div>

            <!-- 2. Form Side (Left on Desktop, Below Logo on Mobile) -->
            <div class="w-full lg:w-[55%] flex flex-col justify-center bg-white dark:bg-slate-900 p-8 md:p-12 lg:p-20 relative">
                <div class="w-full max-sm mx-auto">
                    <!-- Status Messages -->
                    <?php if ($error): ?>
                        <div class="bg-red-50 dark:bg-red-950/20 border border-red-100 dark:border-red-900/20 rounded-2xl p-4 mb-8 flex items-start gap-3">
                            <span class="material-symbols-outlined text-red-500 text-[20px]">error</span>
                            <div>
                                <h4 class="font-bold text-red-900 dark:text-red-400 text-sm">Gagal Login</h4>
                                <p class="text-xs text-red-600 dark:text-red-400/80 mt-1"><?php echo $error; ?></p>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($_GET['timeout'])): ?>
                        <div class="bg-amber-50 dark:bg-amber-950/20 border border-amber-100 dark:border-amber-900/20 rounded-2xl p-4 mb-8 flex items-start gap-3">
                            <span class="material-symbols-outlined text-amber-500 text-[20px]">warning</span>
                            <div>
                                <h4 class="font-bold text-amber-900 dark:text-amber-400 text-sm">Sesi Berakhir</h4>
                                <p class="text-xs text-amber-700 dark:text-amber-400/80 mt-1">Sesi anda sudah habis, silahkan login kembali.</p>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Form -->
                    <form action="" method="POST" class="flex flex-col gap-6">
                        <!-- username Field -->
                        <div class="flex flex-col gap-2">
                            <label class="text-[11px] font-black uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400 ml-1" for="username">Username</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 text-[20px]">person</span>
                                <input
                                    class="form-input flex w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 pl-11 pr-4 h-14 text-slate-900 dark:text-white placeholder:text-slate-400 focus:border-primary focus:ring-4 focus:ring-primary/10 focus:outline-none transition-all duration-300 font-medium"
                                    id="username" name="username" placeholder="Masukkan username" type="text" required />
                            </div>
                        </div>

                        <!-- Password Field -->
                        <div class="flex flex-col gap-2">
                            <div class="flex items-center justify-between ml-1">
                                <label class="text-[11px] font-black uppercase tracking-[0.2em] text-slate-500 dark:text-slate-400" for="password">Password</label>
                            </div>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 material-symbols-outlined text-slate-400 text-[20px]">lock</span>
                                <input
                                    class="form-input flex w-full rounded-2xl border border-slate-200 dark:border-slate-700 bg-slate-50/50 dark:bg-slate-800/50 pl-11 pr-12 h-14 text-slate-900 dark:text-white placeholder:text-slate-400 focus:border-primary focus:ring-4 focus:ring-primary/10 focus:outline-none transition-all duration-300 font-medium"
                                    id="password" name="password" placeholder="••••••••" type="password" required />
                                <button
                                    class="absolute right-4 top-1/2 -translate-y-1/2 flex items-center justify-center text-slate-400 hover:text-primary transition-colors"
                                    type="button"
                                    onclick="const p = document.getElementById('password'); p.type = p.type === 'password' ? 'text' : 'password'; this.children[0].textContent = p.type === 'password' ? 'visibility' : 'visibility_off';">
                                    <span class="material-symbols-outlined text-[20px]">visibility</span>
                                </button>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button
                            class="mt-4 flex w-full items-center justify-center rounded-2xl bg-primary hover:bg-primary-dark h-15 py-4 text-base font-black text-white shadow-[0_20px_40px_-10px_rgba(13,89,242,0.3)] hover:shadow-[0_25px_50px_-10px_rgba(13,89,242,0.5)] hover:scale-[1.01] active:scale-[0.99] focus:outline-none focus:ring-4 focus:ring-primary/20 transition-all duration-300"
                            type="submit">
                            <span class="material-symbols-outlined mr-2 text-[20px]">login</span>
                            Login Ke Sistem
                        </button>
                    </form>
                </div>
            </div>

            <!-- 3. Visual Panel (Right Side on Desktop, Hidden on Mobile) -->
            <div class="hidden lg:flex w-full lg:w-[45%] relative bg-slate-950 text-white flex-col justify-between p-12 overflow-hidden">
                <!-- Background Image -->
                <div class="absolute inset-0 z-0 h-full w-full">
                    <div class="h-full w-full bg-cover bg-center opacity-80"
                        style='background-image: url("../assets/images/login-bg.jpg");'>
                    </div>
                    <!-- Gradient Overlay to make text and branding readable -->
                    <div class="absolute inset-0 bg-gradient-to-l from-slate-950/20 via-slate-950/40 to-slate-950/80"></div>
                </div>

                <!-- Branding -->
                <div class="relative z-10 flex items-center gap-4">
                    <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 p-1 shadow-xl">
                        <img src="../assets/images/logo.jpeg" alt="Logo" class="w-full h-full object-cover rounded-xl">
                    </div>
                    <div>
                        <h1 class="text-xl font-black tracking-tight text-white leading-tight uppercase">Lapak</h1>
                        <p class="text-[10px] font-bold tracking-[0.3em] text-white uppercase">Bangsawan</p>
                    </div>
                </div>

                <!-- Footer info -->
                <div class="relative z-10 text-slate-400 text-[10px] font-bold tracking-[0.1em] uppercase">
                    © 2026 Lapak Bangsawan — Sistem Manajemen
                </div>
            </div>
        </div>
    </div>
</body>

</html>