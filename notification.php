<?php
include 'config/Database.php';
include 'models/Login.php';
include 'models/Notification.php';

$userId = Login::isUserLoggedIn();
// echo "<pre>";
htmlspecialchars_decode(Notification::displayNotifications($userId));
print_r(Notification::calculateTime('2021-01-01 12:00:00'));
