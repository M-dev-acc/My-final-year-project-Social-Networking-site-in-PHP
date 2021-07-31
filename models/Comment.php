<?php

class Comment
{
	public static function postComment(String $commentBody, int $postId, int $userId): void
	{
		if (strlen($commentBody) > 160 || strlen($commentBody) < 1) {
            // die('Incorrect length!');
        }

        if (!Database::runQuery('SELECT post_id FROM tbl_posts WHERE post_id=:postid', array(':postid'=>$postId))) {
            echo 'Invalid post ID';
        } else {
    		Database::runQuery("INSERT INTO tbl_comments(comment_body, post_id, user_id, created_at) VALUES(:commentbody, :postid, :userid, NOW())", array(":commentbody" => $commentBody, ":postid" => $postId, ":userid" => $userId));

        }
	}

	public static function displayComments(int $postId, int $limit): array
	{
		if ($limit !== 0) {
			$comments = Database::runQuery("SELECT tbl_comments.comment_body AS comment, tbl_users.user_name AS user FROM tbl_users, tbl_comments WHERE tbl_comments.post_id=:postid AND tbl_users.user_id=tbl_comments.user_id ORDER BY tbl_comments.comment_id DESC LIMIT " . $limit, array(":postid" => $postId));				
		}else{
			$comments = Database::runQuery("SELECT tbl_comments.comment_body AS comment, tbl_users.user_name AS user FROM tbl_users, tbl_comments WHERE tbl_comments.post_id=:postid AND tbl_users.user_id=tbl_comments.user_id ORDER BY tbl_comments.comment_id DESC", array(":postid" => $postId));
		}
		return $comments;
	}
}