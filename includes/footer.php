    </main>
</div>

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('-translate-x-full');
    document.getElementById('sidebar-overlay').classList.toggle('hidden');
}

function toggleNotifications() {
    document.getElementById('notif-dropdown').classList.toggle('hidden');
}

function markRead(id) {
    fetch('mark_read.php?id=' + id).then(() => {
        location.reload();
    });
}

// Close notification dropdown on outside click
document.addEventListener('click', function(e) {
    const dropdown = document.getElementById('notif-dropdown');
    const bell = dropdown?.previousElementSibling;
    if (dropdown && !dropdown.contains(e.target) && !bell?.contains(e.target)) {
        dropdown.classList.add('hidden');
    }
});
</script>
</body>
</html>
