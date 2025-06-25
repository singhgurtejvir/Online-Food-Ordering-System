<?php
require_once 'functions.php';

// Clear all session data
session_start();
session_unset();
session_destroy();

// Start new session for flash message
session_start();
setFlashMessage('success', 'You have been logged out successfully.');

// Redirect to home page
header("Location: index.php");
exit();
?>