<?php
$pageTitle = 'Find a Contractor';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['client']);
require_once __DIR__ . '/../includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_request'])) {
    runQuery("INSERT INTO customer_requests (customer_id, company_id, project_type, location, description) VALUES (?,?,?,?,?)",
        [$_SESSION['user_id'], $_POST['company_id'], $_POST['project_type'], $_POST['location'], $_POST['description']]);
    $success = 'Request submitted! Admin will review shortly.';
}

$companies = runQuery("SELECT * FROM companies ORDER BY verified DESC, rating DESC");
$gradients = ['from-blue-100 to-blue-50', 'from-green-100 to-green-50', 'from-amber-100 to-amber-50', 'from-purple-100 to-purple-50', 'from-rose-100 to-rose-50'];
?>

<?php if (isset($success)): ?>
<div class="mb-4 p-4 rounded-lg text-sm bg-green-100 text-green-700 border border-green-200"><?= $success ?></div>
<?php endif; ?>

<div class="flex flex-col sm:flex-row gap-4 mb-6">
    <input type="text" id="contractor-search" placeholder="Search contractor by name, city or location..."
           class="flex-1 px-4 py-3 bg-white border border-gray-300 rounded-xl text-gray-900 placeholder-gray-400 focus:outline-none focus:border-yellow-500 transition-colors">
    <select id="city-filter" class="px-4 py-3 bg-white border border-gray-300 rounded-xl text-gray-900 focus:outline-none focus:border-yellow-500 transition-colors">
        <option value="all">All Cities</option>
        <option value="Arusha">Arusha</option>
        <option value="Dar es Salaam">Dar es Salaam</option>
    </select>
</div>

<div id="contractor-list" class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
    <?php foreach ($companies as $c):
        $licenses = array_filter(array_map('trim', explode("\n", $c['licenses'] ?? '')));
        $g = $gradients[$c['id'] % count($gradients)];
        $initials = !empty($c['logo_initials']) ? $c['logo_initials'] : strtoupper(substr($c['name'], 0, 2));
    ?>
    <div class="bg-white border border-gray-200 rounded-2xl overflow-hidden shadow-sm hover:shadow-md transition-all" data-city="<?= htmlspecialchars($c['city']) ?>" data-name="<?= htmlspecialchars($c['name']) ?>">
        <div class="h-40 relative overflow-hidden">
            <div class="w-full h-full bg-gradient-to-br <?= $g ?> flex items-center justify-center">
                <span class="text-5xl font-bold text-gray-700/40"><?= htmlspecialchars($initials) ?></span>
            </div>
            <div class="absolute inset-0 bg-gradient-to-t from-black/30 to-transparent"></div>
            <div class="absolute top-3 left-3 flex items-center space-x-2">
                <span class="px-2 py-0.5 bg-yellow-100 border border-yellow-200 text-yellow-700 text-xs rounded-full font-medium">NCA Certified</span>
                <span class="px-2 py-0.5 <?= $c['verified'] ? 'bg-green-100 border-green-200 text-green-700' : 'bg-yellow-100 border-yellow-200 text-yellow-700' ?> text-xs rounded-full font-medium"><?= $c['verified'] ? 'Verified' : 'Pending' ?></span>
            </div>
            <div class="absolute top-3 right-3 flex items-center bg-white/80 px-2 py-0.5 rounded-full">
                <span class="text-yellow-500 text-sm">★</span>
                <span class="text-gray-800 text-sm font-bold ml-1"><?= $c['rating'] ?></span>
                <span class="text-gray-400 text-xs ml-1">/5.0</span>
            </div>
            <div class="absolute bottom-3 left-3">
                <span class="text-2xl font-bold text-white/50"><?= htmlspecialchars($initials) ?></span>
            </div>
        </div>
        <div class="p-6">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-xl font-bold text-gray-900"><?= htmlspecialchars($c['name']) ?></h3>
                <span class="text-xs text-gray-400">ID: <?= htmlspecialchars($c['company_id']) ?></span>
            </div>
            <p class="text-gray-500 text-sm italic mb-4">"<?= htmlspecialchars($c['tagline']) ?>"</p>
            <div class="space-y-2 text-sm">
                <div class="flex items-center text-gray-500">
                    <svg class="w-4 h-4 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <?= htmlspecialchars($c['location']) ?>, <?= htmlspecialchars($c['city']) ?>
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
            <div class="mt-4 pt-4 border-t border-gray-200">
                <?php if (!empty($licenses)): ?>
                <div class="space-y-1 text-xs text-gray-500 mb-3">
                    <?php foreach ($licenses as $lic): ?>
                    <div class="flex items-center"><span class="w-3 h-3 rounded-full bg-yellow-400/30 mr-2"></span><?= htmlspecialchars($lic) ?></div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                <button onclick="openRequestModal(<?= $c['id'] ?>)" class="w-full px-4 py-2 bg-yellow-500 hover:bg-yellow-600 text-black font-semibold rounded-xl transition-colors text-sm">Select Company</button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Request Modal -->
<div id="request-modal" class="hidden fixed inset-0 z-50 flex items-center justify-center">
    <div class="fixed inset-0 bg-black/50" onclick="closeRequestModal()"></div>
    <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg mx-4 p-6 z-10 max-h-[90vh] overflow-y-auto">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-xl font-bold text-gray-900">Submit Request</h3>
            <button onclick="closeRequestModal()" class="text-gray-400 hover:text-gray-600">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <form method="POST" class="space-y-4">
            <input type="hidden" name="company_id" id="modal-company-id">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Project Type</label>
                <input type="text" name="project_type" required placeholder="e.g. Residential House, Office Building"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:outline-none focus:border-yellow-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Location</label>
                <input type="text" name="location" required placeholder="e.g. Dar es Salaam, Kinondoni"
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:outline-none focus:border-yellow-500">
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                <textarea name="description" rows="4" required placeholder="Describe your project requirements..."
                          class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:outline-none focus:border-yellow-500"></textarea>
            </div>
            <button type="submit" name="submit_request" class="w-full bg-yellow-500 hover:bg-yellow-600 text-black font-semibold px-4 py-2.5 rounded-xl transition-colors">Submit Request</button>
        </form>
    </div>
</div>

<script>
const searchInput = document.getElementById('contractor-search');
const cityFilter = document.getElementById('city-filter');
const contractorList = document.getElementById('contractor-list');

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

searchInput.addEventListener('input', filterContractors);
cityFilter.addEventListener('change', filterContractors);

function openRequestModal(companyId) {
    document.getElementById('modal-company-id').value = companyId;
    document.getElementById('request-modal').classList.remove('hidden');
}

function closeRequestModal() {
    document.getElementById('request-modal').classList.add('hidden');
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') closeRequestModal();
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
