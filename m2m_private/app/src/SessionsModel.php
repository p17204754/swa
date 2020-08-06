<?php
/**
 * Created by PhpStorm.
 * User: p17204754
 * Date: 06/11/2019
 * Time: 14:41
 */

namespace Messages;

class SessionsModel
{
    private $value_to_store;
    private $userId_to_store;
    private $username_to_store;
    private $downloaded_messages;
    private $storage_result;
    private $session_wrapper;
    private $database_wrapper;
    private $database_connection_settings;
    private $sql_queries;

    public function __construct()
    {
        $this->value_to_store = null;
        $this->userId_to_store = null;
        $this->username_to_store = null;
        $this->downloaded_messages = null;
        $this->storage_result = null;
        $this->session_wrapper = null;
        $this->database_wrapper = null;
        $this->database_connection_settings = null;
        $this->sql_queries = null;
    }

    public function __destruct() { }

    /**
     * used to set whether a user has logged in
     * @param $value_to_store
     */
    public function setSessionIsLoggedIn($value_to_store)
    {
        $this->value_to_store = $value_to_store;
    }

    /**
     * Used to store the user Id for later use in other scripts
     * @param $value_to_store
     */
    public function setSessionUserId($value_to_store)
    {
        $this->userId_to_store = $value_to_store;
    }

    /**
     * Used to set the user name of the logged in user
     * @param $value_to_store
     */
    public function setSessionUsername($value_to_store)
    {
        $this->username_to_store = $value_to_store;
    }

    /**
     * Sets downloaded messages during the session for later use
     * used when finding which message to download into the database
     * @param $value_to_store
     */
    public function setDownloadedMessages($value_to_store)
    {
        $this->downloaded_messages = $value_to_store;
    }

    /**
     * Used to set the session wrapper in order to use its methods
     * @param $session_wrapper
     */
    public function setSessionWrapper($session_wrapper)
    {
        $this->session_wrapper = $session_wrapper;
    }

    /**
     * Used to set the database wrapper
     * @param $database_wrapper
     */
    public function setDatabaseWrapper($database_wrapper)
    {
        $this->database_wrapper = $database_wrapper;
    }

    /**
     * Used to set the database connection allowing us to connect to the database using
     * a given connection string
     * @param $database_connection_settings
     */
    public function setDatabaseConnectionSettings($database_connection_settings)
    {
        $this->database_connection_settings = $database_connection_settings;
    }

    /**
     * Used to set sql queries for the database to be queried on
     * @param $sql_queries
     */
    public function setSqlQueries($sql_queries)
    {
        $this->sql_queries = $sql_queries;
    }

    public function getStorageResult()
    {
        return $this->storage_result;
    }

    /**
     * Used to get the stored values of a given session key
     * @param $session_key
     * @return mixed
     */
    public function getStoredValues($session_key){


        $stored_value = $this->session_wrapper->getSessionVar($session_key);

        $stored_values = $stored_value;

        return $stored_values;

    }

    /**
     * Used to unset the login status of a user
     * Unsets the user_id as well
     * used when the user is logging out
     */
    public function unsetLoginStatus()
    {
        $store_result = false;
        $store_result_status = $this->session_wrapper->unsetSessionVar('isLoggedIn');
        $store_result_userId = $this->session_wrapper->unsetSessionVar('user_id');


        if ($store_result_status !== false && $store_result_userId !== false)
        {
            $store_result = true;
        }
        $this->storage_result = $store_result;
    }

    /**
     * used to unset the logged in users username
     * used when the user is logging out
     */
    public function unsetUsername()
    {
        $store_result = false;
        $store_result_username = $this->session_wrapper->unsetSessionVar('user_name', $this->username_to_store);
        if  ($store_result_username !== false)
        {
            $store_result = true;
        }
        $this->storage_result = $store_result;
    }

    /**
     * Unsets the downloaded messages from the EE server
     */
    public function unsetDownloadedMessages()
    {
        $store_result = false;
        $store_result_messages = $this->session_wrapper->unsetSessionVar('downloaded_messages', $this->downloaded_messages);
        if  ($store_result_messages !== false)
        {
            $store_result = true;
        }
        $this->storage_result = $store_result;
    }

    /**
     * Used to use set the user's log in details to multiple session values
     */
    public function storeLoginDataInSessionsFile(){
        $store_result = false;
        $store_result_status = $this->session_wrapper->setSessionVar('isLoggedIn', $this->value_to_store);
        $store_result_userId = $this->session_wrapper->setSessionVar('user_id', $this->userId_to_store);
        $store_result_username = $this->session_wrapper->setSessionVar('user_name', $this->username_to_store);

        if ($store_result_status !== false && $store_result_userId !== false && $store_result_username !== false)	{
            $store_result = true;
        }
        $this->storage_result = $store_result;
    }

    /**
     * Used to store the downloaded messages in a session
     */
    public function storeMessageDataInSessionFile()
    {
        $store_result = false;
        $store_result_message = $this->session_wrapper->setSessionVar('downloaded_messages', $this->downloaded_messages);

        if ($store_result_message !== false)	{
            $store_result = true;
        }
        $this->storage_result = $store_result;
    }
}