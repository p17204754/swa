<?php
/**
 * Created by PhpStorm.
 * User: p17204754
 * Date: 06/11/2019
 * Time: 14:43
 */
namespace Messages;
class SQLQueries
{
    public function __construct()
    {
    }

    public function __destruct()
    {
        // TODO: Implement __destruct() method.
    }


    /**
     * Function to store message data using doctrine
     * @param $queryBuilder
     * @param array $clean_parameters
     * @return array
     */
    public static function StoreMessageData($queryBuilder, array $clean_parameters)
    {
        $store_result = [];
        $message_sender = $clean_parameters['sanitised_number'];
        $message_destination = $clean_parameters['sanitised_destination'];
        $message_time = $clean_parameters['sanitised_time'];
        $message_bearer = $clean_parameters['sanitised_bearer'];
        $message_ref = $clean_parameters['sanitised_ref'];
        $message_switch1 = $clean_parameters['sanitised_switch1'];
        $message_switch2 = $clean_parameters['sanitised_switch2'];
        $message_switch3 = $clean_parameters['sanitised_switch3'];
        $message_switch4 = $clean_parameters['sanitised_switch4'];
        $message_fan = $clean_parameters['sanitised_fanSettings'];
        $message_temp = $clean_parameters['sanitised_temp'];
        $message_keypad = $clean_parameters['sanitised_keypad'];
        $message_group = $clean_parameters['sanitised_group'];

        $queryBuilder = $queryBuilder->insert('messages')
            ->values([
                'message_sender' =>':sender',
                'message_destination' => ':destination',
                'message_time' => ':time',
                'message_bearer' => ':bearer',
                'message_switch1' => ':s1',
                'message_switch2' => ':s2',
                'message_switch3' => ':s3',
                'message_switch4' => ':s4',
                'message_fan' => ':fan',
                'message_temp' => ':temp',
                'message_keypad' => ':keypad',
                'message_group' => ':group',

            ])
            ->setParameters([
                ':sender' => $message_sender,
                ':destination' => $message_destination,
                ':time' => $message_time,
                ':bearer' => $message_bearer,
                ':ref' => $message_ref,
                ':s1' => $message_switch1,
                ':s2' => $message_switch2,
                ':s3' => $message_switch3,
                ':s4' => $message_switch4,
                ':fan' => $message_fan,
                ':temp' => $message_temp,
                ':keypad' => $message_keypad,
                ':group' => $message_group,
            ]);

        $store_result['outcome'] = $queryBuilder->execute();
        $store_result['sql_query'] = $queryBuilder->getSQL();

        return $store_result;
    }

    /**
     * Function to get all stored messages from the database
     * @param $queryBuilder
     * @return mixed
     */
    public static function getStoredMessages($queryBuilder){
        $queryBuilder = $queryBuilder->select('message_id', 'message_sender', 'message_destination', 'message_bearer',
            'message_switch1', 'message_switch2', 'message_switch3', 'message_switch4', 'message_fan',
            'message_temp', 'message_keypad', 'message_group','message_time')
            ->from('messages');
        $retrieved_messages = $queryBuilder->execute()->fetchAll();
        return $retrieved_messages;
    }

    /**
     * Function to check whether a message already exists in the database
     * @param $queryBuilder
     * @param array $message_details
     * @return mixed
     */
    public static function getCheckMessageItems($queryBuilder,  array $message_details){
        $message_sender = $message_details['sanitised_number'];
        $message_time = $message_details['sanitised_time'];
        $queryBuilder = $queryBuilder->select('message_sender', 'message_time')
            ->from('messages')
            ->where($queryBuilder->expr()->andX(
                $queryBuilder->expr()->eq('message_sender', '?'),
                $queryBuilder->expr()->eq('message_time', '?')
            )
            )
            ->setParameters([0 => $message_sender, 1 => $message_time ]);
        $retrieved_Items = $queryBuilder->execute()->fetchAll();
        return $retrieved_Items;
}

    /**
     * Function to store user data in user_info table
     * @param $queryBuilder
     * @param array $clean_parameters
     * @param string $hashed_password
     * @return array
     */
    public static function StoreUserData($queryBuilder, array $clean_parameters,  $hashed_password)
    {
        $store_result = [];
        $username = $clean_parameters['sanitised_username'];
        $email = $clean_parameters['sanitised_email'];

        $queryBuilder = $queryBuilder->insert('user_info')
            ->values([
                'user_name' => ':name',
                'email' => ':email',
                'password' => ':password',
            ])
            ->setParameters([
                ':name' => $username,
                ':email' => $email,
                ':password' => $hashed_password
            ]);

        $store_result['outcome'] = $queryBuilder->execute();
        $store_result['sql_query'] = $queryBuilder->getSQL();

        return $store_result;
    }

    /**
     * Function to get user data using the user_name entered by the user
     * data found in the user_info table
     * @param $queryBuilder
     * @param array $cleaned_parameters
     * @return mixed
     */
    public static function RetrieveUserData($queryBuilder, array $cleaned_parameters)
    {
        $username = $cleaned_parameters['sanitised_username'];

        $queryBuilder = $queryBuilder->select('user_id','user_name', 'password')
            ->from('user_info')
            ->where($queryBuilder->expr()->orX(
                $queryBuilder->expr()->eq('user_name', '?')
            ))
            ->setParameter(0, $username);
        $retrieved_Items = $queryBuilder->execute()->fetchAll();
        $sql_query_string = '';
        return $retrieved_Items;
    }

    /**
     * Function to check whether user data already exists in the user_info table
     * @param $queryBuilder
     * @param array $cleaned_parameters
     * @return mixed
     */
    public static function CheckUserData($queryBuilder, array $cleaned_parameters)
    {
        $username = $cleaned_parameters['sanitised_username'];
        $email = $cleaned_parameters['sanitised_email'];

        $queryBuilder = $queryBuilder->select('user_id','email','user_name', 'password')
            ->from('user_info')
            ->where($queryBuilder->expr()->orX(
                $queryBuilder->expr()->eq('user_name', '?'),
                $queryBuilder->expr()->eq('email', '?')
            ))
            ->setParameters([0 => $username, 1 => $email]);
        $retrieved_Items = $queryBuilder->execute()->fetchAll();
        $sql_query_string = '';
        return $retrieved_Items;
    }

    /**
     * Function to retrieve all users
     * Uses on the admin interface
     * @param $queryBuilder
     * @return mixed
     */
    public static function RetrieveAllUsers($queryBuilder){
        $queryBuilder = $queryBuilder->select('user_id, user_name, email, role, lastLoggedIn, lastModified')
            ->from('user_info');
        $retrieved_Items = $queryBuilder->execute()->fetchAll();

        return $retrieved_Items;
    }

    /**
     * Function to get user data based on the user ID given
     * used on the edit user data page. Access through admin interface
     * @param $queryBuilder
     * @param $user_id
     * @return mixed
     */
    public static function getUserDataToEdit($queryBuilder, $user_id){
        $id = $user_id;
        $queryBuilder = $queryBuilder->select('user_id, user_name, email, role')
            ->from('user_info')
            ->where($queryBuilder->expr()->eq('user_id', '?'))
            ->setParameter(0, $id);
        $retrieved_Items = $queryBuilder->execute()->fetchAll();

        return $retrieved_Items;
    }

    /**
     * Retrieve the user role from the user_info page
     * used to check what role the user has to give authorization
     * @param $queryBuilder
     * @param $user_id
     * @return mixed
     */
    public static function RetrieveUserRole($queryBuilder,  $user_id){
        $id = $user_id;
        $queryBuilder = $queryBuilder->select('user_id, role')
            ->from('user_info')
            ->where($queryBuilder->expr()->eq('user_id', '?'))
            ->setParameter(0, $id);
        $retrieved_Items = $queryBuilder->execute()->fetchAll();

        return $retrieved_Items;
    }

    /**
     * Sets the data and time the user was last logged in
     * used when users log in
     * @param $queryBuilder
     * @param $user_id
     * @param $date
     * @return mixed
     */
    public static function setUserLastLogIn($queryBuilder, $user_id, $date){
        $queryBuilder = $queryBuilder->update('user_info')
            ->set('lastLoggedIn', '?')
            ->where($queryBuilder->expr()->eq('user_id', '?'))
            ->setParameters([0 => $date,1 => $user_id]);

        $update_outcome = $queryBuilder->execute();
        return $update_outcome;
    }

    /**
     * Update user data with the values entered by the admin on the edit user page
     * @param $queryBuilder
     * @param $user_id
     * @param $cleaned_parameters
     * @return mixed
     */
    public static function updateEditedUser($queryBuilder, $user_id, $cleaned_parameters){
        $id = $user_id;
        $username = $cleaned_parameters['sanitised_username'];
        $email = $cleaned_parameters['sanitised_email'];
        $role = $cleaned_parameters['sanitised_role'];
        $queryBuilder = $queryBuilder->update('user_info')
            ->set('user_name', '?')
            ->set('email', '?')
            ->set('role', '?')
            ->where($queryBuilder->expr()->eq('user_id', '?'))
            ->setParameters([0 => $username, 1 => $email, 2 => $role, 3 => $id]);
        $update_outcome = $queryBuilder->execute();

        return $update_outcome;
    }

    /**
     * Deletes user based on the selected user ID in the admin interface
     * @param $queryBuilder
     * @param $user_id
     * @return mixed
     */
    public static function deleteUser($queryBuilder, $user_id)
    {
        $id = $user_id;
        $queryBuilder = $queryBuilder->delete('user_info')
            ->where($queryBuilder->expr()->eq('user_id', '?'))
            ->setParameter(0, $id);
        $deletion_outcome = $queryBuilder->execute();

        return $deletion_outcome;
    }

}