<?php
class Post
{
    public static function createPost($postBody, $loggedUserId, $profileUserId)
    {
        if ($loggedUserId === $profileUserId) {
            Database::runQuery('INSERT INTO tbl_posts(post_body, post_created_at, post_author, post_likes) VALUES (:postbody, NOW(), :userid, 0)', array(':postbody' => $postBody, ':userid' => $profileUserId));
        } else {
            die("Incorrect User.");
        }
    }
    public static function createImagePost($postbody, $loggedUserId, $profileUserId)
    {
        if ($loggedUserId === $profileUserId) {
            Database::runQuery('INSERT INTO tbl_posts(post_body, post_created_at, post_author, post_likes) VALUES (:postbody, NOW(), :userid, 0)', array(':postbody' => $postbody, ':userid' => $profileUserId));
            $postid = Database::runQuery("SELECT post_id FROM tbl_posts WHERE post_author = :userid ORDER BY post_author LIMIT 1", array(":userid" => $loggedUserId))[0]["post_id"];
            return $postid;
        } else {
            die("Incorrect User.");
        }
    }
    public static function displayPost(Int $userid, String $username, Int $loggedInUserId): String
    {
        $DatabasePosts = Database::runQuery("SELECT * FROM tbl_posts WHERE post_author=:userid ORDER BY post_id DESC", array(":userid" => $userid));
        $posts         = "";
        foreach ($DatabasePosts as $post) {
            if (!Database::runQuery("SELECT post_id FROM tbl_post_likes WHERE post_id=:postid AND user_id=:userid", array(":postid" => $post["post_id"], ":userid" => $loggedInUserId))) {
                $posts .= "<article class='post'>
                            <header class='post__header'>
                                <img src='' alt='' class='post__header--image'>
                                <h4 class='post__header--heading'>${username}</h4>
                            </header>
                            <main class='post__details'>
                                <figure class='post__content'>";
                if ($post['post_image']) {
                    $posts .= "<img src='${post['post_image']}' alt='' lazyload class='post__image'>";
                }
                $posts .= "<figcaption class='post__caption'>
                                        ${post['post_body']}
                                    </figcaption>
                                </figure>
                                <strong class='post_like'>${post['post_likes']} Likes</strong>
                            </main>
                            <footer class='post__footer'>
                            <form action='${_SERVER['PHP_SELF']}?username=${username}&post_id=${post['post_id']}' method='post'>
                                <input type='submit' name='editpost' value='&#129293;' class='post__btn'>
                                <input type='submit' name='deletepost' value='&#10060;' class='post__btn'>
                            </form>
                            </footer>
                        </article>";
            } else {
                $posts .= "<article class='post'>
                            <header class='post__header'>
                                <img src='' alt='' class='post__header--image'>
                                <h4 class='post__header--heading'>${username}</h4>
                            </header>
                            <main class='post__details'>
                                <figure class='post__content'>";
                if ($post['post_image']) {
                    $posts .= "<img src='${post['post_image']}' alt='' lazyload class='post__image'>";
                }
                $posts .= "<figcaption class='post__caption'>
                                        ${post['post_body']}
                                    </figcaption>
                                </figure>
                            </main>
                            <strong class='post_like'>${post['post_likes']} Likes</strong>
                            <footer class='post__footer'>
                                <form action='profile.php?username=${username}&post_id=${post['post_id']}' method='post'>
                                    <input type='submit' name='editpost' value='&#10084;&#65039;' class='post__btn'>
                                    <input type='submit' name='deletepost' value='&#10060;' class='post__btn'>
                                </form>
                            </footer>
                        </article>";
            }

        }
        return $posts;
    }
    public static function displayPostGrid(Int $userid, String $username, Int $loggedInUserId): String
    {
        $DatabasePosts = Database::runQuery("SELECT post_id, post_body, post_image FROM tbl_posts WHERE post_author=:userid ORDER BY post_id DESC", array(":userid" => $userid));
        $posts         = "";
        foreach ($DatabasePosts as $post) {
            if ($post["post_image"]) {
                $posts .= "<div class='user_posts__card'>
                           <img src='${post['post_image']}' alt='' class='user_posts__card--img'>                            
                        </div>";
            } else {
                $posts .= "<div class='user_posts__card border'>
                            <main class='user__post-box--body relative'>
                                ${post['post_body']}
                            </main>
                        </div>";
            }

        }
        return $posts;
    }
}
