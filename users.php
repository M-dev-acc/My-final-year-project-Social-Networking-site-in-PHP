
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/profile.css">
    <link rel="stylesheet" href="css/navigation.css">
    <?php 
	include 'config/Database.php';
	include 'models/Login.php';
	include 'models/Notification.php';
	?>
	<title>
	<?php
	if (isset($_GET['user_id'])) {
		$username = Database::runQuery("SELECT user_name FROM tbl_users WHERE user_id=:userid", array(":userid" => $_GET['user_id']))[0]['user_name'];
		echo "@" . $username . "'s followers";		
	}	
	?>
	</title>
</head>
<body>
	<?php
	include 'navigation.php';

	if (Login::isUserLoggedIn()) {
		$isFollowing = false;
		
		if (isset($_POST["follow_user"])) {
			// echo $_POST['follower_id'];
			if (!Database::runQuery("SELECT id FROM tbl_followers WHERE user_id = :userid AND follower_id = :followerid", array(":userid" => $_POST['follower_id'], ":followerid" => Login::isUserLoggedIn()))) {
                Database::runQuery("INSERT INTO tbl_followers(user_id, follower_id) VALUES (:userid, :followerid)", array(":userid" => $_POST['follower_id'], ":followerid" => Login::isUserLoggedIn()));
                Notification::sendNotification("", Login::isUserLoggedIn(), array('follower_id' => Login::isUserLoggedIn(), 'post_id' => 0, 'user_id' => $_POST['follower_id']));
            }
            $isFollowing = true;
		} elseif (isset($_POST['unfollow_user'])) {
			if (Database::runQuery("SELECT id FROM tbl_followers WHERE user_id = :userid AND follower_id = :followerid", array(":userid" => $_POST['follower_id'], ":followerid" => Login::isUserLoggedIn()))) {
                Database::runQuery("DELETE FROM tbl_followers WHERE follower_id = :followerid AND user_id = :userid", array(":userid" => $_POST['follower_id'], ":followerid" => Login::isUserLoggedIn()));
            }
            $isFollowing = false;
		}

	} else{
		header('location: login.html');
	}
 
	//script for follow user
	?>
	<?php if (isset($_GET['users_list'])): 
		if ($_GET['users_list'] === "followers"): ?>
		<section class="users-section">
			<h2 class="users-section__heading"><?=$username; ?>'s followers</h2>
			<?php
			$following = Database::runQuery("SELECT tbl_followers.follower_id AS user_id, tbl_users.user_name AS username, tbl_users.profile_image FROM `tbl_followers`, tbl_users WHERE tbl_followers.user_id=:user_id AND tbl_users.user_id=tbl_followers.follower_id", array(":user_id" => $_GET['user_id']));
			foreach ($following as $follower):
				extract($follower);
			?>
	        <section class="users-section">
		        <div class="user-div">
		        <form method="post" action="users.php?user_id=<?=$_GET['user_id']?>&users_list=followers">
		           <a href="profile.php?username=<?=$username; ?>" class="user-div__link">
		           	<img src="<?=$profile_image; ?>" alt="<?=$username; ?>'s profile image" class="user-div__image">
		           	<strong class="username"><?=$username; ?></strong>
		           </a>
		           <input type="hidden" name="follower_id" value="<?=$user_id; ?>">
		           <?php if ($isFollowing): ?>
		           <input type="submit" name="unfollow_user" value="Unfollow" class="follow-btn">
		           <?php elseif(!$isFollowing): ?> 
		           <input type="submit" name="follow_user" value="Follow" class="follow-btn"> 
		       	   <?php endif; ?>
		        </form>            
		        </div>         
		    </section>
         <?php endforeach; ?>        
	   </section>
	 	<?php elseif ($_GET['users_list'] === "following"): ?>
 		<section class="users-section">
			<h2><?=$username; ?> following</h2>
			<?php
			$following = Database::runQuery("SELECT tbl_followers.user_id, tbl_users.user_name AS username, tbl_users.profile_image FROM `tbl_followers`, tbl_users WHERE tbl_followers.follower_id=:user_id AND tbl_users.user_id=tbl_followers.user_id", array(":user_id" => $_GET['user_id']));
			foreach ($following as $follower):
				extract($follower);
			?>
	        <!-- <?=$user_name . " " . $user_id; ?> -->
	        <section class="users-section">
		        <div class="user-div">
		        <form method="post" action="users.php?user_id=<?=$_GET['user_id']?>&users_list=followers">
		           <a href="profile.php?username=<?=$username; ?>" class="user-div__link">
		           	<img src="<?=$profile_image; ?>" alt="<?=$username; ?>'s profile image" class="user-div__image">
		           	<strong class="username"><?=$username; ?></strong>
		           </a>
		           <input type="hidden" name="follower_id" value="<?=$user_id; ?>">
		           <?php if ($isFollowing): ?>
		           <input type="submit" name="unfollow_user" value="Unfollow" class="follow-btn">
		           <?php elseif(!$isFollowing): ?> 
		           <input type="submit" name="follow_user" value="Follow" class="follow-btn"> 
		       	   <?php endif; ?>
		        </form>            
		        </div>         
		    </section>
	        <?php endforeach; ?>        
    	</section>
		<?php endif; ?>
	<?php endif; ?>
</body>
</html>