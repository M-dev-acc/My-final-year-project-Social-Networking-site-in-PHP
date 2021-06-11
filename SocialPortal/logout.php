<?php
include "config/Database.php";
include "models/Login.php";

if ($_POST["Confirm"]) {
    if ($_POST["allDevices"]) {
        Database::runQuery("DELETE FROM tbl_login_tokens WHERE user_id = :user_id", array(":user_id" => Login::isUserLoggedIn()));
        header('location: login.html');
    } else {
        if ($_COOKIE["SP_login_id"]) {
            Database::runQuery("DELETE FROM tbl_login_tokens WHERE login_token = :login_token", array(":login_token" => md5($_COOKIE["SP_login_id"])));
        }
        setcookie('SP_login_id', '1', time() - 3600);
        setcookie('SP_login_id_', '1', time() - 3600);
        header("location: login.html");
    }

}
