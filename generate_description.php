<?php
require 'auth.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$finalDesc = "";
$tags = "";
$displayProductName = "";
$displayImageUrl = "";
$errors = [];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $productName = filter_input(INPUT_POST, 'product_name', FILTER_UNSAFE_RAW);
    $shortDesc = filter_input(INPUT_POST, 'short_desc', FILTER_UNSAFE_RAW);
    $productName = $productName ? strip_tags(trim($productName)) : '';
    $shortDesc = $shortDesc ? strip_tags(trim($shortDesc)) : '';

    if (empty($productName) || empty($shortDesc) || empty($_FILES['image']['name'])) {
        $errors[] = "All fields are required.";
    } else {
        $uploadDir = "Uploads/";
        if (!file_exists($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        $imageName = basename($_FILES["image"]["name"]);
        $imageName = preg_replace("/[^A-Za-z0-9._-]/", "", $imageName);
        $targetPath = $uploadDir . time() . "_" . $imageName;

        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
        $maxSize = 5 * 1024 * 1024;
        if (!in_array($_FILES['image']['type'], $allowedTypes) || $_FILES['image']['size'] > $maxSize) {
            $errors[] = "Invalid image type or size. Use JPEG/PNG/GIF under 5MB.";
        } else {
            if (move_uploaded_file($_FILES["image"]["tmp_name"], $targetPath)) {
                $imageUrl = "http://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/$targetPath";

                // Imagga
                $imaggaCurl = curl_init();
                curl_setopt_array($imaggaCurl, [
                    CURLOPT_URL => "https://api.imagga.com/v2/tags?image_url=" . urlencode($imageUrl),
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HTTPHEADER => [
                        "Authorization: Basic " . base64_encode("acc_54c84bf26eb4b73:ed77c49a46b5b55a1ac79441d7dd2223")
                    ],
                ]);
                $imaggaResponse = curl_exec($imaggaCurl);
                $imaggaError = curl_error($imaggaCurl);
                curl_close($imaggaCurl);

                if ($imaggaError) {
                    $errors[] = "Imagga API Error: $imaggaError";
                } else {
                    $imaggaData = json_decode($imaggaResponse, true);

                    if (!empty($imaggaData['result']['tags'])) {
                        $tagNames = array_map(function ($tag) {
                            return htmlspecialchars($tag['tag']['en']);
                        }, array_slice($imaggaData['result']['tags'], 0, 10));
                        $tags = implode(", ", $tagNames);
                    } else {
                        $errors[] = "No tags found.";
                    }
                }

                // Product Description API
                $descCurl = curl_init();
                curl_setopt_array($descCurl, [
                    CURLOPT_URL => "https://ai-ecommerce-product-description-generator.p.rapidapi.com/generate_product_description?" . http_build_query([
                        "language" => "English",
                        "name" => $productName,
                        "description" => $shortDesc . " " . $tags
                    ]),
                    CURLOPT_RETURNTRANSFER => true,
                    CURLOPT_HTTPHEADER => [
                        "x-rapidapi-host: ai-ecommerce-product-description-generator.p.rapidapi.com",
                        "x-rapidapi-key: 23965a5f25msh686f92187ab8e2ap11d5fajsna38f937e4793"
                    ],
                ]);

                $descResponse = curl_exec($descCurl);
                $descError = curl_error($descCurl);
                curl_close($descCurl);

                if ($descError) {
                    $errors[] = "Description API Error: $descError";
                } else {
                    $descData = json_decode($descResponse, true);

                    if (isset($descData['product_description'])) {
                        $sentences = preg_split('/(?<=[.!?])\s+/', $descData['product_description'], -1, PREG_SPLIT_NO_EMPTY);
                        $paragraphs = [];
                        $currentParagraph = '';
                        foreach ($sentences as $index => $sentence) {
                            $currentParagraph .= $sentence . ' ';
                            if (($index + 1) % 3 == 0 || $index == count($sentences) - 1) {
                                $paragraphs[] = trim($currentParagraph);
                                $currentParagraph = '';
                            }
                        }
                        $finalDesc = implode("\n\n", array_map('htmlspecialchars', $paragraphs));
                    } elseif (isset($descData['descriptions']) && is_array($descData['descriptions'])) {
                        $finalDesc = htmlspecialchars(implode("\n\n", $descData['descriptions']));
                    } else {
                        $errors[] = "No description returned.";
                    }
                }

                $displayProductName = htmlspecialchars($productName);
                $displayImageUrl = htmlspecialchars($imageUrl);
            } else {
                $errors[] = "Image upload failed.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Generate Description - SmartShop</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            'main-teal': '#008080',
            'soft-teal': '#ccf2f2',
            'muted-black': '#1a1a1a',
            'dark-teal': '#005a5a'
          },
          fontFamily: {
            sans: ['Inter', 'sans-serif']
          }
        }
      }
    };

    function previewImage(event) {
      const preview = document.getElementById("preview");
      const image = document.getElementById("imagePreview");
      preview.classList.remove("hidden");
      image.src = URL.createObjectURL(event.target.files[0]);
    }

    function copyToClipboard() {
      const desc = document.getElementById("descBox").innerText;
      navigator.clipboard.writeText(desc).then(() => alert("Copied to clipboard!"));
    }
  </script>
  <style>
    /* Navbar underline animation */
    nav a.group {
      position: relative;
      transition: color 0.3s ease;
    }

    nav a.group::after {
      content: '';
      position: absolute;
      left: 0;
      bottom: -4px;
      width: 0%;
      height: 2px;
      background-color: #008080;
      transition: width 0.3s ease;
    }

    nav a.group:hover::after {
      width: 100%;
    }
  </style>
</head>
<body class="bg-gray-100 font-sans text-muted-black">

<!-- NAVBAR -->
<header class="bg-white border-b border-gray-200 px-8 py-5 shadow-sm flex justify-between items-center">
  <h1 class="text-2xl font-semibold tracking-tight">SmartShop</h1>
  <nav class="flex space-x-8 text-base font-medium">
    <a href="index.php" class="group relative hover:text-main-teal">Home</a>
    <a href="products.php" class="group relative hover:text-main-teal">Browse</a>
    <a href="my_products.php" class="group relative hover:text-main-teal">My Products</a>
    <a href="logout.php" class="group relative text-red-600 hover:text-red-700 transition">Logout</a>
  </nav>
</header>

<!-- MAIN CONTENT -->
<main class="max-w-xl mx-auto mt-10 bg-white p-8 rounded-xl shadow space-y-8">

  <!-- Title -->
  <h2 class="text-3xl font-bold text-main-teal text-center">AI Product Description Generator</h2>

  <!-- Input Form Card -->
  <div class="bg-soft-teal border border-main-teal rounded-lg p-6 space-y-4 shadow">

    <form action="" method="post" enctype="multipart/form-data" class="space-y-6">

      <div>
        <label class="block font-medium mb-1">Product Name</label>
        <input type="text" name="product_name" class="w-full border p-2 rounded" required>
      </div>

      <div>
        <label class="block font-medium mb-1">Short Description</label>
        <textarea name="short_desc" class="w-full border p-2 rounded" required></textarea>
      </div>

      <div>
        <label class="block font-medium mb-1">Upload Image</label>
        <label for="imageUpload" class="flex items-center justify-center border-2 border-dashed border-main-teal bg-white text-center p-6 rounded cursor-pointer hover:bg-gray-100 transition">
          <span class="text-gray-700">Drag & drop an image here or click to upload</span>
          <input id="imageUpload" type="file" name="image" accept="image/*" class="hidden" required onchange="previewImage(event)">
        </label>
        <div id="preview" class="mt-3 hidden">
          <p class="text-sm text-gray-600 mb-1">Image Preview:</p>
          <img id="imagePreview" src="#" alt="Preview" class="max-w-full rounded shadow border border-gray-300">
        </div>
      </div>

      <button type="submit" class="bg-main-teal text-white px-6 py-2 rounded hover:bg-teal-700 transition w-full">Generate</button>
    </form>
  </div>

  <!-- Error Messages -->
  <?php if (!empty($errors)): ?>
    <div class="p-4 bg-red-100 text-red-700 rounded">
      <?php foreach ($errors as $error): ?>
        <p><?= $error ?></p>
      <?php endforeach; ?>
    </div>

  <!-- Results Section -->
  <?php elseif ($finalDesc): ?>
    <div class="bg-soft-teal border border-main-teal rounded-xl shadow space-y-6 p-6">
      
      <!-- Product Name -->
      <h3 class="text-3xl font-bold text-main-teal text-center"><?= $displayProductName ?></h3>

      <!-- Uploaded Image -->
      <div class="flex justify-center">
        <img src="<?= $displayImageUrl ?>" alt="Uploaded Product" class="max-w-xs rounded shadow border border-gray-300">
      </div>

      <!-- Image Tags -->
      <div>
        <h4 class="font-semibold text-muted-black mb-1">Image Tags:</h4>
        <div class="flex flex-wrap gap-2">
          <?php foreach (explode(",", $tags) as $tag): ?>
            <span class="bg-main-teal text-white px-3 py-1 rounded-full text-sm"><?= trim($tag) ?></span>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Generated Descriptions -->
      <div>
        <h4 class="font-semibold text-muted-black mb-2">Generated Descriptions:</h4>
        <div id="descBox" class="space-y-3">
          <?php foreach (explode("\n\n", $finalDesc) as $paragraph): ?>
            <div class="bg-white border border-teal-200 rounded-lg p-4 text-sm text-gray-800 whitespace-pre-wrap shadow-sm">
              <?= $paragraph ?>
            </div>
          <?php endforeach; ?>
        </div>
      </div>

      <!-- Copy Button -->
      <button onclick="copyToClipboard()" class="w-full px-4 py-2 bg-dark-teal text-white rounded hover:bg-main-teal transition">
        Copy Description
      </button>
    </div>
  <?php endif; ?>

</main>

<footer class="text-center text-sm text-gray-500 py-6 mt-10">
  &copy; <?= date('Y') ?> SmartShop. All rights reserved.
</footer>
</body>
</html>
