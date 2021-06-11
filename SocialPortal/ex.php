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
include "models/Post.php";
$var = Database::runQuery("SELECT user_name FROM tbl_users WHERE user_name = :username", array(":username" => $_GET['username']))[0]["user_name"];

echo ($var) ? $var : "no";

// echo strlen("Lorem ipsum dolor sit amet consectetur adipisicing elit. Ratione, quaerat? Lorem ipsum dolor sit, amet consectetur adipisicing elit. Iusto, labore! Lorem ipsum, dolor sit amet consectetur adipisicing elit. Eius, asperiores.adipisicing ");
?>
