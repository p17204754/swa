<?php
/**
 * Created by PhpStorm.
 * User: arron
 * Date: 30/11/2019
 * Time: 18:10
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Doctrine\DBAL\DriverManager;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$app->post('/processregisteruser', function(Request $request, Response $response) use ($app)
{



    $logger = new Logger('registerUser');
    $logger->pushHandler(new StreamHandler(LOG_FILE, Logger::INFO));



    $tainted_parameters = $request->getParsedBody();
    $session_id = session_id();
    //var_dump($session_id);
    $clean_parameters = cleanuserDetails($app, $tainted_parameters);
   // var_dump($clean_parameters);
    $hashed_password = hash_password($app, $clean_parameters['password']);
    //var_dump($hashed_password);
    $register_result = storeNewUserDetails($app, $logger, $clean_parameters, $hashed_password);
    $store_result = [];
    return $this->view->render($response,
        'register_user_result.html.twig',
        [
            'css_path' => CSS_PATH,
            'landing_page' => LANDING_PAGE,
            'action3' => ADMIN,
            'action4' => SEND,
            'action5' => DOWNLOAD,
            'action6' => STORE,
            'page_title' => APP_NAME,
            'page_heading_1' => APP_NAME,
            'page_heading_2' => "Register result",
            'cleaned_username' => $clean_parameters['sanitised_username'],
            'cleaned_email' => $clean_parameters['sanitised_email'],
            'welcome_message' => $register_result['welcomemsg'],
            'store_result' => $register_result['outcome'],
            //'login_result' => $register_result,
        ]);
});

/**
 * Function to clean user details that the user wishes to register with
 * @param $app
 * @param array $tainted_parameters
 * @return array of validated values
 */
function cleanuserDetails($app, array $tainted_parameters){
    $cleaned_parameters = [];
    $validator = $app->getContainer()->get('validator');

    $tainted_username = $tainted_parameters['username'];
    $tainted_email = $tainted_parameters['email'];

    $cleaned_parameters['password'] = $tainted_parameters['password'];
    $cleaned_parameters['sanitised_username'] = $validator->sanitiseString($tainted_username);
    $cleaned_parameters['sanitised_email'] = $validator->sanitiseEmail($tainted_email);

    return $cleaned_parameters;
}

/**
 * function to hash the users password
 * @param $app
 * @param $password
 * @return string hashed string
 */
function hash_password($app, $password): string{
    $bcrypt_wrapper = $app->getContainer()->get('bcryptWrapper');
    $hashed_password = $bcrypt_wrapper->hashPassword($password);
    return $hashed_password;
}

/**
 * Function to check that the entered user details have not already been taken
 * @param $app
 * @param array $cleaned_parameters validated user details
 * @return int|string returns an integar to show which value has already  been taken
 * @throws \Doctrine\DBAL\DBALException
 */
function checkEnteredDetails($app, array $cleaned_parameters)
{
    $fetch_result= [];
    $result = '';
    $database_connection_settings = $app->getContainer()->get('doctrine_settings');
    $database_queries = $app->getContainer()->get('sqlQueries');
    $database_connection = DriverManager::getConnection($database_connection_settings);


    $queryBuilder = $database_connection->createQueryBuilder();
    try{
        $fetch_result = $database_queries::CheckUserData($queryBuilder, $cleaned_parameters);
        if($cleaned_parameters['sanitised_username'] == "")
        {
            $result = 3;
        }
        else if($cleaned_parameters['sanitised_email'] == "")
        {
            $result = 4;
        }
        else if($cleaned_parameters['password'] == "")
        {
            $result = 5;
        }
        else if(count($fetch_result) == 0)
        {
            $result = 0;
        }
        else if($fetch_result[0]['user_name'] == $cleaned_parameters['sanitised_username']){
            $result = 1;
        }
        else if($fetch_result[0]['email'] == $cleaned_parameters['sanitised_email'])
        {
            $result = 2;
        }
    }catch(exception $e){
        $result = 'An error occurred: ' . $e->getMessage();
    }


    return $result;
}


/**
 * Function to store the new user details
 * @param $app
 * @param Logger $logger allows for logs to be created when new user is created
 * @param array $cleaned_parameters validated user details
 * @param string $hashed_password hashed user password
 * @return array array of information to display based on outcome
 * @throws \Doctrine\DBAL\DBALException
 */
function storeNewUserDetails($app, Logger $logger, array $cleaned_parameters, string $hashed_password): array
{
    $check_details = checkEnteredDetails($app, $cleaned_parameters);
    $storage_result = [];
    $store_result = [];

    if($check_details === 0)
    {

        $database_connection_settings = $app->getContainer()->get('doctrine_settings');
        $database_queries = $app->getContainer()->get('sqlQueries');
        $database_connection =  DriverManager::getConnection($database_connection_settings);

        $queryBuilder = $database_connection->createQueryBuilder();
        try{
            $storage_result = $database_queries::StoreUserData($queryBuilder, $cleaned_parameters, $hashed_password);
        }catch (exception $e){
            echo 'An error occurred:' . $e->getMessage();
        }


        if($storage_result['outcome'] ==1 )
        {
            $store_result['outcome'] = 'Your account was successfully created!';
            $store_result['welcomemsg'] = $cleaned_parameters['sanitised_username'] . ' thank you for registering!';
            $logger->info('New user registered', ['username' => $cleaned_parameters['sanitised_username'],
                'email' => $cleaned_parameters['sanitised_email']]);
        }
        else
        {
            $store_result['outcome'] = 'There was a problem creating your new account. Please try again';
            $store_result['welcomemsg'] = '';
            $logger->warning('Could not store new user data', ['username' => $cleaned_parameters['sanitised_username'],
                'email' => $cleaned_parameters['sanitised_email']]);
        }
    }
    else if($check_details === 1){
        $store_result['outcome'] = 'user name is already taken. Please try again';
        $store_result['welcomemsg'] = '';
        $logger->info('New user was not registered as username was already taken',
            ['username' => $cleaned_parameters['sanitised_username']]);
    }
    else if($check_details === 2){
        $store_result['outcome'] = 'Email is already linked to another account. Please try again';
        $store_result['welcomemsg'] = '';
        $logger->info('New user was not registered as email is already assigned to another account',
            ['email' => $cleaned_parameters['sanitised_email']]);
    }
    else if($check_details === 3){
        $store_result['outcome'] = 'Please enter a user name.';
        $store_result['welcomemsg'] = '';
        $logger->info('New user was not registered as username was not provided',
            ['email' => $cleaned_parameters['sanitised_email']]);
    }
    else if($check_details === 4){
        $store_result['outcome'] = 'Please enter a email address';
        $store_result['welcomemsg'] = '';
        $logger->info('New user was not registered as email was not provided',
            ['email' => $cleaned_parameters['sanitised_email']]);
    }
    else if($check_details === 5){
        $store_result['outcome'] = 'Please enter a valid password';
        $store_result['welcomemsg'] = '';
        $logger->info('New user was not registered as password was not provided',
            ['email' => $cleaned_parameters['sanitised_email']]);
    }
    else{
        $store_result['outcome'] = 'Oops something went wrong. Please try again';
        $store_result['welcomemsg'] = '';
        $logger->warning('New user could not be registered',
            ['username' => $cleaned_parameters['sanitised_username'],'email' => $cleaned_parameters['sanitised_email']]);
    }

    return $store_result;
}