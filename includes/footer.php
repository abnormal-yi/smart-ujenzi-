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

// Marks notification as read, navigates to link, updates badge immediately
function markRead(id, link) {
    fetch('mark_read.php?id=' + id)
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.ok) {
                updateNotifBadge(data.remaining);
                if (link && link !== '#') {
                    window.location.href = link;
                }
            }
        })
        .catch(function() {
            // fallback: navigate anyway
            if (link && link !== '#') {
                window.location.href = link;
            }
        });
}

// Updates the bell badge and header unread text based on remaining count
function updateNotifBadge(remaining) {
    var badge = document.getElementById('notif-badge');
    var headerBadge = document.getElementById('notif-header-badge');
    if (badge) {
        if (remaining > 0) {
            badge.textContent = remaining;
            badge.classList.remove('hidden');
        } else {
            badge.classList.add('hidden');
        }
    }
    if (headerBadge) {
        if (remaining > 0) {
            headerBadge.textContent = remaining + ' unread';
            headerBadge.classList.remove('hidden');
        } else {
            headerBadge.classList.add('hidden');
        }
    }
}

// Closes notification dropdown when user clicks outside of it
document.addEventListener('click', function(e) {
    var dropdown = document.getElementById('notif-dropdown');
    var bell = dropdown ? dropdown.previousElementSibling : null;
    if (dropdown && !dropdown.contains(e.target) && bell && !bell.contains(e.target)) {
        dropdown.classList.add('hidden');
    }
});

// Toggles language between English and Swahili
function toggleLang() {
    document.getElementById('lang-form').submit();
}
</script>
</body>
</html>
