// Auto-refresh notifications every 60 seconds
setInterval(function() {
    fetch('mark_read.php?check=1')
        .then(r => r.text())
        .catch(() => {});
}, 60000);
