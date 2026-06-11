// Periodically pings the server every 60 seconds to keep the session alive
// and check for new notifications (the ?check=1 param is a keepalive marker)
setInterval(function() {
    fetch('mark_read.php?check=1')
        .then(r => r.text())
        .catch(() => {});
}, 60000); // 60,000 ms = 1 minute interval
