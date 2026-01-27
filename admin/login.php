<?php
session_start();
require_once dirname(__DIR__) . '/config/init.php';
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
            header("Location: dashboard");
            exit();
        } else {
            $error = "Incorrect Password.";
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;900&amp;display=swap"
        rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
        rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
        rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#0d59f2",
                        "background-light": "#f5f6f8",
                        "background-dark": "#101622",
                    },
                    fontFamily: {
                        "display": ["Inter", "sans-serif"]
                    },
                    borderRadius: { "DEFAULT": "0.25rem", "lg": "0.5rem", "xl": "0.75rem", "full": "9999px" },
                },
            },
        }
    </script>
    </script>
    <style>
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out forwards;
        }
    </style>
</head>

<body class="bg-background-light dark:bg-background-dark font-display text-slate-900 dark:text-white antialiased">
    <div class="flex min-h-screen w-full flex-row overflow-hidden">
        <!-- Left Side: Visual Anchor (Desktop Only) -->
        <div
            class="hidden lg:flex w-1/2 relative bg-slate-900 text-white flex-col justify-between p-12 overflow-hidden">
            <!-- Background Image -->
            <div class="absolute inset-0 z-0 h-full w-full">
                <div class="h-full w-full bg-cover bg-center opacity-60 mix-blend-overlay"
                    data-alt="Premium raw beef steak on a dark stone background with herbs"
                    style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuApiN778Hkr54yuB7RBFiA9NIZK_TDAzRmi-fq9gHZCuljj2zGgRFYMYMCTF2w45sGwYZzx5fNa7uQnE1okSIZwe30Q6z1nHvAZqJD8s9o-V6Nv-ndp04M_BHlw04D8bEwMR8EY39YKPWkSl3tUtcVGx54CDBotzL3P2qhMqyiwiPu25apr6rB33c4DD6-rJkSWoV4z02x5z_wk2eDDxujR-zmnhwPcdlBRr2CdZ_8bA-IMSAZ2Zj6p1IThXbwCJw33qv_T-aRq7Vc");'>
                </div>
                <div class="absolute inset-0 bg-gradient-to-t from-slate-900/90 via-slate-900/40 to-slate-900/30"></div>
            </div>
            <!-- Logo on Image Side -->
            <div class="relative z-10 flex items-center gap-3">
                <div
                    class="flex h-12 w-12 items-center justify-center rounded-lg bg-primary/20 backdrop-blur-sm border border-white/10 text-white overflow-hidden">
                    <img src="../assets/images/logo.jpeg" alt="Logo" class="w-full h-full object-cover">
                </div>
                <h2 class="text-xl font-bold tracking-tight text-white">Lapak Bangsawan</h2>
            </div>
            <!-- Testimonial/Quote -->
            <div class="relative z-10 max-w-lg">
                <blockquote class="text-2xl font-medium leading-snug text-white">
                    "The quality of meat from Lapak Bangsawan has completely transformed our restaurant's menu. Simply
                    the best protein sourcing available."
                </blockquote>
                <div class="mt-6 flex items-center gap-4">
                    <div class="h-10 w-10 overflow-hidden rounded-full border border-white/20">
                        <div class="h-full w-full bg-cover bg-center"
                            data-alt="Portrait of a smiling chef in a white uniform"
                            style='background-image: url("https://lh3.googleusercontent.com/aida-public/AB6AXuC46hBd3Py3bnIo3KFmy7Vi1-Ezx485_E3pod0EvZY2twRi1iIxEbdbD_XCeJ6LJ2910nJKBSJlVa3yQ18vDdvgIli3nkTJI0kjbJ7V9EqZd4CQcpHLfDTFkfYNGb1C6-bpF_VC3nibQfNR7lmCuzccFx6lBnsJWdnjmDKiVl97Kp88CL-En69x3QxhK7CXIFYwlMe3g8RY55XTs6L-0CS-Gmb73p13Na4NhxbxyGFw47NUtqQVCqAHoJeNoCUeBsAlvj4reNcR2wA");'>
                        </div>
                    </div>
                    <div>
                        <p class="text-sm font-semibold text-white">Chef Hendra Gunawan</p>
                        <p class="text-xs text-slate-300">Executive Chef, The Royal Grill</p>
                    </div>
                </div>
            </div>
        </div>
        <!-- Right Side: Login Form -->
        <div
            class="flex w-full lg:w-1/2 flex-col justify-center bg-slate-50 dark:bg-background-dark px-4 py-8 relative lg:static">

            <!-- Mobile Background Elements -->
            <div class="lg:hidden absolute inset-0 overflow-hidden pointer-events-none z-0">
                <div
                    class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-blue-100 via-white to-white dark:from-slate-800 dark:via-slate-900 dark:to-slate-900">
                </div>
                <div
                    class="absolute -top-[100px] -left-[100px] w-[500px] h-[500px] bg-primary/20 rounded-full blur-3xl opacity-50 mix-blend-multiply dark:mix-blend-normal dark:bg-primary/10">
                </div>
                <div
                    class="absolute top-[20%] -right-[100px] w-[400px] h-[400px] bg-cyan-400/20 rounded-full blur-3xl opacity-50 mix-blend-multiply dark:mix-blend-normal dark:bg-cyan-500/10">
                </div>
                <div class="absolute bottom-0 left-[10%] w-[600px] h-[400px] bg-indigo-500/10 rounded-full blur-3xl">
                </div>
            </div>

            <!-- Mobile Logo (Visible only on small screens) -->
            <div
                class="mb-10 flex flex-col items-center justify-center gap-4 lg:hidden relative z-10 animate-fade-in-up">
                <div class="flex items-center gap-3 px-6 py-3">
                    <div
                        class="flex h-20 w-20 items-center justify-center rounded-full bg-white p-1 shadow-lg shadow-primary/20 ring-4 ring-white/20">
                        <img src="../assets/images/logo.jpeg" alt="Logo"
                            class="w-full h-full object-cover rounded-full">
                    </div>
                </div>
            </div>

            <div class="w-full max-w-[420px] mx-auto relative z-10">
                <!-- Login Card (Mobile) / Container (Desktop) -->
                <div
                    class="bg-white/80 dark:bg-slate-800/80 backdrop-blur-xl lg:backdrop-blur-none lg:bg-transparent lg:dark:bg-transparent p-8 lg:p-0 rounded-3xl lg:rounded-none shadow-2xl shadow-slate-200/50 dark:shadow-black/20 lg:shadow-none border border-white/50 dark:border-slate-700/50 lg:border-none transition-all duration-500 animate-fade-in-up">
                    <!-- Page Heading -->
                    <div class="mb-8">
                        <h3 class="text-3xl font-black tracking-tight text-slate-900 dark:text-white mb-2">
                            Login</h3>
                        <p class="text-slate-500 dark:text-slate-400 text-sm lg:hidden italic">Silahkan masuk untuk
                            mengakses dashboard.</p>
                        <p class="hidden lg:block text-slate-500 dark:text-slate-400 text-base">Silahkan masuk untuk
                            mengakses dashboard.</p>
                    </div>

                    <!-- Form -->
                    <?php if ($error): ?>
                        <div class="mb-4 p-3 bg-red-50 text-red-600 border border-red-100 rounded-xl text-sm font-medium">
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    <?php if (isset($_GET['timeout'])): ?>
                        <div
                            class="mb-4 p-3 bg-amber-50 text-amber-700 border border-amber-100 rounded-xl text-sm flex items-center gap-2 font-medium">
                            <span class="material-symbols-outlined text-lg">warning</span>
                            Sesi anda sudah habis, login kembali.
                        </div>
                    <?php endif; ?>

                    <form action="" class="flex flex-col gap-6" method="POST">
                        <!-- username Field -->
                        <div class="flex flex-col gap-2">
                            <label class="text-sm font-medium text-slate-900 dark:text-white"
                                for="username">Username</label>
                            <div class="relative">
                                <input
                                    class="form-input flex w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white/50 dark:bg-slate-900/50 px-4 h-14 text-slate-900 dark:text-white placeholder:text-slate-400 focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none transition-all duration-200 hover:bg-white dark:hover:bg-slate-800 focus:bg-white dark:focus:bg-slate-800"
                                    id="username" name="username" placeholder="Enter your username" type="text" />
                            </div>
                        </div>

                        <!-- Password Field -->
                        <div class="flex flex-col gap-2">
                            <div class="flex items-center justify-between">
                                <label class="text-sm font-medium text-slate-900 dark:text-white"
                                    for="password">Password</label>
                            </div>
                            <div class="relative flex items-center">
                                <input
                                    class="form-input flex w-full rounded-xl border border-slate-200 dark:border-slate-700 bg-white/50 dark:bg-slate-900/50 px-4 h-14 text-slate-900 dark:text-white placeholder:text-slate-400 focus:border-primary focus:ring-2 focus:ring-primary/20 focus:outline-none transition-all duration-200 hover:bg-white dark:hover:bg-slate-800 focus:bg-white dark:focus:bg-slate-800 pr-12"
                                    id="password" name="password" placeholder="••••••••" type="password" />
                                <button
                                    class="absolute right-4 flex items-center justify-center text-slate-400 hover:text-slate-600 dark:hover:text-slate-300"
                                    type="button"
                                    onclick="const p = document.getElementById('password'); p.type = p.type === 'password' ? 'text' : 'password';">
                                    <span class="material-symbols-outlined text-[20px]">visibility</span>
                                </button>
                            </div>
                        </div>

                        <!-- Submit Button -->
                        <button
                            class="mt-4 flex w-full items-center justify-center rounded-xl bg-gradient-to-r from-primary to-blue-600 h-14 text-base font-bold text-white shadow-lg shadow-primary/30 hover:shadow-primary/50 hover:scale-[1.02] active:scale-[0.98] focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition-all duration-200"
                            type="submit">
                            Log In
                        </button>

                    </form>
                </div>
            </div>
        </div>
    </div>
</body>

</html>