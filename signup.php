<?php
require 'db.php';

$message = '';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $full_name = htmlspecialchars($_POST['full_name']);
    $email = htmlspecialchars($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $user_type = $_POST['user_type'];

    // Check if email already exists
    $checkStmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $checkStmt->execute([$email]);

    if ($checkStmt->rowCount() > 0) {
        $message = "An account with this email already exists. Please <a href='login.php' class='underline text-teal-700'>login</a>.";
    } else {
        $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, user_type) VALUES (?, ?, ?, ?)");
        try {
            $stmt->execute([$full_name, $email, $password, $user_type]);
            $message = "Account created successfully! <a href='login.php' class='underline text-teal-700'>Login</a>";
        } catch (PDOException $e) {
            $message = "Error: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SmartShop - Sign Up</title>
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

    <!-- SIGNUP FORM -->
    <main class="flex-grow flex items-center justify-center px-4 py-16">
        <div class="w-full max-w-md bg-white shadow-lg rounded-xl p-8 border border-teal-200">
            <h2 class="text-2xl font-bold text-main-teal text-center mb-6">📝 Create an Account</h2>

            <?php if (!empty($message)): ?>
                <p class="text-center text-sm font-medium text-main-teal mb-4"><?= $message ?></p>
            <?php endif; ?>

            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Full Name</label>
                    <input type="text" name="full_name" placeholder="Jane Doe" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-main-teal">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Email</label>
                    <input type="email" name="email" placeholder="you@example.com" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-main-teal">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">Password</label>
                    <input type="password" name="password" placeholder="••••••••" required
                           class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-2 focus:ring-main-teal"
                           pattern=".{8,}" title="Password must be at least 8 characters">
                </div>

                <div>
                    <label class="block text-sm font-medium mb-1">User Type</label>
                    <select name="user_type" required
                            class="w-full px-3 py-2 border border-gray-300 rounded-md bg-white focus:outline-none focus:ring-2 focus:ring-main-teal">
                        <option value="customer">Customer</option>
                        <option value="seller">Seller</option>
                    </select>
                </div>

                <button type="submit"
                        class="w-full bg-main-teal hover:bg-opacity-90 text-white py-2 px-4 rounded-md font-semibold transition">
                    ➤ Sign Up
                </button>
            </form>

            <p class="mt-4 text-sm text-center text-gray-600">
                Already have an account?
                <a href="login.php" class="font-medium text-main-teal hover:underline">Login</a>
            </p>
        </div>
    </main>

    <!-- FOOTER -->
    <footer class="mt-12 text-sm text-gray-500 text-center pb-6">
        &copy; <?= date('Y') ?> SmartShop. All rights reserved.
    </footer>

</body>
</html>


