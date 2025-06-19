<?php
session_start();
require 'db.php';

$error = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_type'] = $user['user_type'];
        $_SESSION['full_name'] = $user['full_name'];

        if ($user['user_type'] === 'customer') {
            header("Location: customer_dashboard.php");
        } else {
            header("Location: seller_dashboard.php");
        }
        exit;
    } else {
        $error = "Invalid credentials!";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SmartShop - Login</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs" defer></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'main-teal': '#008080',
                        'soft-teal': '#ccf2f2',
                        'muted-black': '#1a1a1a'
                    },
                    fontFamily: {
                        sans: ['Inter', 'sans-serif']
                    }
                }
            }
        }
    </script>
</head>
<body class="bg-gray-50 text-muted-black min-h-screen font-sans">

    <!-- NAVBAR -->
    <header class="border-b border-gray-200 px-8 py-5 flex justify-between items-center bg-white">
        <h1 class="text-2xl font-semibold tracking-tight">SmartShop</h1>
        <nav class="flex space-x-8 text-base font-medium">
            <a href="login.php" class="relative group">
                <span class="transition-colors duration-300 group-hover:text-main-teal">Login</span>
                <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-main-teal transition-all duration-300 group-hover:w-full"></span>
            </a>
            <a href="signup.php" class="relative group">
                <span class="transition-colors duration-300 group-hover:text-main-teal">Sign Up</span>
                <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-main-teal transition-all duration-300 group-hover:w-full"></span>
            </a>
            <a href="products.php" class="relative group">
                <span class="transition-colors duration-300 group-hover:text-main-teal">Browse</span>
                <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-main-teal transition-all duration-300 group-hover:w-full"></span>
            </a>
        </nav>
    </header>

    <!-- LOGIN FORM SECTION -->
    <main class="flex-grow flex items-center justify-center px-4 py-16">
        <div class="w-full max-w-md bg-white shadow-lg rounded-xl p-8 border border-teal-200">
            <h2 class="text-2xl font-bold text-main-teal text-center mb-6">🔐 Login to SmartShop</h2>

            <?php if ($error): ?>
                <p class="text-red-600 text-sm text-center mb-4 font-medium"><?= htmlspecialchars($error) ?></p>
            <?php endif; ?>

            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-muted-black text-sm font-medium mb-1">Email</label>
                    <input type="email" name="email" placeholder="you@example.com" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-main-teal">
                </div>

                <div>
                    <label class="block text-muted-black text-sm font-medium mb-1">Password</label>
                    <input type="password" name="password" placeholder="••••••••" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-main-teal">
                </div>

                <button type="submit"
                        class="w-full bg-main-teal hover:bg-opacity-90 text-white py-2 px-4 rounded-md font-semibold transition">
                    ➤ Login
                </button>
            </form>

            <p class="mt-4 text-sm text-center text-gray-600">
                Don’t have an account?
                <a href="signup.php" class="font-medium text-main-teal hover:underline">Sign up</a>
            </p>
        </div>
    </main>

    <!-- FOOTER -->
    <footer class="mt-12 text-sm text-gray-500 text-center pb-6">
        &copy; <?= date('Y') ?> SmartShop. All rights reserved.
    </footer>

</body>
</html>
