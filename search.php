<?php
include 'config/Database.php';
include 'models/Login.php';

if (isset($_GET['query'])) {
	$searchQueryChunks = str_split($_GET['query'], 2);

	$whereClause = "";
	$paramsArray = array(":username" => '%'. $_GET['query'] .'%');

	for ($i=0; $i < count($searchQueryChunks); $i++) { 
		$whereClause .= " OR user_name LIKE :chunk$i";
		$paramsArray[":chunk$i"] = $searchQueryChunks[$i];	
	}

	$users = Database::runQuery("SELECT user_name, profile_image FROM tbl_users WHERE user_name LIKE :username". $whereClause ."", $paramsArray);

	echo json_encode($users);
} elseif (isset($_GET['guess'])) {
	$searchQueryChunks = str_split($_GET['guess'], 2);

	$whereClause = "";
	$paramsArray = array(":username" => '%'. $_GET['guess'] .'%', ":userid" => Login::isUserLoggedIn());

	for ($i=0; $i < count($searchQueryChunks); $i++) { 
		$whereClause .= " OR user_name LIKE :chunk$i";
		$paramsArray[":chunk$i"] = $searchQueryChunks[$i];	
	}

	$following = Database::runQuery("SELECT tbl_followers.user_id, tbl_users.user_name, tbl_users.profile_image FROM tbl_followers, tbl_users WHERE tbl_users.user_id=tbl_followers.user_id AND tbl_followers.follower_id=:userid AND tbl_users.user_name LIKE :username". $whereClause ."", $paramsArray);
	
	echo json_encode($following);
}