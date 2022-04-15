<?php
require_once 'inc.php';
require_once 'src/functions.php';
echo "<pre>";

if (isset($_REQUEST['email'])) {
    $user = findUser($_REQUEST['email']);

    if (!$user) {
        $user = createUser([
            'name' => $_REQUEST['name'],
            'email' => $_REQUEST['email'],
            'phone' => $_REQUEST['phone'],
        ]);
    }

    $orderResult = createOrder($user['id'], [
        'street' => trim($_REQUEST['street']),
        'home' => trim($_REQUEST['home']),
        'part' => trim($_REQUEST['part']),
        'appt' => trim($_REQUEST['appt']),
        'floor' => trim($_REQUEST['floor']),
    ]);

    echo printMessage($orderResult);
}

