<?php 
session_start();

if (isset($_SESSION['user_type'])) {
    if ($_SESSION['user_type'] === 'customer') {
        header("Location: customer_dashboard.php");
        exit;
    } elseif ($_SESSION['user_type'] === 'seller') {
        header("Location: seller_dashboard.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>SmartShop - Welcome</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://unpkg.com/alpinejs" defer></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
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
<body class="bg-white text-muted-black min-h-screen flex flex-col font-sans">

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

    <!-- HERO SECTION -->
    <main class="flex-grow flex items-center justify-center px-4 py-20 bg-gray-50">
        <div class="w-full max-w-5xl flex flex-col md:flex-row items-center justify-between gap-12">
            <img src="hero.png" alt="Welcome Illustration" class="w-full md:w-1/2 max-w-md rounded-2xl shadow-md" data-aos="fade-right" data-aos-duration="1000">
            <div class="w-full md:w-1/2 text-center md:text-left" data-aos="fade-up" data-aos-delay="200">
                <h2 class="text-4xl font-semibold text-main-teal mb-4">Welcome to SmartShop</h2>
                <p class="text-lg text-gray-600 font-normal mb-8 max-w-md">A streamlined e-commerce platform crafted for seamless experiences — whether you’re selling or shopping.</p>
                <div class="flex flex-col sm:flex-row justify-center md:justify-start items-center gap-4">
                    <a href="login.php" class="w-full sm:w-auto bg-main-teal text-white py-3 px-7 rounded-md text-sm font-semibold hover:bg-opacity-90 transition-all duration-300 shadow-sm">Log In</a>
                    <a href="signup.php" class="w-full sm:w-auto bg-white text-main-teal py-3 px-7 border border-main-teal rounded-md text-sm font-semibold hover:bg-main-teal hover:text-white transition-all duration-300 shadow-sm">Create Account</a>
                    <a href="products.php" class="w-full sm:w-auto bg-white text-muted-black py-3 px-7 border border-gray-300 rounded-md text-sm font-medium hover:bg-gray-100 transition duration-300 shadow-sm">Browse Products</a>
                </div>
            </div>
        </div>
    </main>

    <section class="bg-white text-center px-4 pt-16" data-aos="fade-down" data-aos-delay="100">
  <h2 class="text-3xl md:text-4xl font-semibold text-main-teal mb-6">Why Choose SmartShop?</h2>
  <p class="text-gray-600 text-base md:text-lg max-w-2xl mx-auto mb-4">
    Built to empower both buyers and sellers with intelligent features, robust security, and a seamless experience from discovery to checkout.
  </p>
</section>

    <!-- IMAGE CAROUSEL SECTION -->
    <section class="bg-white px-4 py-20" data-aos="fade-up" data-aos-delay="300">
        <div class="max-w-6xl mx-auto relative"
             x-data="{slide: 0,slides: [
                { image: 'feature1.jpg', title: 'Fast & Secure Checkout' },
                { image: 'feature2.jpg', title: 'Powerful Seller Dashboard' },
                { image: 'feature3.jpg', title: 'Explore Quality Products' }
             ],touchStartX: null,startAutoSlide() {
                setInterval(() => this.next(), 6000);
             },next() { this.slide = (this.slide + 1) % this.slides.length; },prev() {
                this.slide = (this.slide - 1 + this.slides.length) % this.slides.length; },goTo(index) { this.slide = index; },handleTouchStart(e) { this.touchStartX = e.touches[0].clientX; },handleTouchEnd(e) { const endX = e.changedTouches[0].clientX; if (this.touchStartX - endX > 50) { this.next(); } else if (endX - this.touchStartX > 50) { this.prev(); } }}"
             x-init="startAutoSlide()" @touchstart="handleTouchStart($event)" @touchend="handleTouchEnd($event)" class="select-none">
            <div class="relative w-full h-80 md:h-[28rem] lg:h-[32rem] rounded-2xl overflow-hidden shadow-md">
                <template x-for="(slideItem, index) in slides" :key="index">
                    <div x-show="slide === index" x-transition class="absolute inset-0 w-full h-full">
                        <img :src="slideItem.image" :alt="slideItem.title" class="w-full h-full object-cover object-center rounded-2xl transition-opacity duration-500">
                        <div class="absolute inset-0 bg-black/30 rounded-2xl flex items-center justify-center">
                            <h2 class="text-white text-2xl md:text-4xl font-semibold tracking-tight text-center px-4">
                                <span x-text="slideItem.title"></span>
                            </h2>
                        </div>
                    </div>
                </template>
            </div>
            <div class="flex justify-center mt-6 space-x-2">
                <template x-for="(item, index) in slides" :key="'dot-' + index">
                    <button @click="goTo(index)" class="w-3 h-3 rounded-full" :class="{'bg-main-teal': slide === index,'bg-gray-300': slide !== index}"></button>
                </template>
            </div>
        </div>
    </section>

    <!-- STATS SECTION -->
    <section class="bg-white py-20 px-6" data-aos="fade-up">
      <div class="max-w-6xl mx-auto grid grid-cols-1 sm:grid-cols-3 gap-10 text-center">
        <div>
          <h3 class="text-5xl font-bold text-main-teal" x-data="{ count: 0 }" x-init="let i = setInterval(() => { if(count < 1500){ count += 25 } else { clearInterval(i) } }, 20)">
            <span x-text="count + '+'"></span>
          </h3>
          <p class="mt-2 text-gray-600 text-lg">Active Customers</p>
        </div>
        <div>
          <h3 class="text-5xl font-bold text-main-teal" x-data="{ count: 0 }" x-init="let i = setInterval(() => { if(count < 320){ count += 4 } else { clearInterval(i) } }, 30)">
            <span x-text="count + '+'"></span>
          </h3>
          <p class="mt-2 text-gray-600 text-lg">Verified Sellers</p>
        </div>
        <div>
          <h3 class="text-5xl font-bold text-main-teal" x-data="{ count: 0 }" x-init="let i = setInterval(() => { if(count < 10000){ count += 100 } else { clearInterval(i) } }, 10)">
            <span x-text="count + '+'"></span>
          </h3>
          <p class="mt-2 text-gray-600 text-lg">Products Sold</p>
        </div>
      </div>
    </section>

 <!-- USER TESTIMONIALS -->
<section class="bg-white py-16 px-4" data-aos="fade-up" data-aos-delay="400">
  <div class="max-w-6xl mx-auto text-center">
    <h2 class="text-3xl font-semibold text-main-teal mb-12">What Our Users Say</h2>
    <div class="grid gap-10 md:grid-cols-3">
      <div class="bg-soft-teal p-6 rounded-xl shadow-sm hover:shadow-md transition duration-300">
        <img src="avatar1.jpg" alt="Jane Doe" class="w-16 h-16 rounded-full mx-auto mb-4">
        <p class="text-muted-black text-base italic mb-2">“SmartShop made managing my small store feel like a breeze!”</p>
        <p class="text-sm font-medium text-gray-700">Jane D. – Boutique Owner</p>
      </div>
      <div class="bg-soft-teal p-6 rounded-xl shadow-sm hover:shadow-md transition duration-300">
        <img src="avatar2.jpg" alt="Mike R." class="w-16 h-16 rounded-full mx-auto mb-4">
        <p class="text-muted-black text-base italic mb-2">“The checkout was lightning fast, and I got tracking updates instantly.”</p>
        <p class="text-sm font-medium text-gray-700">Mike R. – Customer</p>
      </div>
      <div class="bg-soft-teal p-6 rounded-xl shadow-sm hover:shadow-md transition duration-300">
        <img src="avatar3.jpg" alt="Sara M." class="w-16 h-16 rounded-full mx-auto mb-4">
        <p class="text-muted-black text-base italic mb-2">“SmartShop’s interface is so intuitive — I love how everything works smoothly.”</p>
        <p class="text-sm font-medium text-gray-700">Sara M. – Lifestyle Blogger</p>
      </div>
    </div>
  </div>
</section>


  <!-- FEATURED SELLERS -->
<section class="bg-gray-50 py-16 px-4" data-aos="fade-up" data-aos-delay="500">
  <div class="max-w-6xl mx-auto text-center">
    <h2 class="text-3xl font-semibold text-main-teal mb-12">Meet Our Featured Sellers</h2>
    <div class="grid gap-10 md:grid-cols-3">
      <div class="bg-white p-6 rounded-xl shadow-sm hover:shadow-md transition duration-300 text-center">
        <img src="seller1.jpg" alt="Ella Styles" class="w-20 h-20 rounded-full mx-auto mb-4">
        <h3 class="text-lg font-semibold text-muted-black mb-1">Ella Styles</h3>
        <p class="text-sm text-gray-600 mb-2">Trendy apparel & accessories</p>
        <a href="#" class="text-main-teal text-sm hover:underline">View Store</a>
      </div>
      <div class="bg-white p-6 rounded-xl shadow-sm hover:shadow-md transition duration-300 text-center">
        <img src="seller2.jpg" alt="TechLoop" class="w-20 h-20 rounded-full mx-auto mb-4">
        <h3 class="text-lg font-semibold text-muted-black mb-1">TechLoop</h3>
        <p class="text-sm text-gray-600 mb-2">Smart gadgets and tools</p>
        <a href="#" class="text-main-teal text-sm hover:underline">View Store</a>
      </div>
      <div class="bg-white p-6 rounded-xl shadow-sm hover:shadow-md transition duration-300 text-center">
        <img src="seller3.jpg" alt="Green Haven" class="w-20 h-20 rounded-full mx-auto mb-4">
        <h3 class="text-lg font-semibold text-muted-black mb-1">Green Haven</h3>
        <p class="text-sm text-gray-600 mb-2">Sustainable lifestyle goods</p>
        <a href="#" class="text-main-teal text-sm hover:underline">View Store</a>
      </div>
    </div>
  </div>
</section>

    <!-- FOOTER -->
    <footer class="mt-12 text-sm text-gray-500 text-center pb-6">
        &copy; <?= date('Y') ?> SmartShop. All rights reserved.
    </footer>

    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init();
    </script>

</body>
</html>
