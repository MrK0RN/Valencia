<?php
require_once __DIR__ . '/../classes/Auth.php';
require_once __DIR__ . '/../classes/Session.php';

Session::start();
Auth::logout();

header('Location: /admin/login.php');
exit;

