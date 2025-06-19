<?php
require 'auth.php';
require 'db.php';

if ($_SESSION['user_type'] !== 'seller') {
    header("Location: index.php");
    exit;
}

$id = $_GET['id'] ?? null;
if (!$id) {
    die("Product ID missing.");
}

// Fetch product
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ? AND seller_id = ?");
$stmt->execute([$id, $_SESSION['user_id']]);
$product = $stmt->fetch();

if (!$product) {
    die("Product not found or unauthorized.");
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $desc = $_POST['description'];
    $price = $_POST['price'];

    // Handle image upload if a new one is provided
    $imagePath = $product['image'];

    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $fileTmp = $_FILES['image']['tmp_name'];
        $fileName = basename($_FILES['image']['name']);
        $fileExt = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $allowedExts = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($fileExt, $allowedExts)) {
            $newName = uniqid("img_") . '.' . $fileExt;
            $uploadDir = 'uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            $uploadPath = $uploadDir . $newName;
            if (move_uploaded_file($fileTmp, $uploadPath)) {
                $imagePath = $uploadPath;
            } else {
                $message = "❌ Failed to upload image.";
            }
        } else {
            $message = "❌ Invalid image format. Only JPG, PNG, and GIF allowed.";
        }
    }

    if (!$message) {
        $stmt = $pdo->prepare("UPDATE products SET name=?, description=?, price=?, image=? WHERE id=? AND seller_id=?");
        $stmt->execute([$name, $desc, $price, $imagePath, $id, $_SESSION['user_id']]);

        header("Location: my_products.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Product - SmartShop</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
<body class="bg-gray-100 min-h-screen font-sans">
<!-- NAVBAR -->
<header class="bg-white border-b border-gray-200 px-8 py-5 shadow-sm flex justify-between items-center">
    <h1 class="text-2xl font-semibold tracking-tight">SmartShop</h1>
    <nav class="flex space-x-8 text-base font-medium">
        <a href="index.php" class="relative group">
            <span class="transition-colors duration-300 group-hover:text-main-teal">Home</span>
            <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-main-teal transition-all duration-300 group-hover:w-full"></span>
        </a>
        <a href="products.php" class="relative group">
            <span class="transition-colors duration-300 group-hover:text-main-teal">Browse</span>
            <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-main-teal transition-all duration-300 group-hover:w-full"></span>
        </a>
        <a href="my_products.php" class="relative group">
            <span class="transition-colors duration-300 group-hover:text-main-teal">My Products</span>
            <span class="absolute bottom-0 left-0 w-0 h-0.5 bg-main-teal transition-all duration-300 group-hover:w-full"></span>
        </a>
        <a href="logout.php" class="relative group text-red-600 hover:text-red-700 transition">
            Logout
        </a>
    </nav>
</header>
    <div class="max-w-xl mx-auto mt-12 p-8 bg-white shadow-lg rounded-xl border border-teal-200">
        <h1 class="text-3xl font-bold text-teal-700 mb-4">✏️ Edit Product</h1>

        <?php if ($message): ?>
            <div class="mb-4 p-3 rounded text-sm font-medium <?= str_starts_with($message, '✅') ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' ?>">
                <?= $message ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="space-y-4">
            <div>
                <label for="name" class="block text-teal-800 font-semibold mb-1">Product Name</label>
                <input type="text" name="name" id="name" required
                       value="<?= htmlspecialchars($product['name']) ?>"
                       class="w-full p-2 border border-teal-300 rounded focus:outline-none focus:ring-2 focus:ring-teal-500">
            </div>

            <div>
                <label for="description" class="block text-teal-800 font-semibold mb-1">Description</label>
                <textarea name="description" id="description" required rows="4"
                          class="w-full p-2 border border-teal-300 rounded focus:outline-none focus:ring-2 focus:ring-teal-500"><?= htmlspecialchars($product['description']) ?></textarea>
            </div>

            <div>
                <label for="price" class="block text-teal-800 font-semibold mb-1">Price ($)</label>
                <input type="number" step="0.01" name="price" id="price" required
                       value="<?= htmlspecialchars($product['price']) ?>"
                       class="w-full p-2 border border-teal-300 rounded focus:outline-none focus:ring-2 focus:ring-teal-500">
            </div>

            <div>
                <label for="image" class="block text-teal-800 font-semibold mb-1">Upload New Image</label>
                <input type="file" name="image" id="image"
                       class="w-full p-2 border border-teal-300 rounded bg-white focus:outline-none focus:ring-2 focus:ring-teal-500">
            </div>

            <?php if (!empty($product['image'])): ?>
                <div>
                    <label class="block text-sm text-teal-600 mb-1">Current Image</label>
                    <img src="<?= htmlspecialchars($product['image']) ?>" alt="Current Product Image"
                         class="rounded-lg w-full h-48 object-cover border border-gray-300">
                </div>
            <?php endif; ?>

            <div id="previewContainer" class="hidden">
                <label class="block text-sm text-teal-600 mt-3">New Image Preview</label>
                <img id="new-preview" class="rounded-lg w-full h-48 object-cover border border-teal-400">
            </div>

            <button type="submit"
                    class="w-full py-2 px-4 bg-teal-600 text-white font-semibold rounded hover:bg-teal-700 transition">
                💾 Save Changes
            </button>
        </form>

        <p class="mt-6 text-center">
            <a href="my_products.php" class="text-teal-700 hover:underline">← Back to My Products</a>
        </p>
    </div>

    <script>
        const fileInput = document.getElementById('image');
        const previewContainer = document.getElementById('previewContainer');
        const previewImage = document.getElementById('new-preview');

        fileInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function (e) {
                    previewImage.src = e.target.result;
                    previewContainer.classList.remove('hidden');
                };
                reader.readAsDataURL(file);
            } else {
                previewContainer.classList.add('hidden');
                previewImage.src = '';
            }
        });
    </script>
</body>
</html>


