<?php 
include 'config/Database.php';
include 'models/Login.php';
include 'models/Post.php';
include 'models/Notification.php';
include 'models/Comment.php';

$followerId = Login::isUserLoggedIn();
if (isset($_GET['post_id'])) {
    if (Database::runQuery("SELECT post_id FROM tbl_posts WHERE post_id=:postid", array(":postid" => $_GET['post_id']))) {
        $userId = Database::runQuery("SELECT post_author FROM tbl_posts WHERE post_id=:postid", array(":postid" => $_GET['post_id']))[0]['post_author'];
    }
}elseif (isset($_GET['user_id'])) {
    $userId = Database::runQuery("SELECT user_id FROM tbl_users WHERE user_id=:userid", array(":userid" => $_GET['user_id']))[0]['user_id'];
}

if (Login::isUserLoggedIn()) {
    if (isset($_POST["follow_user"])) {
        if (!Database::runQuery("SELECT id FROM tbl_followers WHERE user_id = :userid AND follower_id = :followerid", array(":userid" => $userId, ":followerid" => $followerId))) {
            Database::runQuery("INSERT INTO tbl_followers(user_id, follower_id) VALUES (:userid, :followerid)", array(":userid" => $userId, ":followerid" => $followerId));
            Notification::sendNotification("", $followerId, array('follower_id' => $followerId, 'user_id' => $userid));

        }
    } elseif (isset($_POST["unfollow_user"])) {
        if (Database::runQuery("SELECT id FROM tbl_followers WHERE user_id = :userid AND follower_id = :followerid", array(":userid" => $userId, ":followerid" => $followerId))) {
            Database::runQuery("DELETE FROM tbl_followers WHERE follower_id = :followerid AND user_id = :userid", array(":userid" => $userId, ":followerid" => $followerId));
        }
    } elseif (isset($_POST['deletepost'])) {
        if (Database::runQuery("SELECT post_id FROM tbl_posts WHERE post_id=:postid AND post_author=:postauthor", array(':postid' => $_GET['post_id'], ':postauthor' => $followerId))) {
            Database::runQuery("DELETE FROM tbl_posts WHERE post_id=:postid", array(":postid" => $_GET['post_id']));
            Database::runQuery("DELETE FROM tbl_post_likes WHERE post_id=:postid", array(":postid" => $_GET['post_id']));
            header('location: post.php?user_id='.$followerId);
        }
    } elseif (isset($_POST['like_post'])) {
        Post::likePost($_GET['post_id'], $followerId);

    } elseif (isset($_POST['unlike_post'])) {
        Post::likePost($_GET['post_id'], $followerId);
    } elseif (isset($_POST['comment_on_post'])){
        Comment::postComment($_POST['post_comment'], $_GET['post_id'], $followerId);
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
    <link rel="stylesheet" type="text/css" href="css/navigation.css">
    <link rel="stylesheet" type="text/css" href="css/post.css">
    <title>
        Post
    </title>
</head>
<body>
    <?php include 'navigation.php'; ?>
    
    <?php 
    if (isset($_GET['post_id'])) :
        $post = Post::displaySinglePost($_GET['post_id']);
        if ($post):
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

    <?php
        else:
    ?>
        <article class="post"><h2>Post is not available.</h2></article>
    <?php
        endif;

    elseif(isset($_GET['user_id'])):
        $userPostsArray = Post::allPostsOfUser($_GET['user_id']);
        if ($userPostsArray):
        $username = Database::runQuery("SELECT user_name FROM tbl_users WHERE user_id=:userid", array(':userid' => $_GET['user_id']))[0]['user_name'];
        foreach ($userPostsArray as $post):
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
                <form method="post" action="post.php?post_id=<?=$post['post_id']; ?>">
                <li><a href="edit-post.php?post_id=<?=$post['post_id'];?>">Edit post</a></li>
                <li><button class="menu__btn--link red-text" type="submit" name="deletepost">Delete post</button></li>
                <li>Cancel</li>
                </form>
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
    <?php 
        endforeach;

        else:
    ?>
        <article class="post"><h2>User hasn't posted any posts yet <br>or<br> User is not available.</h2></article>
    <?php
        endif;

    endif;
    ?>
    </main>

    <div class="modal" id="loginModal">
        <h2>Sign in to your account.</h2>
        <h4>You need to sign in. Sign in and start exploring.</h4>
        <footer>
            <a href="login.html" class="btn blue">Log in</a>
            <button class="btn link-btn" id="closeModal">Cancel</button>
        </footer>
    </div>
</body>
</html>
<script>
    const btnList = document.querySelectorAll('#toggleBtn');
    const clsBtnList = document.querySelectorAll('#closeModal');
    Array.from(btnList).forEach(button => {
      button.addEventListener('click', function(e) {
        e.preventDefault();

        const modalName = button.dataset.target;
        const modal = document.querySelector(modalName);

        modal.style.display = "flex";
      });
    });
    Array.from(clsBtnList).forEach(closeBtn => {
      closeBtn.addEventListener('click', function(){
        this.closest('.modal').style.display = "none";
      });
    });

    const menuBtnList = document.querySelectorAll('.menu__btn');
    Array.from(menuBtnList).forEach(menuBtn => {

        menuBtn.addEventListener('click', (event) => {
            event.preventDefault();
            if (!document.querySelector(menuBtn.dataset.target).classList.contains('show')) {
                document.querySelector(menuBtn.dataset.target).classList.add('show')                
            }else{
                document.querySelector(menuBtn.dataset.target).classList.remove('show')                
            }
        })
               
    });
    if ( window.history.replaceState ) {
        window.history.replaceState( null, null, window.location.href );
    }
</script>