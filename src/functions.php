<?php

/**
 * Поиск пользователя по $email
 * @param string $email Email адрес пользователя
 * @return null|array Массив пользователя или null если не найден
 */
function findUser(string $email): ?array
{
    global $dbConnection;
    $findQuery = $dbConnection->prepare("SELECT * FROM `users` where `email`= :email");

    $findQuery
        ->execute([
            'email' => $email
        ]);
    return $findQuery->fetch(PDO::FETCH_ASSOC) ?: null;
}

/**
 * Создаем пользователя
 * @param array $user Массив данных для создания пользователя
 * @return null|array Массив пользователя или null если не найден
 */
function createUser(array $user): ?array
{
    global $dbConnection;
    $insertQuery = $dbConnection->prepare("INSERT INTO `users` (`name`,`email`,`phone`) VALUES (:name, :email, :phone)");
    $insertQuery
        ->execute([
            'name' => $user['name'],
            'email' => $user['email'],
            'phone' => $user['phone'],
        ]);

    return findUser($user['email']);
}

/**
 * Добавляем заказ в базу
 * @param int $userId Айди пользователя
 * @param array $orderData Массив данных для создания заказа
 * @return array Результат создания заказа
 */
function createOrder(int $userId, array $orderData): array
{
    global $dbConnection;
    $insertQuery = $dbConnection->prepare("INSERT INTO `orders` (`author_id`,`address`,`comment`,`payment_type`,`is_need_call`) 
        VALUES (:author_id, :address, :comment, :payment_type, :is_need_call)");

    $full_address = [
        $orderData['street'] ? 'ул.' . trim($orderData['street']) : null,
        $orderData['home'] ? 'д.' . trim($orderData['home']) : null,
        $orderData['part'] ? 'корп.' . trim($orderData['part']) : null,
        $orderData['appt'] ? 'кв.' . trim($orderData['appt']) : null,
        $orderData['floor'] ? 'эт.' . trim($orderData['floor']) : null,
    ];
    $preparedAddress = join(', ', array_filter($full_address));

    $insertQuery
        ->execute([
            'author_id' => $userId,
            'address' => $preparedAddress,
            'comment' => $orderData['comment'] ?: null,
            'payment_type' => $orderData['payment_type'],
            'is_need_call' => empty($orderData['callback']) ? 1 : null,
        ]);
    $orderId = $dbConnection->lastInsertId();

    $countOrders = updateCountOrders($userId);

    return ['orderId' => $orderId, 'countOrders' => $countOrders, 'address' => $preparedAddress];
}

/**
 * Считаем и обновляем количество заказов у пользователя
 * @param int $userId Айди пользователя
 * @return int Количество заказов пользователя
 */
function updateCountOrders(int $userId): int
{
    global $dbConnection;
    $findQuery = $dbConnection->prepare("SELECT COUNT(*) FROM `orders` WHERE `author_id` = :user_id");
    $findQuery->execute(["user_id" => $userId]);
    $count = intval($findQuery->fetchColumn());

    $findQuery = $dbConnection->prepare("UPDATE `users` SET `orders_count` = :count WHERE `id` = :user_id");
    $findQuery->execute(["user_id" => $userId, 'count' => $count]);
    return $count;
}

/**
 * Выводим на экран результат оформления заказа
 * @param array $orderResult Результат оформления заказа
 * @return string Строка успешного оформления заказа
 */
function printMessage(array $orderResult): string
{
    if (empty($orderResult['orderId'])) {
        return "Ошибка оформления заказа :(";
    }

    return 'Спасибо, ваш заказ будет доставлен по адресу: “' . $orderResult['address'] . '”'
        . '<br>Номер вашего заказа: #' . $orderResult['orderId']
        . '<br>Это ваш ' . $orderResult['countOrders'] . '-й заказ!';
}


