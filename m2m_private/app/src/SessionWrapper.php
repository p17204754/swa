<?php
/**
 * Created by PhpStorm.
 * User: p17175791
 * Date: 27/11/2019
 * Time: 17:41
 */

namespace Messages;


class SessionWrapper
{
    public function __construct(){}

    public function __destruct()
    {}

    /**
     * Function to set the session variable using a given key and value
     * @param $session_key
     * @param $session_value_to_set
     * @return bool
     */
    public function setSessionVar($session_key, $session_value_to_set)
    {
        $session_value_set_successfully = false;
        if (!empty($session_value_to_set))
        {
            $_SESSION[$session_key] = $session_value_to_set;
            if (isset($_SESSION[$session_key], $session_value_to_set) == 0)
            {
                $session_value_set_successfully = true;
            }
        }
        return $session_value_set_successfully;
    }

    /**
     * Function to get a session value given a key
     * @param $session_key
     * @return bool
     */
    public function getSessionVar($session_key)
    {
        $session_value = false;

        if (isset($_SESSION[$session_key]))
        {
            $session_value = $_SESSION[$session_key];
        }
        return $session_value;
    }

    /**
     * Function to unset a session key and its value using a given key
     * @param $session_key
     * @return bool
     */
    public function unsetSessionVar($session_key)
    {
        $unset_session = false;
        if (isset($_SESSION[$session_key]))
        {
            unset($_SESSION[$session_key]);
        }
        if (!isset($_SESSION[$session_key]))
        {
            $unset_session = true;
        }
        return $unset_session;
    }
}