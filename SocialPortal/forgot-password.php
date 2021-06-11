<?php
include 'config/Database.php';
include 'models/Login.php';
include 'models/Mail.php';
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
	<title>Forgot password</title>
</head>
<body>
<?php
$message = "";
if (isset($_POST['verifyemail'])) {
	if (Database::runQuery("SELECT user_email FROM tbl_users WHERE user_email=:useremail", array(':useremail' => $_POST['user_email']))) {
		$cstrong = true;
        $token   = bin2hex(openssl_random_pseudo_bytes(64, $cstrong));
        $userid = Database::runQuery("SELECT user_id FROM tbl_users WHERE user_email=:useremail", array(':useremail' => $_POST['user_email']))[0]['user_id'];

        Database::runQuery("INSERT INTO tbl_password_tokens(password_token, user_id) VALUES(:token, :userid)", array(':token' => md5($token), ':userid' => $userid));
        Mail::sendMail('Change your password.', 'Click to reset you password <a href="http://localhost:7723/SocialPortal/settings.php?password_token=${token}">http://localhost:7723/SocialPortal/settings.php?password_token=${token}</a>', $_POST['user_email']);
	}else{
		$message = "Incorrect Email";
	}
}
?>
    <form method="post" id="login_form" class="form push-down">
        
        <fieldset class="form__container">
            <legend class="form__container--heading">Verify your Email</legend>
            <input type="text"
                name="user_email" 
                id="user_email" 
                class="form__text-field" 
                placeholder="Email."
                data-message_field="#user_id-message_field">
            <small class="form__note">We will automatically send email with a link to reset password.</small>            

            <span id="user_id-message_field" class="form__error-message"><?=$message;?></span>
            <input type="submit" value="Verify email" id="login-btn" name="verifyemail" class="form__button"> 
        </fieldset>
    </form>
</body>
</html>