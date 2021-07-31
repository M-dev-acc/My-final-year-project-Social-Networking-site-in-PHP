<?php
include 'config/Database.php';
include 'models/Login.php';
include 'models/Post.php';
include 'models/Notification.php';

$post_count      = "0";
$follower_count  = "0";
$following_count = "0";
$isFollowing     = false;

if (isset($_GET["username"])) {
    if (Database::runQuery("SELECT user_name FROM tbl_users WHERE user_name = :username", array(":username" => $_GET["username"]))) {
        $username        = Database::runQuery("SELECT user_name FROM tbl_users WHERE user_name = :username", array(":username" => $_GET['username']))[0]["user_name"];
        $userid          = Database::runQuery("SELECT user_id FROM tbl_users WHERE user_name = :username", array(":username" => $_GET['username']))[0]["user_id"];
        $profileImage          = Database::runQuery("SELECT profile_image FROM tbl_users WHERE user_name = :username", array(":username" => $_GET['username']))[0]['profile_image'];
        $userAbout          = Database::runQuery("SELECT user_about FROM tbl_users WHERE user_name = :username", array(":username" => $_GET['username']))[0]['user_about'];        
        $post_count      = Database::runQuery("SELECT COUNT(post_author) AS post_count FROM tbl_posts WHERE post_author=:userid", array(":userid" => $userid))[0]["post_count"];
        $follower_count  = Database::runQuery("SELECT COUNT(user_id) AS follower_count FROM tbl_followers WHERE user_id=:userid", array(":userid" => $userid))[0]["follower_count"];
        $following_count = Database::runQuery("SELECT COUNT(follower_id) AS following_count FROM tbl_followers WHERE follower_id=:userid", array(":userid" => $userid))[0]["following_count"];
        $followerId      = Login::isUserLoggedIn();
        $posts           = Post::displayPostGrid($userid, $username, $followerId);

        if ($username) {
            if ($userid != $followerId) {
                if (isset($_POST["follow"])) {
                    if (!Database::runQuery("SELECT id FROM tbl_followers WHERE user_id = :userid AND follower_id = :followerid", array(":userid" => $userid, ":followerid" => $followerId))) {
                        Database::runQuery("INSERT INTO tbl_followers(user_id, follower_id) VALUES (:userid, :followerid)", array(":userid" => $userid, ":followerid" => $followerId));
                        Notification::sendNotification("", $followerId, array('follower_id' => $followerId, 'post_id' => 0, 'user_id' => $userid));
                    }
                    $isFollowing = true;
                } elseif (isset($_POST["unfollow"])) {
                    if (Database::runQuery("SELECT id FROM tbl_followers WHERE user_id = :userid AND follower_id = :followerid", array(":userid" => $userid, ":followerid" => $followerId))) {
                        Database::runQuery("DELETE FROM tbl_followers WHERE follower_id = :followerid AND user_id = :userid", array(":userid" => $userid, ":followerid" => $followerId));
                    }
                    $isFollowing = false;
                } elseif (Database::runQuery("SELECT id FROM tbl_followers WHERE user_id = :userid AND follower_id = :followerid", array(":userid" => $userid, ":followerid" => $followerId))) {
                    $isFollowing = true;
                }
            }
            if (isset($_POST["deletepost"])) {
                if (Database::runQuery("DELETE FROM tbl_posts WHERE post_id=:postid", array(':postid' => $_GET['post_id']))) {
                    Database::runQuery("DELETE FROM tbl_posts WHERE post_id=:postid", array(':userid' => $_GET['post_id']));
                    Database::runQuery("DELETE FROM tbl_posts_likes WHERE post_id=:postid", array(':userid' => $_GET['post_id']));
                    header("location:profile.php?username=${username}");
                }
            }
        } else {
            $username        = "User not found";
            $post_count      = "0";
            $follower_count  = "0";
            $following_count = "0";
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
    <title>@<?=$username;?></title>
</head>
<body>
    
    <?php include 'navigation.php'?>
    <section class="user_panel" style="">
        <img src="<?=$profileImage;?>" alt="" class="user_panel__image" style=" ">

        <article class="user_panel__details">
            <header class="user_panel__heading">
            <form method="post">
                <h2 class="user_panel__heading--text"><?=$username?>
                <?php if ($userid != $followerId): ?>
                    <?php if ($isFollowing): ?>
                    <input type="submit" value="Unfollow" name="unfollow" class="btn">
                    <a href="messages.php?reciever=<?=$userid; ?>" class="user_panel__status--link border-btn"><small>Message</small></a>
                    <?php elseif (!$followerId): ?>
                    <button class="toggleBtn btn blue" id="toggleBtn" data-target="#loginModal">Follow</button>
                    <?php else: ?>
                    <input type="submit" value="Follow" name="follow" class="btn blue">
                    <a href="messages.php?reciever=<?=$userid; ?>" class="user_panel__status--link border-btn"><small>Message</small></a>
                    <?php endif;?>
                <?php endif;?>
                </h2>
            </form>
            </header>
            <p class="user_panel__status">
                <a href="post.php?user_id=<?=$userid; ?>" class="user_panel__status--link"><strong><?=($post_count) ? $post_count : "0";?></strong> Posts</a>
                <a href="users.php?user_id=<?=$userid; ?>&users_list=followers" class="user_panel__status--link"><strong><?=($follower_count) ? $follower_count : "0";?></strong> Followers</a>
                <a href="users.php?user_id=<?=$userid; ?>&users_list=following" class="user_panel__status--link"><strong><?=($following_count) ? $following_count : "0";?></strong> Following</a>
            </p>
            <div class="user_panel__about">
                <pre><?=$userAbout;?></pre>
            </div>
        </article>
    </section>
    <section class="user_posts">
            <header class="user_posts__header">
                <button class="user_posts__header--btn" id="tabs_btn" data-tabs_id="#text-post_tab">Text</button>
                <button class="user_posts__header--btn" id="tabs_btn" data-tabs_id="#image-post_tab">Images</button>
                <button class="user_posts__header--btn" id="tabs_btn" data-tabs_id="#all-post_tab">All Posts</button>
            </header>
            <main class="user_posts__container">
                <div class="user_posts__container--tabs" id="#all-post_tab">
                    <?=htmlspecialchars_decode($posts);?>
                </div>
                <div class="user_posts__container--tabs" id="#image-post_tab">
                    <?php
                    foreach (Post::allPostsOfUser($userid) as $postData) :
                        extract($postData);

                        if ($post_image):
                    ?>
                    <div class="user_posts__card" data-post_id="<?=$post_id; ?>">
                        <img src="<?=$post_image; ?>" alt="post_img" class="user_posts__card--img" >
                    </div>
                    <?php
                        endif;
                    endforeach; ?>
                </div>
                <div class="user_posts__container--tabs" id="#text-post_tab">
                    <?php
                    foreach (Post::allPostsOfUser($userid) as $postData) :
                        extract($postData);

                        if (!$post_image):
                    ?>
                    <div class="user_posts__card" data-post_id="<?=$post_id; ?>">
                        <blockquote class="text-post">                            
                        <?=(strlen($post_body) > 160) ? substr($post_body, 0, 160)."..." : $post_body; ?>
                        </blockquote>
                    </div>
                    <?php
                        endif;
                    endforeach; ?>
                    <div class="user_posts__card">
                        
                    </div>
                </div>
            </main>
    </section>
    <div class="modal" id="loginModal">
        <h2>Sign in to your account.</h2>
        <h4>You need to sign in. Sign in and start exploring.</h4>
        <footer>
            <a href="login.html" class="btn blue">Log in</a>
            <button class="btn link-btn" id="closeModal">Cancel</button>
        </footer>
    </div>
    <div class="modal" id="editPostModal">
        <form action="" method="post">
        <h2>Edit Post</h2>
        <footer>
            <input type="submit" name="updatepost" class="btn blue">
        </form>
            <button class="btn link-btn" id="closeModal">Cancel</button>
        </footer>
    </div>
</body>
</html>
<?php
    } else {
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
    <title>User not found</title>
</head>
<body>    
    <?php include 'navigation.php'?>
    <article class="post"><h2> User is not available.</h2></article>
</body>
</html>
<?php
        
        }
    }
?>
<script>
const tabsButton = document.querySelectorAll('.user_posts__header .user_posts__header--btn');
const btnList = document.querySelectorAll('#toggleBtn');
const clsBtnList = document.querySelectorAll('#closeModal');
const activeTab = ()=>{
    tabsButton.forEach((button => {
        button.addEventListener('click', () => {
            const tabReferenceId = button.dataset.tabs_id;
            const tabToActive = document.getElementById(tabReferenceId);

            document.querySelectorAll('.user_posts__header--btn').forEach((button) => {
                button.classList.remove('btn-active');
            });
            document.querySelectorAll('.user_posts__container--tabs').forEach((tab) => {
                tab.classList.remove('tab-active');
            });
            tabToActive.classList.add('tab-active');
            button.classList.add('btn-active');
        });
    }))
};

const createRedirect = () => {
    const posts = document.querySelectorAll('.user_posts__container--tabs .user_posts__card ');

    Array.from(posts).forEach(post => {
        post.addEventListener('click', () => {
            document.location = `post.php?post_id=${post.dataset.post_id}`;
        });
    });
    
};

document.addEventListener('DOMContentLoaded', ()=>{
    createRedirect();
    activeTab();
    const tabContainer =  document.querySelectorAll('.user_posts__header');
    tabContainer.forEach((container) => {
        container.querySelectorAll('.user_posts__header--btn').forEach((button)=>{
            button.click();
        });
    });
    buttonList.dataset.target.style.display = "none";
});

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
if ( window.history.replaceState ) {
    window.history.replaceState( null, null, window.location.href );
}
</script>