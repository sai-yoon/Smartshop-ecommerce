<?php
require 'auth.php';
require 'db.php';

$user_id = $_SESSION['user_id'];
$user_type = $_SESSION['user_type'];

// Fetch current profile data
$stmt = $pdo->prepare("SELECT full_name, email, contact_no, city, zip_code FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    echo "User not found.";
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = $_POST['full_name'];
    $email = $_POST['email'];
    $contact = $_POST['contact_no'];
    $city = $_POST['city'];
    $zip = $_POST['zip_code'];

    if (empty($full_name) || empty($email)) {
        echo "<p>Please fill all required fields.</p>";
    } else {
        $stmt = $pdo->prepare("
            UPDATE users 
            SET full_name = ?, email = ?, contact_no = ?, city = ?, zip_code = ? 
            WHERE id = ?
        ");
        $stmt->execute([$full_name, $email, $contact, $city, $zip, $user_id]);

        // Update session full_name as well
        $_SESSION['full_name'] = $full_name;

        echo "<script>alert('Profile updated successfully.'); window.location.href='" . $user_type . "_dashboard.php';</script>";
        exit;
    }
}
?>

<!DOCTYPE html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Profile - SmartShop</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>
<body class="bg-teal-50 min-h-screen">
    <!-- Navigation Bar -->
    <header class="bg-white shadow-md px-6 py-4 flex justify-between items-center">
        <h1 class="text-2xl font-bold text-main-teal text-teal-700">SmartShop</h1>
        <nav class="flex space-x-6 text-base font-medium">
            <a href="index.php" class="hover:text-teal-600 transition">Home</a>
            <a href="products.php" class="hover:text-teal-600 transition">Browse</a>
            <?php if ($user_type === 'seller'): ?>
                <a href="seller_dashboard.php" class="hover:text-teal-600 transition">My Products</a>
            <?php else: ?>
                <a href="customer_orders.php" class="hover:text-teal-600 transition">My Orders</a>
            <?php endif; ?>
            <a href="logout.php" class="text-red-600 hover:text-red-700 font-semibold transition">Logout</a>
        </nav>
    </header>

    <!-- Main Content -->
    <main class="flex items-center justify-center py-12 px-4">
        <div class="bg-white w-full max-w-md shadow-lg rounded-xl p-8 border border-teal-200">
            <h1 class="text-2xl font-bold text-teal-700 text-center mb-6">✏️ Edit Profile</h1>

            <form method="POST" class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-teal-800">Full Name:</label>
                    <input type="text" name="full_name" value="<?= htmlspecialchars($user['full_name']) ?>" required
                           class="mt-1 w-full border border-teal-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-teal-800">Email:</label>
                    <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required
                           class="mt-1 w-full border border-teal-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-teal-800">Contact Number:</label>
                    <input type="text" name="contact_no" value="<?= htmlspecialchars($user['contact_no']) ?>"
                           class="mt-1 w-full border border-teal-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-teal-800">City:</label>
                    <input type="text" name="city" value="<?= htmlspecialchars($user['city']) ?>"
                           class="mt-1 w-full border border-teal-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                </div>

                <div>
                    <label class="block text-sm font-medium text-teal-800">Zip Code:</label>
                    <input type="text" name="zip_code" value="<?= htmlspecialchars($user['zip_code']) ?>"
                           class="mt-1 w-full border border-teal-300 rounded-md px-3 py-2 focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-teal-500">
                </div>

                <button type="submit" class="w-full bg-teal-600 hover:bg-teal-700 text-white py-2 px-4 rounded-md font-semibold transition">
                    💾 Save Changes
                </button>
            </form>

            <p class="mt-6 text-center text-sm text-teal-600">
                <a href="<?= $user_type ?>_dashboard.php" class="hover:underline">← Back to Dashboard</a>
            </p>
        </div>
    </main>
</body>

</html>
