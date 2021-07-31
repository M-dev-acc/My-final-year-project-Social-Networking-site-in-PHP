<?php
include 'config/Database.php';

if (isset($_POST['login'])) {
    $empty_inputs      = array();
    $validation_status = true;
    $valid_password = "";

    foreach ($_POST as $form_field => $field_value) {
        if (empty($field_value)) {
            array_push($empty_inputs, $form_field);
        }
    }

     if (!Database::runQuery('SELECT user_name FROM tbl_users WHERE user_name = :user_login_id OR user_email = :user_login_id', array(':user_login_id' => $_POST['user_id']))) {
        $message = array('message_key' => 'error',
            'error_type'                   => 'invalid_input_value',
            'error_field'                  => 'user_id',
            'message_body'                 => 'User id is not registerd.');
        $validation_status = false;
        echo (json_encode($message, JSON_PRETTY_PRINT));
    }else{
        $user_id        = Database::runQuery('SELECT user_name FROM tbl_users WHERE user_name = :user_login_id OR user_email = :user_login_id', array(':user_login_id' => $_POST['user_id']))[0]['user_name'];
        $valid_password = Database::runQuery("SELECT user_password FROM tbl_users WHERE user_name=:userloginid", array(':userloginid' => $user_id))[0]['user_password'];
    }
    
    if (count($empty_inputs) != 0) {
        $message = array('message_key' => 'error',
            'error_type'                   => 'empty_value',
            'error_field'                  => json_encode($empty_inputs, JSON_FORCE_OBJECT),
            'message_body'                 => 'All fields are required.');
        $validation_status = false;
        echo (json_encode($message, JSON_PRETTY_PRINT));
    } elseif (!password_verify($_POST['user_password'], $valid_password)) {
        $message = array('message_key' => 'error',
            'error_type'                   => 'invalid_input_value',
            'error_field'                  => 'user_password',
            'message_body'                 => 'Password does not match.');
        $validation_status = false;
        echo (json_encode($message, JSON_PRETTY_PRINT));
    } elseif ($validation_status) {
        $cstrong     = true;
        $login_token = bin2hex(openssl_random_pseudo_bytes(64, $cstrong));

        $user_index = Database::runQuery('SELECT user_id FROM tbl_users WHERE user_name = :user_id OR user_email = :user_id', array(':user_id' => $_POST['user_id']))[0]['user_id'];
        Database::runQuery('INSERT INTO tbl_login_tokens(user_id, login_token) VALUES(:user_id, :login_token)', array(':user_id' => $user_index, ':login_token' => md5($login_token)));
        $username = Database::runQuery('SELECT user_name FROM tbl_users WHERE user_id = :user_id', array(':user_id' => $user_index));

        setcookie('SP_login_id', $login_token, time() + 60 * 60 * 24 * 7, '/', null, null, true);
        setcookie('SP_login_id_', 1, time() + 60 * 60 * 24 * 3, '/', null, null, true);

        $redirect = 'index.php';

        $message  = array('message_key' => 'login_success',
            'message_body'                  => 'SuccessFully logged in.',
            'redirect'                      => $redirect);
        $validation_status = false;
        echo (json_encode($message, JSON_PRETTY_PRINT));
        header('location:'.$redirect);
    }
}
