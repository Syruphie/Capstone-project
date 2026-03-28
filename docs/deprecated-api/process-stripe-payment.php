<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../src/classes/Frontend/bootstrap.php';
require_once __DIR__ . '/../src/api/Action/CreatePaymentIntentAction.php';

(new CreatePaymentIntentAction())->handle();

