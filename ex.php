<link rel="stylesheet" href="css/profile.css">

<?php
// $post   = array('img' => "iamge.jpg");
// $string = "bla bla";
// echo "'image: ${post['img']}'";
// $firstString = "bla bla bla";

// $secondString = &$firstString;
// $secondString .= "use proper words";

// echo $secondString;
include "config/Database.php";
include "models/Login.php";
include "models/Message.php";
// $var = Database::runQuery("SELECT user_name FROM tbl_users WHERE user_name = :username", array(":username" => $_GET['username']))[0]["user_name"];

// echo ($var) ? $var : "no";

// echo strlen("Lorem ipsum dolor sit amet consectetur adipisicing elit. Ratione, quaerat? Lorem ipsum dolor sit, amet consectetur adipisicing elit. Iusto, labore! Lorem ipsum, dolor sit amet consectetur adipisicing elit. Eius, asperiores.adipisicing ");

// echo Notification::timeAgo("2021-07-17 20:47:17");
session_start();
// echo $_SESSION['message_csrf'];
// $mesg = Message::displayMessageS(6, 11);
// $recentChats = Database::runQuery("SELECT receiver FROM tbl_messages WHERE sender=:sender ORDER BY id DESC", array(":sender" => Login::isUserLoggedIn()));
// // $array = array_unique($recentChats);
// $recentChatUsers = array();
// foreach ($recentChats as $value) {
// 	// echo "<pre>";
// 	// print_r($value['receiver']);
// 	array_push($recentChatUsers, $value['receiver']);
// }
// echo "<pre>";
// // print_r();

// foreach (array_unique($recentChatUsers) as $user) {
// 	print_r($userData);
// }

echo substr("Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. enim ad minim veniam, Ut enim ad minim veniam,", 0, 170) . "...";
?>

