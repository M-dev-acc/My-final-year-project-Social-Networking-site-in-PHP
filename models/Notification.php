<?php

class Notification
{
	public static function createNotification(String $postBody=""):array
	{
        $words = explode(" ", $postBody);
        $notification = array();

		foreach ($words as $word) {
            if (substr($word, 0, 1) == "@") {
            	$notification[substr($word, 1)] = 1;
            }
        }	

        return $notification;
	}

	public static function sendNotification(String $postBody = "", Int $loggedInUserId = 0, array $extra):void
	{
        if (count(self::createNotification($postBody)) != 0) {
            foreach (self::createNotification($postBody) as $username => $notification) {
                $sender = $loggedInUserId;
                $reciever = Database::runQuery("SELECT user_id FROM tbl_users WHERE user_name=:username", array(':username' => $username))[0]["user_id"];

                if ($reciever != 0) {
                    Database::runQuery("INSERT INTO tbl_notifications(notification_type, notification_sender, notification_reciever, created_at, extra) VALUES(:type, :sender, :reciever, NOW(), :extra)", array(':type' => $notification, ':sender' => $sender, ':reciever' => $reciever, ':extra' => json_encode($extra)));
                }
            }
        }elseif ($postBody == "" && $extra['post_id'] != 0) {
            $likedPostData = Database::runQuery("SELECT tbl_posts.post_author AS reciever, tbl_post_likes.post_liked_by AS sender FROM tbl_posts, tbl_post_likes WHERE tbl_posts.post_id=tbl_post_likes.post_id AND tbl_posts.post_id=:postid", array(':postid' => $extra['post_id']));
            $sender = $likedPostData[0]['sender'];
            $reciever = $likedPostData[0]['reciever'];

            Database::runQuery("INSERT INTO tbl_notifications(notification_type, notification_sender, notification_reciever, created_at, extra) VALUES(:type, :sender, :reciever, NOW(), :extra)", array(':type' => 2, ':sender' => $sender, ':reciever' => $reciever, ':extra' => json_encode($extra)));
        }elseif ($postBody == "" && $extra['user_id'] != 0 && $extra['follower_id'] != 0) {
            $followerData = Database::runQuery("SELECT tbl_followers.user_id AS reciever, tbl_followers.follower_id AS sender FROM tbl_followers WHERE tbl_followers.user_id=:reciever AND tbl_followers.follower_id=:sender", array(':reciever' => $extra['user_id'], ':sender' => $extra['follower_id']));
            $sender = $followerData[0]['sender'];
            $reciever = $followerData[0]['reciever'];

            Database::runQuery("INSERT INTO tbl_notifications(notification_type, notification_sender, notification_reciever, created_at, extra) VALUES(:type, :sender, :reciever, NOW(), :extra)", array(':type' => 3, ':sender' => $sender, ':reciever' => $reciever, ':extra' => json_encode($extra)));
        }
	}

    public static function displayNotifications(Int $userId): void
    {
        if (Database::runQuery("SELECT * FROM tbl_notifications WHERE notification_reciever=:userid", array(':userid' => $userId))) {
        $notifications = Database::runQuery("SELECT notification_type, notification_sender, extra, created_at FROM tbl_notifications WHERE notification_reciever=:userid ORDER BY notification_id DESC", array(':userid' => $userId));
            foreach ($notifications as $notification) {
                $sender = Database::runQuery("SELECT user_name FROM tbl_users WHERE user_id=:userid", array(':userid' => $notification['notification_sender']))[0]['user_name'];
                
                if ($notification['notification_type'] == 1) {
                    $postId = json_decode($notification['extra'], true)['post_id'];
                    echo "<li class='navigation__links_item notification' data-href='post.php?post_id=${postId}'>
                                <a href='profile.php?username=${sender}'>${sender}</a> has mentioned you in a post.
                                &#9679; <time>". self::timeAgo($notification['created_at']) ."</time>
                            </li>";
                    
                } elseif ($notification['notification_type'] == 2) {
                    $postId = json_decode($notification['extra'], true)['post_id'];
                    echo "<li class='navigation__links_item notification' data-href='post.php?post_id=${postId}'>
                                <a href='profile.php?username=${sender}'>${sender}</a> liked your post.
                                &#9679; <time>". self::timeAgo($notification['created_at']) ."</time>
                            </li>";
                } elseif($notification['notification_type'] == 3){
                   echo "<li class='navigation__links_item notification' data-href='profile.php?username=${sender}'>
                                <a href='profile.php?username=${sender}'>${sender}</a> started following you.
                                &#9679; <time>". self::timeAgo($notification['created_at']) ."</time>
                            </li>"; 
                }
                
            }
       
        }else{
            echo "Nothing happened!";
        }
    }

    public static function timeAgo(string $dateString):String
    {
        $from = new DateTime($dateString);
        $timeDifference = $from->diff(new DateTime("now"));

        if ($timeDifference->y > 0) {
            // return ($timeDifference->y == 1)? $timeDifference->format("%yy ago") : ;
            return $timeDifference->format("%yy ago");
        }elseif ($timeDifference->m > 0) {
            return $timeDifference->format("%mm ago");
        }elseif ($timeDifference->d > 0) {
            return $timeDifference->format("%dd ago");
        }elseif ($timeDifference->h > 0) {
            return $timeDifference->format("%hh ago");
        }elseif ($timeDifference->i > 0) {
            return $timeDifference->format("%imin ago");
        }elseif ($timeDifference->s > 0) {
            return $timeDifference->format("%ssec ago");
        }      
    }

}