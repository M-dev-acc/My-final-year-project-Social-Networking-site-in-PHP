<?php
include 'config/Database.php';
include 'models/Login.php';

$isTokenValid = False;
$message = "";

if (isset($_GET['password_token'])) {

    if (Database::runQuery("SELECT password_token FROM tbl_password_tokens WHERE password_token=:token", array(':token' => $_GET['password_token']))) {
        $isTokenValid = True;

        if (isset($_POST['changepassword'])) {
         if (strlen($_POST['new_password']) >= 6 && strlen($_POST['new_password']) <= 60) {
           if ($_POST['new_password'] == $_POST['repeat_password']) {
            $userid = Database::runQuery("SELECT user_id FROM tbl_password_tokens WHERE password_token=:token", array(':token' => $_GET['password_token']))[0]['user_id'];

            Database::runQuery("UPDATE tbl_users SET user_password=:password WHERE user_id=:userid", array(':password' => password_hash($_POST['new_password'], PASSWORD_BCRYPT), ':userid' => $userid));
            Database::runQuery("DELETE FROM tbl_password_tokens WHERE password_token=:token", array(':token' => $_GET['password_token']));
            exit();
            header('location:login.html');
           }else{
            $message = "Password does not match!";
           } 
         }else{
            $message = "Password is out of range.";
         }
        }
    } else {
        $isTokenValid = False;
        header('location:login.html');
    }
    
}else {
    
    if (isset($_POST['changepassword'])) {
     if (strlen($_POST['new_password']) >= 6 && strlen($_POST['new_password']) <= 60) {
       if ($_POST['new_password'] == $_POST['repeat_password']) {
        $userid = Login::isUserLoggedIn();

        Database::runQuery("UPDATE tbl_users SET user_password=:password WHERE user_id=:userid", array(':password' => password_hash($_POST['new_password'], PASSWORD_BCRYPT), ':userid' => $userid));
        header('location:login.html');
        
        exit();
       }else{
        $message = "Password does not match!";
       } 
     }else{
        $message = "Password is out of range.";
     }
    }
}

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/profile.css">
    <title>Settings</title>
</head>
<body>

<form method="post" action="<?=($isTokenValid) ? 'settings.php?password_token='.$_GET['password_token'] : 'settings.php'; ?>" id="login_form" class="form push-down">
        <fieldset class="form__container">
            <legend class="form__container--heading">Change password</legend>
            <?php if (!$isTokenValid): ?>
            <div class="form-group-element">
                <input type="password"
                name="old_password"
                id="password-field"
                class="form__text-field"
                placeholder="Old Password"
                maxlength="60"
                data-message_field="#user_password-message_field">
                <button class="form-group-element__button" id="show-hide-button" data-for="#user_password">&#128065;</button>
            </div>
            <?php endif; ?>
            <div class="form-group-element">
                <input type="password"
                name="new_password"
                id="password-field"
                class="form__text-field"
                placeholder="New Password"
                maxlength="60"
                data-message_field="#user_password-message_field">
                <button class="form-group-element__button" id="show-hide-button" data-for="#user_password">&#128065;</button>
            </div>
            <div class="form-group-element">
                <input type="password"
                name="repeat_password"
                id="password-field"
                class="form__text-field"
                placeholder="Repeat new Password"
                maxlength="60"
                data-message_field="#user_password-message_field">
                <button class="form-group-element__button" id="show-hide-button" data-for="#user_password">&#128065;</button>
            </div>
            <span id="user_password-message_field" class="form__error-message"><?=$message;?></span>
            <input type="submit" value="Change password" id="login-btn" name="changepassword" class="form__button">
            <?php if(!$isTokenValid): ?>
            <small class="form__note"><a href="forgot-password.php">Forgot password?</a></small>            
            <?php endif; ?>
        </fieldset>
    </form>
</body>
</html>