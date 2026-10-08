<?php
require_once __DIR__ . '/auth.php';
unset($_SESSION['admin']);
session_regenerate_id(true);
redirect(base_url('admin/login.php'));
