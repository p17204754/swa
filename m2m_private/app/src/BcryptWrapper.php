<?php
/**
 * Created by PhpStorm.
 * User: arron
 * Date: 30/11/2019
 * Time: 19:17
 */

namespace Messages;


class BcryptWrapper
{
    public function __construct(){}

    public function __destruct(){}

    /**
     * function to hash password
     * @param $string_to_hash
     * @return bool|string
     */
    public function HashPassword($string_to_hash)
    {
        $password_to_hash = $string_to_hash;
        $bcrypt_hashed_password = '';

        if (!empty($password_to_hash))
        {
            $options = array('cost' => BCRYPT_COST);
            $bcrypt_hashed_password = password_hash($password_to_hash, BCRYPT_ALGO, $options);
        }
        return $bcrypt_hashed_password;
    }

    /**
     * Function to authenticate a users password
     * @param $string_to_check
     * @param $stored_user_password_hash
     * @return bool
     */
    public function authenticatePassword($string_to_check, $stored_user_password_hash)
    {
        $user_authenticated = false;
        $current_user_password = $string_to_check;
        $stored_user_password_hash = $stored_user_password_hash;
        if (!empty($current_user_password) && !empty($stored_user_password_hash))
        {
            if (password_verify($current_user_password, $stored_user_password_hash))
            {
                $user_authenticated = true;
            }
        }
        return $user_authenticated;
    }
}