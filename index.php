<?php
// Landing page: public-facing homepage with hero, contractors listing, features, and testimonials
require_once __DIR__ . '/includes/functions.php';
$isLoggedIn = isAuthenticated();
$userName = $_SESSION['user_name'] ?? '';
$role = $_SESSION['role'] ?? '';
// Fetch NCA verified contractor companies sorted by rating (highest first)
$companies = runQuery('SELECT * FROM companies ORDER BY rating DESC');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SmartUjenzi - Smart Construction Management</title>
    <!-- Tailwind CSS via CDN for utility-first responsive styles -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="min-h-screen bg-white text-gray-900">

<!-- Fixed top navigation bar with brand logo and auth links -->
<nav class="fixed top-0 left-0 right-0 z-50 bg-white/95 backdrop-blur-md border-b border-gray-200 shadow-sm">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex items-center justify-between h-16 lg:h-20">
            <div class="flex items-center space-x-3">
                <span class="w-10 h-10 bg-yellow-500 rounded-full flex items-center justify-center text-white text-lg font-bold">S</span>
                <span class="text-xl lg:text-2xl font-bold tracking-wider text-gray-800">SMART UJENZI</span>
            </div>
            <div class="flex items-center space-x-3">
                <?php if ($isLoggedIn): ?>
                    <!-- Show user name and dashboard link if logged in -->
                    <span class="text-gray-500 text-sm hidden sm:block"><?= htmlspecialchars($userName) ?></span>
                    <a href="dashboard.php" class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold px-5 py-2.5 rounded-xl text-sm transition-colors">Dashboard</a>
                <?php else: ?>
                    <!-- Show login and CTA buttons for visitors -->
                    <a href="login.php" class="text-gray-600 hover:text-gray-900 px-4 py-2 text-sm font-medium transition-colors">Log in</a>
                    <a href="login.php" class="bg-yellow-500 hover:bg-yellow-600 text-white font-semibold px-5 py-2.5 rounded-xl text-sm transition-colors">Get Started</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<!-- Hero Section: full-screen banner with construction background and CTA -->
<section class="relative min-h-screen flex items-center overflow-hidden pt-16">
    <!-- Background image with dark gradient overlay -->
    <div class="absolute inset-0">
        <img src="public/login-hero.jpg" alt="Construction" class="w-full h-full object-cover opacity-60">
        <div class="absolute inset-0 bg-gradient-to-r from-[#0C0D10]/70 via-[#0C0D10]/40 to-transparent"></div>
    </div>
    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-32 w-full">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <!-- Left column: headline, description, CTAs, social proof -->
            <div class="text-center lg:text-left">
                <!-- Badge label -->
                <div class="inline-flex items-center px-3 py-1.5 rounded-full bg-yellow-500/10 border border-yellow-500/20 text-yellow-500 text-xs font-medium mb-6">
                    <span class="w-2 h-2 bg-yellow-500 rounded-full mr-2"></span>
                    Finally, Construction is Smart and Honest!
                </div>
                <h1 class="text-4xl sm:text-5xl lg:text-6xl xl:text-7xl font-bold leading-tight text-white">
                    Finally, Construction
                    <span class="block text-yellow-500">Has Brains!</span>
                </h1>
                <p class="text-gray-400 text-base sm:text-lg lg:text-xl mt-6 max-w-xl mx-auto lg:mx-0 leading-relaxed">
                    A revolutionary unified portal to manage milestone-based building projects, smart resource forecasting, structural safety, and secure payments.
                </p>
                <!-- Call-to-action buttons depending on auth state -->
                <div class="flex flex-col sm:flex-row gap-4 mt-8 justify-center lg:justify-start">
                    <?php if ($isLoggedIn): ?>
                        <a href="dashboard.php" class="bg-yellow-500 hover:bg-yellow-600 text-black font-bold px-8 py-4 rounded-xl transition-colors text-center">Go to Dashboard</a>
                    <?php else: ?>
                        <a href="login.php" class="bg-yellow-500 hover:bg-yellow-600 text-black font-bold px-8 py-4 rounded-xl transition-colors text-center">Start Free Trial</a>
                        <a href="#contractors" class="border border-gray-600 hover:border-gray-500 text-white font-semibold px-8 py-4 rounded-xl transition-colors text-center">Find a Builder</a>
                    <?php endif; ?>
                </div>
                <!-- Social proof: avatars and stats -->
                <div class="flex items-center justify-center lg:justify-start gap-8 mt-10 text-sm text-gray-500">
                    <div class="flex items-center">
                        <div class="flex -space-x-2">
                            <div class="w-8 h-8 rounded-full bg-gray-700 border-2 border-[#0C0D10] flex items-center justify-center text-xs font-bold text-gray-300">JM</div>
                            <div class="w-8 h-8 rounded-full bg-gray-600 border-2 border-[#0C0D10] flex items-center justify-center text-xs font-bold text-white">AK</div>
                            <div class="w-8 h-8 rounded-full bg-gray-500 border-2 border-[#0C0D10] flex items-center justify-center text-xs font-bold text-white">PW</div>
                        </div>
                        <span class="ml-3 text-gray-400"><span class="text-white font-semibold">500+</span> contractors</span>
                    </div>
                    <div class="hidden sm:block h-8 w-px bg-gray-800"></div>
                    <span class="text-gray-400">⭐ 4.9/5 from 200+ reviews</span>
                </div>
            </div>
            <!-- Right column: decorative feature cards (desktop only) -->
            <div class="hidden lg:flex flex-col items-end space-y-4">
                <div class="bg-white/10 backdrop-blur-md border border-white/20 p-4 rounded-2xl w-72">
                    <div class="flex items-center mb-2">
                        <div class="w-8 h-8 bg-blue-500 rounded-full flex items-center justify-center mr-3">
                            <span class="text-white text-xs font-bold">B</span>
                        </div>
                        <div>
                            <div class="font-bold text-sm text-white">Blueprint & Site</div>
                            <div class="text-xs text-gray-300">Live Preview</div>
                        </div>
                    </div>
                </div>
                <div class="bg-white/10 backdrop-blur-md border border-white/20 p-4 rounded-2xl w-72 mr-8">
                    <div class="flex items-center">
                        <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center text-white font-bold text-xs mr-3">C</div>
                        <div>
                            <div class="font-bold text-sm text-white">Connect Ecosystem</div>
                            <div class="text-xs text-gray-300">Clients, PMs, Fundis</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Trusted By companies section with brand logos -->
<section class="py-12 bg-gray-50 border-y border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <p class="text-center text-gray-400 text-sm font-medium mb-8">TRUSTED BY CONSTRUCTION COMPANIES ACROSS TANZANIA</p>
        <div class="flex flex-wrap justify-center gap-8 lg:gap-16 text-gray-300">
            <span class="text-xl font-bold tracking-widest">JIJUENGE</span>
            <span class="text-xl font-bold tracking-widest">MWANGA</span>
            <span class="text-xl font-bold tracking-widest">MAISHA</span>
            <span class="text-xl font-bold tracking-widest">UJENZI</span>
            <span class="text-xl font-bold tracking-widest">JENGA</span>
        </div>
    </div>
</section>

<!-- NCA Verified Contractors Section: searchable/filterable contractor cards -->
<section id="contractors" class="py-20 lg:py-28 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12">
            <span class="text-yellow-600 text-sm font-semibold tracking-widest uppercase">NCA VERIFIED TRUSTED CONTRACTORS</span>
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold mt-4 text-gray-900">
                Select the most compatible, certified builder for your dream project and secure funds with transparency.
            </h2>
        </div>

        <!-- Search bar and city filter dropdown for filtering contractors -->
        <div class="flex flex-col sm:flex-row gap-4 mb-10 max-w-2xl mx-auto">
            <input type="text" id="contractor-search" placeholder="Search builder by name, city or location..."
                   class="flex-1 px-4 py-3 bg-white border border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:border-yellow-500 transition-colors">
            <select id="city-filter" class="px-4 py-3 bg-white border border-gray-300 rounded-xl text-gray-900 focus:outline-none focus:border-yellow-500 transition-colors">
                <option value="all">All Cities</option>
                <option value="arusha">Arusha</option>
                <option value="dar">Dar es Salaam</option>
            </select>
        </div>

        <!-- Dynamic contractor card grid with data attributes for JS filtering -->
        <div id="contractor-list" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">

            <?php foreach ($companies as $c):
                // Parse licenses from newline-separated text into an array
                $licenses = array_filter(array_map('trim', explode("\n", $c['licenses'] ?? '')));
                $gradients = ['from-blue-100 to-blue-50', 'from-green-100 to-green-50', 'from-amber-100 to-amber-50', 'from-purple-100 to-purple-50', 'from-rose-100 to-rose-50'];
                $g = $gradients[$c['id'] % count($gradients)];
            ?>
            <?php
                // Map company images based on company ID
                $img = '';
                if ($c['company_id'] === 'COMP_1') $img = 'public/kazi-bora.jpg';
                elseif ($c['company_id'] === 'COMP_2') $img = 'public/aman-builders.jpg';
                elseif ($c['company_id'] === 'COMP_3') $img = 'public/pamoja.jpg';
            ?>
            <!-- Single contractor card with data-city and data-name for filtering -->
            <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-all" data-city="<?= htmlspecialchars($c['city']) ?>" data-name="<?= htmlspecialchars($c['name']) ?>">
                <div class="h-40 relative overflow-hidden">
                    <!-- Company cover image -->
                    <img src="<?= $img ?>" alt="<?= htmlspecialchars($c['name']) ?>" class="w-full h-full object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                    <!-- Top-left badges: NCA Certified + Verified status -->
                    <div class="absolute top-3 left-3 flex items-center space-x-2">
                        <span class="px-2 py-0.5 bg-yellow-100 border border-yellow-200 text-yellow-700 text-xs rounded-full font-medium">NCA Certified</span>
                        <span class="px-2 py-0.5 <?= $c['verified'] ? 'bg-green-100 border-green-200 text-green-700' : 'bg-yellow-100 border-yellow-200 text-yellow-700' ?> text-xs rounded-full font-medium"><?= $c['verified'] ? 'Verified' : 'Pending' ?></span>
                    </div>
                    <!-- Top-right rating badge -->
                    <div class="absolute top-3 right-3 flex items-center bg-white/80 px-2 py-0.5 rounded-full">
                        <span class="text-yellow-500 text-sm">★</span>
                        <span class="text-gray-800 text-sm font-bold ml-1"><?= $c['rating'] ?></span>
                        <span class="text-gray-400 text-xs ml-1">/5.0</span>
                    </div>
                    <!-- Bottom-left company logo initials -->
                    <div class="absolute bottom-3 left-3">
                        <span class="text-2xl font-bold text-white/30"><?= htmlspecialchars($c['logo_initials']) ?></span>
                    </div>
                </div>
                <div class="p-6">
                    <!-- Company name and ID -->
                    <div class="flex items-center justify-between mb-2">
                        <h3 class="text-xl font-bold text-gray-900"><?= htmlspecialchars($c['name']) ?></h3>
                        <span class="text-xs text-gray-400">ID: <?= htmlspecialchars($c['company_id']) ?></span>
                    </div>
                    <p class="text-gray-500 text-sm italic mb-4">"<?= htmlspecialchars($c['tagline']) ?>"</p>
                    <!-- Company details: location, experience, projects -->
                    <div class="space-y-2 text-sm">
                        <div class="flex items-center text-gray-500">
                            <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <?= htmlspecialchars($c['location']) ?>, <?= htmlspecialchars($c['country']) ?>
                        </div>
                        <div class="flex items-center justify-between text-gray-500">
                            <span>Years Experience</span>
                            <span class="text-gray-900 font-semibold"><?= $c['years_experience'] ?> Years</span>
                        </div>
                        <div class="flex items-center justify-between text-gray-500">
                            <span>Projects Completed</span>
                            <span class="text-gray-900 font-semibold"><?= $c['projects_completed'] ?>+ Projects</span>
                        </div>
                    </div>
                    <!-- Licenses and hire CTA -->
                    <div class="mt-4 pt-4 border-t border-gray-200">
                        <div class="space-y-1 text-xs text-gray-500">
                            <?php foreach ($licenses as $lic): ?>
                            <div class="flex items-center"><span class="w-3 h-3 rounded-full bg-yellow-400/30 mr-2"></span><?= htmlspecialchars($lic) ?></div>
                            <?php endforeach; ?>
                        </div>
                        <div class="flex items-center justify-between mt-3">
                            <span class="text-sm text-gray-500"><span class="text-gray-900 font-semibold"><?= $c['engineers'] ?></span> On-Site PMs</span>
                            <!-- CTA links to either requests page (if customer) or login -->
                            <a href="<?= $isLoggedIn && $role === 'customer' ? 'customer_requests.php' : 'login.php' ?>" class="inline-block px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-white text-sm font-semibold rounded-xl transition-colors">Choose / Hire Contractor</a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>

        </div>


    </div>
</section>

<!-- Features Section: three-column grid highlighting platform capabilities -->
<section class="py-20 lg:py-28 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <span class="text-yellow-600 text-sm font-semibold tracking-widest uppercase">Powerful integrated features</span>
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold mt-4 text-gray-900">
                Everything you need to manage your<br class="hidden sm:block">
                <span class="text-yellow-600">construction project transparently</span> and efficiently.
            </h2>
        </div>
        <div class="grid md:grid-cols-3 gap-6">
            <!-- Feature 1: Secure payments -->
            <div class="bg-white border border-gray-200 rounded-2xl p-8 text-center hover:shadow-md transition-all">
                <div class="w-16 h-16 bg-yellow-100 rounded-2xl flex items-center justify-center mx-auto mb-5">
                    <span class="text-3xl">🔒</span>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Safe Payment Protection</h3>
                <p class="text-gray-500 leading-relaxed">Contracts funds are locked and released only when clients verify that construction milestones comply completely with county inspections.</p>
            </div>
            <!-- Feature 2: Cost estimation -->
            <div class="bg-white border border-gray-200 rounded-2xl p-8 text-center hover:shadow-md transition-all">
                <div class="w-16 h-16 bg-yellow-100 rounded-2xl flex items-center justify-center mx-auto mb-5">
                    <span class="text-3xl">🤖</span>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Instant Cost Estimates</h3>
                <p class="text-gray-500 leading-relaxed">Instantly estimate core bill of materials (cement, quality sand, steel bars) and calculate total costs based on regional index prices.</p>
            </div>
            <!-- Feature 3: Site reports -->
            <div class="bg-white border border-gray-200 rounded-2xl p-8 text-center hover:shadow-md transition-all">
                <div class="w-16 h-16 bg-yellow-100 rounded-2xl flex items-center justify-center mx-auto mb-5">
                    <span class="text-3xl">📱</span>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Daily Site Reports</h3>
                <p class="text-gray-500 leading-relaxed">Supervisors lodge real-time progress, capture pictures, log builder attendance, and flag structural risks instantly.</p>
            </div>
        </div>
    </div>
</section>

<!-- Stats Section: key metrics to build credibility -->
<section class="py-20 bg-white border-y border-gray-200">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 text-center">
            <div>
                <div class="text-4xl lg:text-5xl font-bold text-yellow-600">500+</div>
                <div class="text-gray-500 mt-2">Projects Managed</div>
            </div>
            <div>
                <div class="text-4xl lg:text-5xl font-bold text-yellow-600">50+</div>
                <div class="text-gray-500 mt-2">Active Contractors</div>
            </div>
            <div>
                <div class="text-4xl lg:text-5xl font-bold text-yellow-600">98%</div>
                <div class="text-gray-500 mt-2">On-Time Delivery</div>
            </div>
            <div>
                <div class="text-4xl lg:text-5xl font-bold text-yellow-600">24/7</div>
                <div class="text-gray-500 mt-2">Support Available</div>
            </div>
        </div>
    </div>
</section>

<!-- How It Works: three numbered step cards -->
<section class="py-20 lg:py-28 bg-gray-50">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <span class="text-yellow-600 text-sm font-semibold tracking-widest uppercase">How It Works</span>
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold mt-4 text-gray-900">
                Get Started in <span class="text-yellow-600">3 Simple Steps</span>
            </h2>
        </div>
        <div class="grid md:grid-cols-3 gap-8 lg:gap-12">
            <div class="text-center">
                <div class="w-16 h-16 bg-yellow-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                    <span class="text-2xl font-bold text-yellow-600">1</span>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Choose Your Builder</h3>
                <p class="text-gray-500">Browse NCA certified contractors, compare ratings and experience, then hire the best fit for your project.</p>
            </div>
            <div class="text-center">
                <div class="w-16 h-16 bg-yellow-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                    <span class="text-2xl font-bold text-yellow-600">2</span>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Agree on Milestones</h3>
                <p class="text-gray-500">Set project milestones, agree on budget and timeline. Funds are secured in escrow until each milestone is verified.</p>
            </div>
            <div class="text-center">
                <div class="w-16 h-16 bg-yellow-100 rounded-2xl flex items-center justify-center mx-auto mb-6">
                    <span class="text-2xl font-bold text-yellow-600">3</span>
                </div>
                <h3 class="text-xl font-bold text-gray-900 mb-3">Track & Build</h3>
                <p class="text-gray-500">Monitor progress with daily site reports, track materials, communicate with your team, and see your project come to life.</p>
            </div>
        </div>
    </div>
</section>

<!-- Testimonials section: user success stories -->
<section class="py-20 lg:py-28 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16">
            <span class="text-yellow-600 text-sm font-semibold tracking-widest uppercase">Testimonials</span>
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold mt-4 text-gray-900">What Contractors Say</h2>
        </div>
        <div class="grid md:grid-cols-3 gap-6">
            <!-- Testimonial 1 -->
            <div class="bg-white border border-gray-200 rounded-2xl p-8 shadow-sm">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center font-bold text-gray-600">JM</div>
                    <div class="ml-4">
                        <div class="font-semibold text-gray-900">James Mwangi</div>
                        <div class="text-sm text-gray-500">General Contractor</div>
                    </div>
                </div>
                <p class="text-gray-600 leading-relaxed">"SmartUjenzi has transformed how we manage our construction sites. The material tracking alone saved us 20% on waste."</p>
                <div class="mt-4 text-yellow-500">★★★★★</div>
            </div>
            <!-- Testimonial 2 -->
            <div class="bg-white border border-gray-200 rounded-2xl p-8 shadow-sm">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center font-bold text-gray-600">AK</div>
                    <div class="ml-4">
                        <div class="font-semibold text-gray-900">Aisha Kombo</div>
                        <div class="text-sm text-gray-500">Project Manager</div>
                    </div>
                </div>
                <p class="text-gray-600 leading-relaxed">"The task assignment and progress tracking features are exactly what we needed. Our team went up by 40%."</p>
                <div class="mt-4 text-yellow-500">★★★★★</div>
            </div>
            <!-- Testimonial 3 -->
            <div class="bg-white border border-gray-200 rounded-2xl p-8 shadow-sm">
                <div class="flex items-center mb-4">
                    <div class="w-12 h-12 rounded-full bg-gray-200 flex items-center justify-center font-bold text-gray-600">PW</div>
                    <div class="ml-4">
                        <div class="font-semibold text-gray-900">Peter Wanjohi</div>
                        <div class="text-sm text-gray-500">Site Supervisor</div>
                    </div>
                </div>
                <p class="text-gray-600 leading-relaxed">"Being able to see real-time project updates and communicate with the team has made site coordination effortless."</p>
                <div class="mt-4 text-yellow-500">★★★★★</div>
            </div>
        </div>
    </div>
</section>

<!-- Final Call-to-Action Section -->
<section class="py-20 lg:py-28 bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-gray-900">
            Ready to <span class="text-yellow-600">Build Smarter?</span>
        </h2>
        <p class="text-gray-500 text-lg mt-4 max-w-2xl mx-auto">
            Join hundreds of contractors already using SmartUjenzi to manage their construction projects efficiently.
        </p>
        <div class="flex flex-col sm:flex-row gap-4 justify-center mt-8">
            <?php if ($isLoggedIn): ?>
                <a href="dashboard.php" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold px-8 py-4 rounded-xl transition-colors shadow-sm">Go to Dashboard</a>
            <?php else: ?>
                <a href="login.php" class="bg-yellow-500 hover:bg-yellow-600 text-white font-bold px-8 py-4 rounded-xl transition-colors shadow-sm">Get Started Free</a>
                <a href="login.php" class="border border-gray-300 hover:border-gray-400 text-gray-700 font-semibold px-8 py-4 rounded-xl transition-colors">Schedule a Demo</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- Footer with branding, links, and copyright -->
<footer class="border-t border-gray-200 py-12 bg-gray-900 text-gray-400">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid md:grid-cols-4 gap-8">
            <div class="md:col-span-2">
                <div class="flex items-center space-x-3 mb-4">
                    <span class="w-8 h-8 bg-yellow-500 rounded-full flex items-center justify-center text-white text-sm font-bold">S</span>
                    <span class="text-lg font-bold tracking-wider text-white">SMART UJENZI</span>
                </div>
                <p class="text-gray-500 text-sm max-w-md">
                    Smart construction management platform. Built for contractors, by contractors.
                    Empowering construction teams across Tanzania to deliver projects on time and on budget.
                </p>
            </div>
            <div>
                <h4 class="font-semibold text-white mb-4">Platform</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="#contractors" class="hover:text-white transition-colors">Find a Builder</a></li>
                    <li><a href="#features" class="hover:text-white transition-colors">Features</a></li>
                    <li><a href="login.php" class="hover:text-white transition-colors">Sign In</a></li>
                </ul>
            </div>
            <div>
                <h4 class="font-semibold text-white mb-4">Company</h4>
                <ul class="space-y-2 text-sm">
                    <li><span class="hover:text-white transition-colors cursor-default">About</span></li>
                    <li><span class="hover:text-white transition-colors cursor-default">Contact</span></li>
                    <li><span class="hover:text-white transition-colors cursor-default">Privacy Policy</span></li>
                </ul>
            </div>
        </div>
        <div class="border-t border-gray-800 mt-8 pt-8 text-center text-sm text-gray-600">
            &copy; <?= date('Y') ?> SmartUjenzi. All rights reserved.
        </div>
    </div>
</footer>

<!-- Client-side contractor search/filter functionality -->
<script>
const searchInput = document.getElementById('contractor-search');
const cityFilter = document.getElementById('city-filter');
const contractorList = document.getElementById('contractor-list');

// Filters contractor cards by both text search and city selection
function filterContractors() {
    const search = searchInput.value.toLowerCase();
    const city = cityFilter.value;
    const cards = contractorList.querySelectorAll('[data-city]');

    cards.forEach(card => {
        const cardCity = card.dataset.city;
        const cardName = card.dataset.name.toLowerCase();
        const cardText = card.textContent.toLowerCase();
        const matchesCity = city === 'all' || cardCity === city;
        const matchesSearch = search === '' || cardName.includes(search) || cardText.includes(search);
        card.style.display = matchesCity && matchesSearch ? '' : 'none';
    });
}

// Attach event listeners to search input and city filter dropdown
searchInput.addEventListener('input', filterContractors);
cityFilter.addEventListener('change', filterContractors);
</script>

</body>
</html>
