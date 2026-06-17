<?php
$pageTitle = 'Payment History';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['client']);
require_once __DIR__ . '/../includes/header.php';

$userId = $_SESSION['user_id'];
$payments = runQuery("SELECT p.*, pr.name as project_name FROM payments p JOIN projects pr ON p.project_id = pr.id WHERE pr.customer_id = ? ORDER BY p.payment_date DESC", [$userId]);
$totalAmount = array_sum(array_column($payments, 'amount'));
?>

<?php if (empty($payments)): ?>
<div class="bg-white rounded-xl shadow-sm border border-gray-100 p-12 text-center">
    <svg class="w-16 h-16 mx-auto text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
    <h3 class="text-xl font-bold text-gray-800 mb-2">No Payments Yet</h3>
    <p class="text-gray-500">Payments will appear here once your project is active.</p>
</div>
<?php else: ?>
<div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="bg-gray-50 border-b border-gray-100">
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Project</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Description</th>
                    <th class="text-right px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Amount (TZS)</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Date</th>
                    <th class="text-left px-6 py-3 text-xs font-semibold text-gray-500 uppercase tracking-wider">Status</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($payments as $p): ?>
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-6 py-4 text-sm font-medium text-gray-900"><?= htmlspecialchars($p['project_name']) ?></td>
                    <td class="px-6 py-4 text-sm text-gray-500"><?= htmlspecialchars($p['description']) ?></td>
                    <td class="px-6 py-4 text-sm text-gray-900 text-right font-semibold"><?= number_format($p['amount']) ?></td>
                    <td class="px-6 py-4 text-sm text-gray-500"><?= date('d M Y', strtotime($p['payment_date'])) ?></td>
                    <td class="px-6 py-4">
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium
                            <?= $p['status'] === 'completed' ? 'bg-green-100 text-green-700' : ($p['status'] === 'pending' ? 'bg-yellow-100 text-yellow-700' : 'bg-red-100 text-red-700') ?>">
                            <?= ucfirst($p['status']) ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr class="bg-gray-50 border-t-2 border-gray-200">
                    <td colspan="2" class="px-6 py-4 text-sm font-bold text-gray-700 text-right">Total</td>
                    <td class="px-6 py-4 text-sm font-bold text-gray-900 text-right">TZS <?= number_format($totalAmount) ?></td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
