<?php
require_once 'config/Database.php';

$db = new Database();

if (isset($_POST['register'])) {
    $empty_inputs      = array();
    $validation_status = true;

    foreach ($_POST as $form_field => $field_value) {
        if (empty($field_value)) {
            array_push($empty_inputs, $form_field);
        }
    }
    if (count($empty_inputs) != 0) {
        $message = array('message_key' => 'error',
            'error_type'                   => 'empty_value',
            'error_field'                  => json_encode($empty_inputs, JSON_FORCE_OBJECT),
            'message_body'                 => 'All fields are required.');
        $validation_status = false;
        echo (json_encode($message, JSON_PRETTY_PRINT));
    } elseif (count(str_split($_POST['user_name'])) < 3 || count(str_split($_POST['user_name'])) > 40) {
        $validation_status = false;
        $message           = array('message_key' => 'error',
            'error_type'                             => 'invalid_input_length',
            'error_field'                            => 'user_name',
            'message_body'                           => 'Username must have 3-30 letters.');
        echo (json_encode($message, JSON_PRETTY_PRINT));
    } elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
        $message = array('message_key' => 'error',
            'error_type'                   => 'invalid_email_format',
            'error_field'                  => 'email',
            'message_body'                 => 'Use correct format of an email.');
        $validation_status = false;
        echo (json_encode($message, JSON_PRETTY_PRINT));
    } elseif (count(str_split($_POST['password'])) < 10 || count(str_split($_POST['password'])) > 60) {
        $message = array('message_key' => 'error',
            'error_type'                   => 'invalid_input_length',
            'error_field'                  => 'password',
            'message_body'                 => 'Password must have 10-60 letters.');
        $validation_status = false;
        echo (json_encode($message, JSON_PRETTY_PRINT));
    } elseif ($validation_status === true) {
        if (!$db->runQuery('SELECT user_name FROM tbl_users WHERE user_name = :user_name', array(':user_name' => $_POST['user_name']))) {
            if (!$db->runQuery('SELECT user_email FROM tbl_users WHERE user_email = :email', array(':email' => $_POST['email']))) {
                $db->runQuery('INSERT INTO tbl_users(user_name, user_password, user_email, user_about) VALUES (:username, :password, :email, :username)', array(':username' => $_POST['user_name'],
                    ':password'                                                                                                                          => password_hash($_POST['password'], PASSWORD_BCRYPT),
                    ':email'                                                                                                                             => $_POST['email']));
                $message = array('message_key' => 'success',
                    'message_body'                 => 'Your account has been created.');
                echo (json_encode($message, JSON_PRETTY_PRINT));
                header('location: login.html');
            } else {
                $message = array('message_key' => 'error',
                    'error_type'                   => 'invalid_input_value',
                    'error_field'                  => 'email',
                    'message_body'                 => 'Email already selected.');
                echo (json_encode($message, JSON_PRETTY_PRINT));
            }
        } else {
            $message = array('message_key' => 'error',
                'error_type'                   => 'invalid_input_value',
                'error_field'                  => 'user_name',
                'message_body'                 => 'Username alredy selected.');
            echo (json_encode($message, JSON_PRETTY_PRINT));
        }
    }

}
