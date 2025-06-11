<?php
require_once '../include/config.php';

// Destroy the session
session_destroy();

// Redirect to login page
redirect('../auth/login.php');
?> 