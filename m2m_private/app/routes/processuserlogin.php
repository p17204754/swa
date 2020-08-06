<?php
/**
 * Created by PhpStorm.
 * User: arron
 * Date: 30/11/2019
 * Time: 17:39
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Doctrine\DBAL\DriverManager;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$app->post('/processuserlogin', function(Request $request, Response $response) use ($app)
{

    $logger = new Logger('userLogin');
    $logger->pushHandler(new StreamHandler(LOG_FILE, Logger::INFO));

    $session_id = session_id();
    $tainted_parameters = $request->getParsedBody();

    $cleaned_parameters = cleanUserInputtedDetails($app, $tainted_parameters);

    $login_result = retrieveUserDetails($app, $logger, $cleaned_parameters);

    return $this->view->render($response,
        'login_user_result.html.twig',
        [
            'css_path' => CSS_PATH,
            'landing_page' => LANDING_PAGE,
            'action3' => ADMIN,
            'action4' => SEND,
            'action5' => DOWNLOAD,
            'action6' => STORE,
            //'method' => 'post',
            //'action' => 'processloginuser',
            'page_title' => APP_NAME,
            'page_heading_1' => APP_NAME,
            'page_heading_2' => "",
            'login_result' => $login_result,
        ]);
});

/**
 * Function to clean entered user inputs
 * @param $app
 * @param $tainted_parameters
 * @return array
 */
function cleanUserInputtedDetails($app, $tainted_parameters)
{
    $cleaned_parameters = [];
    $validator = $app->getContainer()->get('validator');

    $tainted_username = $tainted_parameters['username'];
    //$tainted_email = $tainted_parameters['email'];

    $cleaned_parameters['password'] = $tainted_parameters['password'];
    $cleaned_parameters['sanitised_username'] = $validator->sanitiseString($tainted_username);
    //$cleaned_parameters['sanitised_email'] = $validator->sanitiseString($tainted_email);
    //$cleaned_parameters['sanitised_requirements'] = $validator->sanitiseString($tainted_requirements);
    return $cleaned_parameters;
}

/**
 * Function to fetch and authenticate user details
 * @param $app
 * @param Logger $logger
 * @param array $cleaned_parameters
 * @return string
 * @throws \Doctrine\DBAL\DBALException
 */
function retrieveUserDetails($app, Logger $logger, array $cleaned_parameters): string
{
    $fetch_result= [];
    $authenticate_result = '';
    $database_connection_settings = $app->getContainer()->get('doctrine_settings');
    $database_queries = $app->getContainer()->get('sqlQueries');
    $database_connection = DriverManager::getConnection($database_connection_settings);


    $queryBuilder = $database_connection->createQueryBuilder();
    if($cleaned_parameters['password'] !== "" and $cleaned_parameters['sanitised_username'] !== ""){
        try{
            $fetch_result = $database_queries::RetrieveUserData($queryBuilder, $cleaned_parameters);

            $session_wrapper = $app->getContainer()->get('sessionWrapper');
            $session_model = $app->getContainer()->get('sessionModel');
            $session_model->setSessionWrapper($session_wrapper);

            $bcrypt_wrapper = $app->getContainer()->get('bcryptWrapper');
            $authenticate = $bcrypt_wrapper->authenticatePassword($cleaned_parameters['password'],
                $fetch_result[0]['password']);

            if ($authenticate == true)
            {
                date_default_timezone_set('Europe/London');
                $date = date('Y-m-d H:i:s');
                $authenticate_result = 'User data was successfully authenticated';
                $session_model->setSessionIsLoggedIn(true);
                $session_model->setSessionUserId($fetch_result[0]['user_id']);
                $test = $database_queries->setUserLastLogIn($queryBuilder, $fetch_result[0]['user_id'], $date);
                //$session_model->setSessionUsername($fetch_result[0]['user_name']);
                $session_model->storeLoginDataInSessionsFile();
                $result = $session_model->getStorageResult();
                //var_dump($result);
                $login = $session_model->getStoredValues('isLoggedIn');
                $user_id = $session_model->getStoredValues('user_id');
                //var_dump($login);
                //var_dump($user_id);
                $logger->info('User logged in', ['username' => $cleaned_parameters['sanitised_username']]);
            }
            else
            {
                $authenticate_result = 'There appears to have been a problem when authenticating your details.  
                Please try again later.';

            }
        }catch(exception $e){
            $authenticate_result = 'An error occurred ' . $e->getMessage();
        }
    }
    else{
        $authenticate_result = 'Please enter a valid username or password';
    }


    return $authenticate_result;
}

