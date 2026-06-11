    </main>
<!-- End of main content (started in header.php) -->
</div>
<!-- End of sidebar + main flex layout (started in header.php) -->

<script>
// Toggles the mobile sidebar visibility
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('-translate-x-full');
    document.getElementById('sidebar-overlay').classList.toggle('hidden');
}

// Toggles the notification dropdown visibility
function toggleNotifications() {
    document.getElementById('notif-dropdown').classList.toggle('hidden');
}

// Marks a notification as read via AJAX, then reloads the page to refresh the badge
function markRead(id) {
    fetch('mark_read.php?id=' + id).then(() => {
        location.reload();
    });
}

// Closes notification dropdown when user clicks outside of it
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
