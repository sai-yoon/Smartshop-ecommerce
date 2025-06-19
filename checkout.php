<?php
require 'auth.php';
require 'db.php';

// Redirect if not a customer
if ($_SESSION['user_type'] !== 'customer') {
    header("Location: index.php");
    exit;
}

// Alert if redirected after order placement
if (isset($_SESSION['order_placed'])) {
    unset($_SESSION['order_placed']);
    header("Location: customer_dashboard.php");
    exit;
}

// Get cart item count for navbar
$cartCountStmt = $pdo->prepare("SELECT COUNT(*) FROM cart WHERE user_id = ?");
$cartCountStmt->execute([$_SESSION['user_id']]);
$cartCount = $cartCountStmt->fetchColumn();

// Fetch cart items
$stmt = $pdo->prepare("
    SELECT c.*, p.name, p.price
    FROM cart c
    JOIN products p ON c.product_id = p.id
    WHERE c.user_id = ?
");
$stmt->execute([$_SESSION['user_id']]);
$items = $stmt->fetchAll();

if (!$items) {
    echo "<p class='text-center mt-10 text-lg'>Your cart is empty. <a class='text-blue-500 underline' href='products.php'>Go back to shopping</a>.</p>";
    exit;
}

// Fetch user details for form autofill
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Calculate total
$total = array_reduce($items, function($carry, $item) {
    return $carry + ($item['price'] * $item['quantity']);
}, 0);

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['place_order'])) {
    $email   = trim($_POST['email']);
    $fullname = trim($_POST['fullname']);
    $address = trim($_POST['address']);
    $contact = trim($_POST['contact']);
    $city    = trim($_POST['city']);
    $zipcode = trim($_POST['zipcode']);

    if ($email && $fullname && $address && $contact && $city && $zipcode) {
        $stmt = $pdo->prepare("INSERT INTO orders 
            (user_id, total, shipping_email, shipping_address, contact_no, city, zip_code)
            VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $_SESSION['user_id'],
            $total,
            $email,
            $address,
            $contact,
            $city,
            $zipcode
        ]);

        $order_id = $pdo->lastInsertId();

        $stmt_item = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price)
                                    VALUES (?, ?, ?, ?)");
        foreach ($items as $item) {
            $stmt_item->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
        }

        $pdo->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$_SESSION['user_id']]);

        $_SESSION['order_placed'] = true;
        header("Location: checkout.php");
        exit;
    } else {
        $error = "All fields are required.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Checkout - SmartShop</title>
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
<body class="bg-gray-100 font-sans">
    <!-- NAVBAR -->
    <header class="bg-white border-b border-gray-200 px-8 py-5 shadow-sm flex justify-between items-center fixed top-0 left-0 right-0 z-10">
        <h1 class="text-2xl font-semibold tracking-tight text-main-teal">SmartShop</h1>
        <nav class="flex space-x-8 text-base font-medium">
            <a href="customer_dashboard.php" class="relative group">
                <span class="transition-colors duration-300 group-hover:text-main-teal">Dashboard</span>
                <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-main-teal transition-all duration-300 group-hover:w-full"></span>
            </a>
            <a href="products.php" class="relative group">
                <span class="transition-colors duration-300 group-hover:text-main-teal">Browse</span>
                <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-main-teal transition-all duration-300 group-hover:w-full"></span>
            </a>
            <a href="cart.php" class="relative group">
                <span class="transition-colors duration-300 group-hover:text-main-teal">Cart (<?= $cartCount ?>)</span>
                <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-main-teal transition-all duration-300 group-hover:w-full"></span>
            </a>
            <a href="customer_orders.php" class="relative group text-main-teal font-semibold">
                <span class="border-b-2 border-main-teal pb-0.5">My Orders</span>
            </a>
            <a href="logout.php" class="relative group text-red-600 hover:text-red-700 transition">Logout</a>
        </nav>
    </header>

    <!-- MAIN CONTENT -->
    <main class="pt-32 pb-12 px-4 sm:px-6 lg:px-8 flex justify-center">
        <div class="max-w-4xl w-full bg-white p-8 rounded-2xl shadow-md">
            <h1 class="text-3xl font-bold text-center mb-8 text-gray-800">Checkout</h1>

            <?php if (!empty($error)): ?>
                <div class="mb-4 text-red-600 text-center font-semibold"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>

            <form method="POST" onsubmit="return validateForm();" class="space-y-6">
                <h2 class="text-xl font-semibold text-gray-700 border-b pb-2 mb-4">Shipping Information</h2>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block font-medium text-gray-700">Email</label>
                        <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required
                            class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 p-2">
                    </div>

                    <div>
                        <label class="block font-medium text-gray-700">Full Name</label>
                        <input type="text" id="fullname" name="fullname" value="<?= htmlspecialchars($user['full_name']) ?>" required
                            class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 p-2">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block font-medium text-gray-700">Address</label>
                        <textarea id="address" name="address" required
                            class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 p-2"><?= htmlspecialchars($user['address']) ?></textarea>
                    </div>

                    <div>
                        <label class="block font-medium text-gray-700">Contact Number</label>
                        <input type="text" id="contact" name="contact" value="<?= htmlspecialchars($user['contact_no']) ?>" required
                            class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 p-2">
                    </div>

                    <div>
                        <label class="block font-medium text-gray-700">City</label>
                        <input type="text" id="city" name="city" value="<?= htmlspecialchars($user['city']) ?>" required
                            class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 p-2">
                    </div>

                    <div>
                        <label class="block font-medium text-gray-700">Zip Code</label>
                        <input type="text" id="zipcode" name="zipcode" value="<?= htmlspecialchars($user['zip_code']) ?>" required
                            class="mt-1 block w-full border-gray-300 rounded-lg shadow-sm focus:ring-indigo-500 focus:border-indigo-500 p-2">
                    </div>
                </div>

                <div class="mt-8 border-t pt-6">
                    <h2 class="text-xl font-semibold text-gray-700 mb-4">Order Summary</h2>
                    <ul class="space-y-2 text-gray-600">
                        <?php foreach ($items as $item): ?>
                            <li class="flex justify-between border-b pb-1">
                                <span><?= htmlspecialchars($item['name']) ?> (x<?= $item['quantity'] ?>)</span>
                                <span>$<?= number_format($item['price'], 2) ?></span>
                            </li>
                        <?php endforeach; ?>
                    </ul>
                    <p class="text-right text-lg font-semibold mt-4 text-gray-800">Total: $<?= number_format($total, 2) ?></p>
                </div>

                <div class="mt-6">
                    <button type="submit" name="place_order"
                            class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-semibold py-3 px-4 rounded-lg shadow-md transition">
                        Confirm & Place Order
                    </button>
                </div>

                <div class="text-center mt-4">
                    <a href="cart.php" class="text-indigo-500 hover:underline">← Back to Cart</a>
                </div>
            </form>
        </div>
    </main>

    <script>
    function validateForm() {
        const fields = ['email', 'fullname', 'address', 'contact', 'city', 'zipcode'];
        for (let id of fields) {
            const el = document.getElementById(id);
            if (!el.value.trim()) {
                alert("Please complete all fields before placing the order.");
                el.focus();
                return false;
            }
        }
        return true;
    }
    </script>
</body>
</html>
