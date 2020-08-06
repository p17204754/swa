<?php
/**
 * Created by PhpStorm.
 * User: arron
 * Date: 19/12/2019
 * Time: 21:27
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Doctrine\DBAL\DriverManager;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$logged_user = checkUserRole($app);
$app->post('/adminInterface/processadminedituser', function(Request $request, Response $response) use ($app)
{


    $logger = new Logger('adminEditUser');
    $logger->pushHandler(new StreamHandler(LOG_FILE, Logger::INFO));
    $logger->pushHandler(new StreamHandler(LOG_FILE, Logger::WARNING));



    $tainted_parameters = $request->getParsedBody();
    $session_id = session_id();
    //var_dump($session_id);
    //var_dump($tainted_parameters);
    $clean_parameters = cleanuserDetailsToSave($app, $tainted_parameters);
    // var_dump($clean_parameters);
    //var_dump($hashed_password);
    $save_result = saveNewUserDetails($app, $tainted_parameters['user_id'], $clean_parameters, $logger);
    $store_result = [];
    return $this->view->render($response,
        'admin_edit_user_result.html.twig',
        [
            'css_path' => CSS_PATH,
            'landing_page' => LANDING_PAGE,

            'page_title' => APP_NAME,
            'page_heading_1' => APP_NAME,
            'page_heading_2' => "Save result",
            'cleaned_username' => $clean_parameters['sanitised_username'],
            'cleaned_email' => $clean_parameters['sanitised_email'],
            'save_result' => $save_result['outcome'],
            //'login_result' => $register_result,
        ]);
})->setName('admin_edit_process')->add(new Messages\Authorization($logged_user));

/**
 * Function to clean user details to save from edits
 * @param $app
 * @param array $tainted_parameters
 * @return array array of validated values
 */
function cleanuserDetailsToSave($app, array $tainted_parameters){
    $cleaned_parameters = [];
    $validator = $app->getContainer()->get('validator');

    $tainted_username = $tainted_parameters['edited_username'];
    $tainted_email = $tainted_parameters['edited_email'];
    $tainted_role = $tainted_parameters['edited_role'];

    $cleaned_parameters['sanitised_username'] = $validator->sanitiseString($tainted_username);
    $cleaned_parameters['sanitised_email'] = $validator->sanitiseEmail($tainted_email);
    $cleaned_parameters['sanitised_role'] = $validator->validateUserOption($tainted_role);

    return $cleaned_parameters;
}


/**
 * function to save the edited details
 * @param $app
 * @param $user_id id of the user to be edited
 * @param array $cleaned_parameters
 * @param $logger allows for logging of the process
 * @return array of infomation about the save process
 */
function saveNewUserDetails($app, $user_id, array $cleaned_parameters, $logger): array
{
    $storage_result = [];
    $store_result = [];
if(!in_array(false, $cleaned_parameters)){
    try{
        $database_connection_settings = $app->getContainer()->get('doctrine_settings');
        $database_queries = $app->getContainer()->get('sqlQueries');
        $database_connection =  DriverManager::getConnection($database_connection_settings);

        $queryBuilder = $database_connection->createQueryBuilder();

        $storage_result['outcome'] = $database_queries::updateEditedUser($queryBuilder, $user_id, $cleaned_parameters);



        if($storage_result['outcome'] == 1 )
        {
            $store_result['outcome'] = 'Account amendments saved successfully!';
            $logger->info('New user details stored', ['username' => $cleaned_parameters['sanitised_username'],
                'email' => $cleaned_parameters['sanitised_email'], 'role' => $cleaned_parameters['sanitised_role']]);
        }
        else
        {
            $store_result['outcome'] = 'There was a problem saving edited values. Please try again';
            $logger->warning('Could not store new user data', ['username' => $cleaned_parameters['sanitised_username'],
                'email' => $cleaned_parameters['sanitised_email'], 'role' => $cleaned_parameters['sanitised_role']]);
        }
    }catch (exception $e){
        $store_result = 'An error occurred please try again' .$e->getCode();
    }
}else{
    if($cleaned_parameters['sanitised_username'] == false){
        $store_result['outcome'] = 'Please enter a valid user name';
    }
    else if($cleaned_parameters['sanitised_email'] == false){
        $store_result['outcome'] = 'Please enter a valid Email address';
    }
    else if($cleaned_parameters['sanitised_role'] == false){
        $store_result['outcome'] = 'Please enter a valid Role from the role list: ' . implode(", ",USER_TYPES);
    }
    else{
        $store_result['outcome'] = 'Something went wrong. PLease try again';
    }

}


    return $store_result;
}