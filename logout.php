<?php
// Start session to access and destroy it
session_start();
// Destroy all session data, effectively logging the user out
session_destroy();
// Redirect to the login page after logout
header('Location: login.php');
exit;
