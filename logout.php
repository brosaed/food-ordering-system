<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

logout();
redirectWithMessage('index.php', 'success', 'You have been logged out successfully');
