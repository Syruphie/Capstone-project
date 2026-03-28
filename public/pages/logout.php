<?php
require_once 'config/database.php';
require_once 'src/classes/Frontend/bootstrap.php';

$user = new FrontendUser();
$user->logout();

header('Location: index.php');
exit;

