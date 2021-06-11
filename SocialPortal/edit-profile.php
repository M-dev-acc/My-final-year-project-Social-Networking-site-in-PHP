<?php 
include 'config/Database.php';
include 'models/Login.php';
include 'models/Image.php';
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
	<title>Edit profile</title>	
</head>
<body>
	<?php
	include 'navigation.php';

	$userData = '';
	$message = '';
	if (Login::isUserLoggedIn()) {
    	$userData = Database::runQuery("SELECT user_id, user_name, profile_image, user_email, user_about FROM tbl_users WHERE user_id = :userid", array(":userid" => Login::isUserLoggedIn()))[0];
		if (isset($_POST['update_profile_picture']) && $_FILES['profileimg']['size'] > 0) {
        	Image::uploadImage('profileimg', "UPDATE tbl_users SET profile_image = :profileimg WHERE user_id=:userid", array(':userid'=>$userData['user_id']));
		}elseif (isset($_POST['updateuserinfo'])) {
			Database::runQuery("UPDATE tbl_users SET user_name=:username, user_email=:useremail, user_about=:userabout WHERE user_id=:userid ", array(":userid" => $userData['user_id'],
				':username' => $_POST['user_name'],
				':useremail' => $_POST['email'],
				':userabout' => $_POST['about']));
			header('location:edit-profile.php');
		}
	}else{
		die("User is not logged in.");
	}
	// print_r($_FILES);
	?>

	<form method="post" enctype="multipart/form-data" class="form push-down">
		<fieldset class="form__container">
            <legend class="form__container--heading">Update profile picture</legend>
	        <img src="<?=$userData['profile_image'];?>" alt="profile_image" class="user_panel__image">
	        <hr>
			<input type="file" name="profileimg">
			<input type="submit" name="update_profile_picture" value="Update profile picture" class="btn blue">
		</fieldset>
	</form>
	<form method="post" id="form" class="form push-down wide">
        <fieldset class="form__container">
            <legend class="form__container--heading">Update profile info</legend>
            <label>Username</label>
            <input type="text"
                name="user_name" 
                id="user_name"
                value="<?=$userData['user_name'];?>" 
                class="form__text-field"
                maxlength="40"
                data-message_field="#username-message_field">
            <span id="username-message_field" class="form__error-message"></span>
            <label>Email</label>            
            <input type="text"
                name="email"
                id="email"
                value="<?=$userData['user_email'];?>"
                class="form__text-field"
                data-message_field="#email-message_field"> 
            <span id="email-message_field" class="form__error-message"></span>
            <label>About</label>
            <textarea
                name="about"
                id="about"
                class="form__text-field"
                data-message_field="#about-message_field">
        		<?=$userData['user_about'];?>
                </textarea>
            <span id="about-message_field" class="form__error-message"></span>                
            <input type="submit" value="Update info" id="register" name="updateuserinfo" class="form__button">
        </fieldset>
    </form>
</body>
</html>