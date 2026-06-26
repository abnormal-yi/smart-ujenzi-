<?php
$pageTitle = 'Upload Documents';
require_once __DIR__ . '/../includes/functions.php';
requireRole(['client', 'customer']);
require_once __DIR__ . '/../includes/header.php';

$userId = $_SESSION['user_id'];
$requests = runQuery("SELECT id, project_type, location, status FROM customer_requests WHERE customer_id = ? ORDER BY id DESC", [$userId]);
$requestId = (int)($_GET['request_id'] ?? ($requests[0]['id'] ?? 0));
$documents = [];

if ($requestId) {
    $documents = runQuery("SELECT * FROM request_documents WHERE request_id = ? ORDER BY created_at DESC", [$requestId]);
}
?>
<div class="max-w-4xl mx-auto">
    <h1 class="text-2xl font-bold mb-6">Upload Project Documents</h1>

    <?php if (empty($requests)): ?>
        <div class="bg-yellow-50 border border-yellow-200 text-yellow-700 p-4 rounded-lg">You have no project requests yet. Submit a request first from your dashboard.</div>
    <?php else: ?>
    <div class="mb-6">
        <label class="block text-sm font-medium text-gray-700 mb-1">Select Project Request</label>
        <select id="requestSelect" class="w-full px-3 py-2 border border-gray-300 rounded-lg text-sm" onchange="location.href='?request_id='+this.value">
            <?php foreach ($requests as $r): ?>
            <option value="<?= $r['id'] ?>" <?= $r['id'] === $requestId ? 'selected' : '' ?>>
                #<?= $r['id'] ?> — <?= htmlspecialchars($r['project_type']) ?> (<?= htmlspecialchars($r['location']) ?>) [<?= $r['status'] ?>]
            </option>
            <?php endforeach; ?>
        </select>
    </div>

    <?php if ($requestId): ?>
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 mb-6">
        <h2 class="text-lg font-semibold mb-4">Upload Files</h2>
        <form id="uploadForm" class="space-y-4">
            <input type="hidden" name="request_id" value="<?= $requestId ?>">
            <div class="border-2 border-dashed border-gray-300 rounded-lg p-8 text-center hover:border-blue-500 transition-colors" id="dropZone">
                <p class="text-gray-500 mb-2">Drag & drop files here or click to browse</p>
                <p class="text-xs text-gray-400">Allowed: PDF, DOC, DWG, DXF, JPG, PNG, ZIP (max 20MB each)</p>
                <input type="file" name="documents[]" multiple accept=".pdf,.doc,.docx,.jpg,.jpeg,.png,.gif,.zip,.dwg,.dxf" class="hidden" id="fileInput">
            </div>
            <div id="fileList" class="text-sm text-gray-600"></div>
            <button type="submit" id="uploadBtn" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 disabled:opacity-50" disabled>Upload Files</button>
            <div id="uploadProgress" class="hidden text-sm text-green-600">Uploading...</div>
        </form>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
        <h2 class="text-lg font-semibold mb-4">Uploaded Documents</h2>
        <?php if (empty($documents)): ?>
            <p class="text-gray-500 text-sm">No documents uploaded yet.</p>
        <?php else: ?>
        <div class="space-y-2">
            <?php foreach ($documents as $d): ?>
            <div class="flex items-center justify-between p-3 bg-gray-50 rounded-lg">
                <div class="flex items-center gap-3">
                    <span class="text-2xl"><?php
                        $extMap = ['pdf'=>'📄','doc'=>'📝','docx'=>'📝','jpg'=>'🖼️','jpeg'=>'🖼️','png'=>'🖼️','gif'=>'🖼️','dwg'=>'📐','dxf'=>'📐','zip'=>'📦'];
                        echo $extMap[$d['file_type']] ?? '📁';
                    ?></span>
                    <div>
                        <p class="text-sm font-medium"><?= htmlspecialchars($d['original_name']) ?></p>
                        <p class="text-xs text-gray-500"><?= number_format($d['file_size'] / 1024, 1) ?> KB</p>
                    </div>
                </div>
                <a href="../download-document.php?id=<?= $d['id'] ?>" class="text-sm text-blue-600 hover:underline">Download</a>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    <?php endif; ?>
</div>

<script>
document.getElementById('dropZone').addEventListener('click', () => document.getElementById('fileInput').click());
document.getElementById('dropZone').addEventListener('dragover', (e) => { e.preventDefault(); document.getElementById('dropZone').classList.add('border-blue-500'); });
document.getElementById('dropZone').addEventListener('dragleave', () => document.getElementById('dropZone').classList.remove('border-blue-500'));
document.getElementById('dropZone').addEventListener('drop', (e) => { e.preventDefault(); document.getElementById('dropZone').classList.remove('border-blue-500'); document.getElementById('fileInput').files = e.dataTransfer.files; updateFileList(); });

document.getElementById('fileInput').addEventListener('change', updateFileList);
function updateFileList() {
    const files = document.getElementById('fileInput').files;
    const list = document.getElementById('fileList');
    if (files.length > 0) {
        list.textContent = files.length + ' file(s) selected: ' + Array.from(files).map(f => f.name).join(', ');
        document.getElementById('uploadBtn').disabled = false;
    } else {
        list.textContent = '';
        document.getElementById('uploadBtn').disabled = true;
    }
}

document.getElementById('uploadForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const btn = document.getElementById('uploadBtn');
    const progress = document.getElementById('uploadProgress');
    btn.disabled = true;
    progress.classList.remove('hidden');
    const formData = new FormData(document.getElementById('uploadForm'));
    for (const file of document.getElementById('fileInput').files) formData.append('documents[]', file);
    formData.set('request_id', document.querySelector('input[name="request_id"]').value);
    try {
        const res = await fetch('../upload_handler.php', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) location.reload();
        else alert('Upload failed: ' + (data.error || 'Unknown error'));
    } catch (err) {
        alert('Upload failed: ' + err.message);
    }
    btn.disabled = false;
    progress.classList.add('hidden');
});
</script>
<?php require_once __DIR__ . '/../includes/footer.php'; ?>
