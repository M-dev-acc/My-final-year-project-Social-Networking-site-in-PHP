<?php
include "config/Database.php";
include "models/Login.php";
include "models/Post.php";
include "models/Image.php";
include "models/Notification.php";

$message = "";
if (isset($_GET["username"])) {
    $profileUserId = Database::runQuery("SELECT user_id FROM tbl_users WHERE user_name = :username", array(":username" => $_GET["username"]))[0]["user_id"];

    if (isset($_POST["post"])) {
        if ($_FILES["postimage"]["size"] === 0) {
            if (strlen($_POST["postcaption"]) > 300 || strlen($_POST["postcaption"]) < 1) {
                $message = "Incorrect length. Your content should be more than 0 or less than 300 words.";
            } else {
                Post::createPost($_POST["postcaption"], Login::isUserLoggedIn(), $profileUserId);
                $message = "Your Post succesfully created!";
            }
        } else {
            if (strlen($_POST["postcaption"]) > 300 || strlen($_POST["postcaption"]) < 1) {
                $message = "Incorrect length. Your content should be more than 0 or less than 300 words.";
            } elseif ($_FILES["postimage"]["size"] > 10240000) {
                $message = "Image should be less tham 10MB.";
            } else {
                $postid = Post::createImagePost($_POST["postcaption"], Login::isUserLoggedIn(), $profileUserId);
                Image::uploadImage("postimage", "UPDATE tbl_posts SET post_image=:postimage WHERE post_id=:postid", array(":postid" => $postid));
                $message = "Your Post succesfully created!";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/profile.css">
    <link rel="stylesheet" href="css/navigation.css">
    <title>Write a post</title>
</head>
<body>
<?php include 'navigation.php'?>
    <form method="post" class="form push-down" enctype="multipart/form-data">
        <fieldset class="form__container">
            <legend class="form__container--heading">Share what you like.</legend>
            <label for="postImage">Choose a image.</label>
            <input type="file"
                name="postimage"
                id="postImage"
                class="form-group-element">
            <textarea name="postcaption"
                id=""
                cols="60"
                rows="10"
                minlength="1"
                maxlength="160"
                placeholder="Write a caption"
                class="form__text-field"></textarea>
            <input type="submit" value="Post" name="post" class="btn blue">
        </fieldset>
    </form>
    <?php if ($message): ?>
    <div class="alert-box">
        <p><?=$message;?></p><button class="closeAlert">X</button>
    </div>
    <?php endif;?>
</body>
</html>
<script>
    let closeAlertBtn = document.querySelector(".closeAlert");
    closeAlertBtn.addEventListener("click", () => {
        document.querySelector(".alert-box").style.display = "none";
    });
    if ( window.history.replaceState ) {
        window.history.replaceState( null, null, window.location.href );
    }
</script>