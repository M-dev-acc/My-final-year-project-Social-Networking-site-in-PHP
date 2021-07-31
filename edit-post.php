<?php 
include 'config/Database.php';
include 'models/Login.php';
include 'models/Post.php';
include 'models/Image.php';
include 'models/Notification.php';

if (isset($_POST['edit_post'])) {
	if ($_FILES['newpostimage']['size'] > 0) {
        $hashtags = Post::getHashtags($_POST['post_body']);

		Image::uploadImage('newpostimage', "UPDATE tbl_posts SET post_image = :newpostimage WHERE post_id=:postid AND post_author=:userid", array(':postid'=>$_GET['post_id'], ":userid" => Login::isUserLoggedIn()));
		Database::runQuery("UPDATE tbl_posts SET post_body=:postbody, post_topics=:hashtags WHERE post_id=:postid AND post_author=:userid", array(":postid" => $_GET['post_id'], ":userid" => Login::isUserLoggedIn(), ':postbody' => $_POST['post_body'], ':hashtags' => $hashtags));
		header('location:edit-post.php?post_id='.$_GET['post_id']);
	}else{
        $hashtags = Post::getHashtags($_POST['post_body']);

		Database::runQuery("UPDATE tbl_posts SET post_body=:postbody, post_topics=:hashtags WHERE post_id=:postid AND post_author=:userid", array(":postid" => $_GET['post_id'], ":postbody" => $_POST['post_body'], ":userid" => Login::isUserLoggedIn(), ':hashtags' => $hashtags));
		header('location:edit-post.php?post_id='.$_GET['post_id']);
	}
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/profile.css">
    <link rel="stylesheet" href="css/navigation.css">
    <link rel="stylesheet" href="css/post.css">
    <title>Edit post</title>
</head>
<body>
<?php 
include 'navigation.php';
if (Login::isUserLoggedIn()):
if (isset($_GET['post_id']) ):
	if (Database::runQuery("SELECT post_id FROM tbl_posts WHERE post_id=:postid", array(':postid' => $_GET['post_id']))):
		$post = Post::displaySinglePost($_GET['post_id']);
?> 
    <article class='post'>
        <form method="post" action="edit-post.php?post_id=<?=$post['post_id']; ?>" enctype="multipart/form-data">
        <header class='post__header'>
            <h4 class='post__header--heading'>
            	<?=Database::runQuery("SELECT user_name FROM tbl_users WHERE user_id=:userid", array(':userid' => $post['post_author']))[0]['user_name']; ?>
            </h4>
        </header>
        <main class='post__details'>
            <figure class='post__content'>
                <?php if($post['post_image']): ?>
                <img src="<?=$post['post_image'];?>" alt='' lazyload class='post__image'>
                <input type="file" name="newpostimage" accept="image/*">
                <?php endif; ?>
                <figcaption class='post__caption'>
                    <textarea name="post_body" rows="1" cols="64" class="post-caption-box"><?=$post['post_body'];?></textarea>
                </figcaption>
            </figure>
        </main>
        <footer class='post__footer'>
        	<input type="submit" value="Edit post" id="edit-btn" name="edit_post" class="form__button">
        </footer>       
        </form>
    </article>
	<?php else: ?>
	<article class='post'><h2>Post is not exists.</h2></article>
	<?php endif; ?>
<?php endif; ?>
<?php else: ?>
	<article class='post'><h2>User is not logged in.</h2></article>
<?php endif; ?>
</body>
</html>