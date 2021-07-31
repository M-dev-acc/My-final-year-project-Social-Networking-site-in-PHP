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
    <?php
    include 'config/Database.php';
    include 'models/Login.php';
    include 'models/Post.php';
    include 'models/Notification.php';
    include 'models/Comment.php';
    ?>
    <title>Explore</title>
</head>
<body>
    <?php include "navigation.php"; ?>
    <?php
    $followerId = Login::isUserLoggedIn();
    
    if ($followerId) {
        if (isset($_POST["follow_user"])) {
            if (!Database::runQuery("SELECT id FROM tbl_followers WHERE user_id = :userid AND follower_id = :followerid", array(":userid" => $userId, ":followerid" => $followerId))) {
                Database::runQuery("INSERT INTO tbl_followers(user_id, follower_id) VALUES (:userid, :followerid)", array(":userid" => $userId, ":followerid" => $followerId));
                Notification::sendNotification("", $followerId, array('follower_id' => $followerId, 'user_id' => $userid));

            }
        } elseif (isset($_POST["unfollow_user"])) {
            if (Database::runQuery("SELECT id FROM tbl_followers WHERE user_id = :userid AND follower_id = :followerid", array(":userid" => $userId, ":followerid" => $followerId))) {
                Database::runQuery("DELETE FROM tbl_followers WHERE follower_id = :followerid AND user_id = :userid", array(":userid" => $userId, ":followerid" => $followerId));
            }
        } elseif (isset($_POST['like_post'])) {
            Post::likePost($_GET['post_id'], $followerId);

        } elseif (isset($_POST['unlike_post'])) {
            Post::likePost($_GET['post_id'], $followerId);
        } elseif (isset($_POST['comment_on_post'])){
            Comment::postComment($_POST['post_comment'], $_GET['post_id'], $followerId);
        }
    }else{
        header('location:login.html');
    }
    ?>
    <?php
    $followingUserPosts = Database::runQuery('SELECT tbl_posts.post_id, tbl_posts.post_image, tbl_posts.post_body, tbl_posts.post_likes, tbl_posts.post_author, tbl_users.`user_name` FROM tbl_users, tbl_posts, tbl_followers
    WHERE tbl_posts.post_author = tbl_followers.user_id
    AND tbl_users.user_id = tbl_posts.post_author
    AND follower_id = :userid
    ORDER BY tbl_posts.post_likes DESC;', array(':userid'=>$followerId));

    foreach ($followingUserPosts as $post):
        $username = Database::runQuery("SELECT user_name FROM tbl_users WHERE user_id=:userid", array(':userid' => $post['post_author']))[0]['user_name'];
    ?>
    <article class='post'>
        <header class='post__header'>
            <img src='' alt='' hight="10" width="10" class='post__header--image'>
            <strong class='post__header--heading'>
                <?=$username; ?>
            </strong>

            <?php if($post['post_author'] == Login::isUserLoggedIn()): ?>
            <button class='menu__btn' data-target="#menu-list<?=$post['post_id']; ?>">&#8942;</button>
            <ul class="menu__list" id="menu-list<?=$post['post_id']; ?>">
                <li><a href="edit-post.php?post_id=<?=$userPost['post_id'];?>">Edit post</a></li>
                <li><button class="menu__btn--link red-text" type="submit" name="deletepost">Delete post</button></li>
                <li>Cancel</li>
            </ul>
            <?php else: 
                if (Database::runQuery("SELECT id FROM tbl_followers WHERE user_id = :userid AND follower_id = :followerid", array(":userid" => $post['post_author'], ":followerid" => Login::isUserLoggedIn()))):
            ?>
            <button class="post__btn--link" type="submit" name="follow_user">Unfollow</button>
            <?php elseif(!Login::isUserLoggedIn()): ?>
            <button class="post__btn--link toggleBtn" id="toggleBtn" data-target="#loginModal">Follow</button>
            <?php else: ?>
            <button class="post__btn--link" type="submit" name="follow_user">Follow</button>
            <?php 
                endif;
            endif; ?>
        </header>
        <main class='post__details'>
            <?php
            if ($post['post_image']):
            ?>
            <figure class='post__content'>
                <img src="<?=$post['post_image']; ?>" alt="<?=$username; ?>'s post image." lazyload class="post__image">
                <figcaption class='post__caption'>
                    <pre><?=Post::addLink($post['post_body']); ?></pre>
                </figcaption>
            </figure>
            <?php else: ?>
            <blockquote class="post__text">
                <pre><?=Post::addLink($post['post_body']); ?></pre>
            </blockquote>
            <?php 
            endif;
            ?>
            <small class='post_like'><?=$post['post_likes'];?> Likes</small>
        </main>
        <footer class='post__footer'>
            <form method="post" action="post.php?post_id=<?=$post['post_id']; ?>" class="post__form">
                <?php 
                if (Database::runQuery("SELECT id FROM tbl_post_likes WHERE post_id = :postid AND post_liked_by = :userid", array(":postid" => $post['post_id'], ":userid" => Login::isUserLoggedIn()))):
                ?>
                <button class="post__btn" type="submit" name="unlike_post">&#10084;&#65039;</button>
                <?php else: ?>
                <button class="post__btn" type="submit" name="like_post">&#129293;</button>
                <?php
                endif; ?>
                <textarea rows="2" class="post__comment-box" placeholder="Write a comment." name="post_comment"></textarea>
                <button type="submit" name="comment_on_post" class="post__btn">&#128172;</button>
            </form>
            <?php 
            if (Comment::displayComments($post['post_id'], 0)):
            ?>
            <ul class="post__comment-section">
                <li><a href="#" class="post__link bottom-space">View all <?=count(Comment::displayComments($post['post_id'], 0));?> comments.</a></li>
                <?php
                $recentComments = Comment::displayComments($post['post_id'], 2);
                foreach ($recentComments as $comment):
                ?>
                <li>
                    <a href="profile.php?username=<?=$comment['user']?>" class="post__link"><?=$comment['user'];?></a>
                    <span class="post-text"><?=$comment['comment']?></span>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </footer>
    </article>
    <?php endforeach; ?>
</body>
</html>