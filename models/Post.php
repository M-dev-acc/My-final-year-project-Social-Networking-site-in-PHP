<?php
class Post
{
    public static function createPost($postBody, $loggedUserId, $profileUserId)
    {
        if ($loggedUserId === $profileUserId) {
            $hashtags = self::getHashtags($postBody);

            Database::runQuery('INSERT INTO tbl_posts(post_body, post_created_at, post_author, post_likes, post_topics) VALUES (:postbody, NOW(), :userid, 0, :hashtags)', array(':postbody' => $postBody, ':userid' => $profileUserId, ':hashtags' => $hashtags));
            $postid = Database::runQuery("SELECT post_id FROM tbl_posts WHERE post_author = :userid ORDER BY post_author DESC LIMIT 1", array(":userid" => $loggedUserId))[0]["post_id"];
            Notification::sendNotification($postBody, $loggedUserId, array("post_id" => $postid));
            
        } else {
            die("Incorrect User.");
        }
    }
    
    public static function createImagePost($postbody, $loggedUserId, $profileUserId)
    {
        if ($loggedUserId === $profileUserId) {
            $hashtags = self::getHashtags($postbody);

            Database::runQuery('INSERT INTO tbl_posts(post_body, post_created_at, post_author, post_likes, post_topics) VALUES (:postbody, NOW(), :userid, 0, :hashtags)', array(':postbody' => $postbody, ':userid' => $profileUserId, ':hashtags' => $hashtags));
            $postid = Database::runQuery("SELECT post_id FROM tbl_posts WHERE post_author = :userid ORDER BY post_author DESC LIMIT 1", array(":userid" => $loggedUserId))[0]["post_id"];
            Notification::sendNotification($postbody, $loggedUserId, array('post_id' => $postid));

            return $postid;
        } else {
            die("Incorrect User.");
        }
    }

    public static function likePost(Int $postId, Int $postLikedBy)
    {
        if (!Database::runQuery("SELECT post_id FROM tbl_post_likes WHERE post_id=:postid AND post_liked_by=:userid", array(":postid" => $postId, ':userid' => $postLikedBy))) {
            Database::runQuery("INSERT INTO tbl_post_likes(post_id, post_liked_by) VALUES (:postid, :followerid)", array(":postid" => $postId, ":followerid" => $postLikedBy));
            
            Database::runQuery("UPDATE tbl_posts SET post_likes=post_likes+1 WHERE post_id=:postid", array(':postid' => $postId));
            Notification::sendNotification("", 0, array('post_id' => "${postId}"));
        }else{
            Database::runQuery("DELETE FROM tbl_post_likes WHERE post_id=:postid AND post_liked_by=:userid", array(":postid" => $postId, ":userid" => $postLikedBy));
            Database::runQuery("UPDATE tbl_posts SET post_likes=post_likes-1 WHERE post_id=:postid", array(':postid' => $postId));
        }
    }

    public static function displaySinglePost(Int $postId)
    {
        if (Database::runQuery("SELECT post_id FROM tbl_posts WHERE post_id=:postid", array(":postid" => $postId))) {
        
        $postDataArray = Database::runQuery("SELECT post_id, post_body, post_image, post_likes, post_author FROM tbl_posts WHERE post_id=:postid", array(":postid" => $postId))[0];
        
        return $postDataArray;
        }
        return false;
    }

    public static function allPostsOfUser(Int $userId)
    {
        if (Database::runQuery("SELECT user_id FROM tbl_users WHERE user_id=:userid", array(":userid" => $userId))) {
        $UserPostsDataArray = Database::runQuery("SELECT post_id, post_body, post_image, post_likes, post_author FROM tbl_posts WHERE post_author=:userid", array(":userid" => $userId));
        
        return $UserPostsDataArray;
        }
        return false;
    }

    public static function displayPostGrid(Int $userid, String $username, Int $loggedInUserId): String
    {
        $DatabasePosts = Database::runQuery("SELECT post_id, post_body, post_image FROM tbl_posts WHERE post_author=:userid ORDER BY post_id ASC", array(":userid" => $userid));
        $posts         = "";
        foreach ($DatabasePosts as $post) {
            if ($post["post_image"]) {
                $posts .= "<div class='user_posts__card' data-post_id='${post['post_id']}'>
                           <img src='${post['post_image']}' alt='' class='user_posts__card--img'>                            
                        </div>";
            } else {
                $postBody = (strlen($post['post_id']) > 160) ? substr($post['post_body'], 0, 160)."..." : $post['post_body'];
                $posts .= "<div class='user_posts__card border' data-post_id='${post['post_id']}'>
                            <blockquote class='text-post'>
                            " . $postBody . "
                            </blockquote>                                
                        </div>";
            }

        }
        return $posts;
    }
    
    public static function addLink(String $text): String
    {
        $words = explode(" ", $text);
        $textWithlinks = "";

        foreach ($words as $word) {
            if (substr($word, 0, 1) == "@") {        
                $textWithlinks .= '<a href="profile.php?username='. substr($word, 1) .'" class="post__link"> '. htmlspecialchars($word) .' </a>';
            } else if (substr($word, 0, 1) == "#") {
                $textWithlinks .= '<a href="hashtag.php?hashtag='. substr($word, 1) .'" class="post__link"> '. htmlspecialchars($word) .' </a>';
            }else{
                $textWithlinks .= htmlspecialchars($word). " ";
            }
        }

        return $textWithlinks;
    }

    public static function getHashtags(String $text): String
    {
        $words = explode(" ", $text);
        $hashtags = "";

        foreach ($words as $word) {
            if (substr($word, 0, 1) == "#") {        
                $hashtags .= substr($word, 1). ",";
            }
        }

        return $hashtags;
    }


}
