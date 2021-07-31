<?php

class Message
{
	public static function sendMessage(String $csrfToken, String $messageBody, int $sender, int $reciever):void
	{
		if ($sender !== $reciever || $csrfToken) {
			if ($csrfToken === md5($_SESSION['message_csrf'])) {
			Database::runQuery("INSERT INTO tbl_messages(message_body, sender, receiver, send_at) VALUES(:message, :sender, :reciever, NOW())", array(":message" => $messageBody, ":sender" => $sender, ":reciever" => $reciever));
			session_destroy();
			}
		}
	}
	public static function displayMessages(int $sender, int $reciever):array
	{
		return Database::runQuery("SELECT * FROM tbl_messages WHERE sender=:sender AND receiver=:reciever OR sender=:reciever AND receiver=:sender", array(":sender" => $sender, ":reciever" => $reciever));
	}

	public static function recentChatList(int $userId):array
	{
		$recentChats = Database::runQuery("SELECT receiver FROM tbl_messages WHERE sender=:sender ORDER BY id DESC", array(":sender" => $userId));
		$recentChatUsers = array();
		foreach ($recentChats as $value) {
			array_push($recentChatUsers, $value['receiver']);
		}
		return $recentChatUsers;
	}
}