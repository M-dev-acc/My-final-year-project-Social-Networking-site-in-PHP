<?php
class Login
{
    public static function isUserLoggedIn()
    {
        if (isset($_COOKIE["SP_login_id"])) {
            if (Database::runQuery('SELECT user_id FROM tbl_login_tokens WHERE login_token=:token', array(':token' => md5($_COOKIE['SP_login_id'])))) {
                $userid = Database::runQuery('SELECT user_id FROM tbl_login_tokens WHERE login_token=:token', array(':token' => md5($_COOKIE['SP_login_id'])))[0]['user_id'];

                if (isset($_COOKIE["SP_login_id_"])) {
                    return $userid;
                } else {
                    $cstrong = true;
                    $token   = bin2hex(openssl_random_pseudo_bytes(64, $cstrong));
                    Database::runQuery('INSERT INTO tbl_login_tokens VALUES (\'\', :user_id, :token)', array(':token' => sha1($token), ':user_id' => $userid));
                    Database::runQuery('DELETE FROM tbl_login_tokens WHERE login_token=:token', array(':token' => md5($_COOKIE['SP_login_id'])));

                    setcookie("SP_login_id", $token, time() + 60 * 60 * 24 * 7, '/', null, null, true);
                    setcookie("SP_login_id_", '1', time() + 60 * 60 * 24 * 3, '/', null, null, true);
                    
                    return $userid;
                }
            }
        }

        return false;
    }

}
