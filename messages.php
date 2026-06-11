<?php
$pageTitle = 'Discussions';
require_once __DIR__ . '/includes/functions.php';
requireRole(['admin', 'manager', 'supervisor']);
require_once __DIR__ . '/includes/header.php';

$projects = runQuery('SELECT id, name FROM projects');
$selectedProject = $_GET['project_id'] ?? ($projects[0]['id'] ?? null);

$messages = [];
if ($selectedProject) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message'])) {
        executeQuery('INSERT INTO messages (project_id, sender_id, message) VALUES (?, ?, ?)',
            [$selectedProject, $_SESSION['user_id'], $_POST['message']]);
        redirect('messages.php?project_id=' . $selectedProject);
    }
    $messages = runQuery('SELECT m.*, u.name as sender_name, u.role as sender_role FROM messages m JOIN users u ON m.sender_id = u.id WHERE m.project_id = ? ORDER BY m.created_at ASC', [$selectedProject]);
}
?>
<div class="grid grid-cols-1 lg:grid-cols-4 gap-6">
    <!-- Project List -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-4">
        <h3 class="font-bold text-gray-800 mb-3">Projects</h3>
        <div class="space-y-1">
            <?php foreach ($projects as $p): ?>
            <a href="messages.php?project_id=<?= $p['id'] ?>"
               class="block px-3 py-2 rounded-lg text-sm <?= $selectedProject == $p['id'] ? 'bg-slate-900 text-white' : 'text-gray-600 hover:bg-gray-100' ?>">
                <?= htmlspecialchars($p['name']) ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Messages -->
    <div class="lg:col-span-3 bg-white rounded-xl shadow-sm border border-gray-100 flex flex-col h-[600px]">
        <?php if ($selectedProject): ?>
        <div class="p-4 border-b border-gray-200 font-bold text-gray-800">
            <?= htmlspecialchars(runQuery('SELECT name FROM projects WHERE id = ?', [$selectedProject])[0]['name'] ?? '') ?>
        </div>

        <div class="flex-1 overflow-y-auto p-4 space-y-4">
            <?php foreach ($messages as $m): ?>
            <div class="flex <?= $m['sender_id'] == $_SESSION['user_id'] ? 'justify-end' : 'justify-start' ?>">
                <div class="max-w-[70%] <?= $m['sender_id'] == $_SESSION['user_id'] ? 'bg-slate-800 text-white' : 'bg-gray-100 text-gray-800' ?> rounded-lg px-4 py-2">
                    <div class="flex items-center space-x-2 mb-1">
                        <span class="text-xs font-semibold <?= $m['sender_id'] == $_SESSION['user_id'] ? 'text-gray-300' : 'text-gray-500' ?>"><?= htmlspecialchars($m['sender_name']) ?></span>
                        <span class="text-xs <?= $m['sender_id'] == $_SESSION['user_id'] ? 'text-gray-400' : 'text-gray-400' ?>"><?= date('M j, g:i A', strtotime($m['created_at'])) ?></span>
                    </div>
                    <p class="text-sm"><?= htmlspecialchars($m['message']) ?></p>
                </div>
            </div>
            <?php endforeach; ?>
            <?php if (empty($messages)): ?>
                <p class="text-center text-gray-400 text-sm mt-10">No messages yet. Start the conversation!</p>
            <?php endif; ?>
        </div>

        <form method="POST" class="p-4 border-t border-gray-200">
            <div class="flex space-x-3">
                <input type="text" name="message" required placeholder="Type your message..."
                       class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-slate-500 text-sm">
                <button type="submit" class="px-6 py-2 bg-slate-900 text-white rounded-lg hover:bg-slate-800 text-sm font-medium">Send</button>
            </div>
        </form>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
