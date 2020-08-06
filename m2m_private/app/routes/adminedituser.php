<?php
/**
 * Created by PhpStorm.
 * User: arron
 * Date: 19/12/2019
 * Time: 19:35
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Doctrine\DBAL\DriverManager;
$logged_user = checkUserRole($app);
$app->get('/adminInterface/edituser', function(Request $request, Response $response) use($app)
{

    $route = $request->getAttribute('route');
    $user_id = $request->getQueryParams('user_id');

        //$route->getArgument('user_id');
    $session_id = session_id();
    $tainted_details = getUserDetails($app, $user_id);
    $cleaned_details = cleanUserDetailsToEdit($tainted_details['items'], $app);
    return $this->view->render($response,
        'admin_edit_user.html.twig',
        [
            'css_path' => CSS_PATH,
            'landing_page' => LANDING_PAGE,
            'method' => 'post',
            'action' => 'processadminedituser',
            'page_title' => APP_NAME,
            'page_heading_1' => APP_NAME,
            'page_heading_2' => "Edit user",
            'user_id' => $user_id['user_id'],
            'user_name' => $cleaned_details['cleaned_user_name'],
            'email' => $cleaned_details['cleaned_email'],
            'role' => $cleaned_details['cleaned_role'],
            'message' => $cleaned_details['message'],
            'user_exists' => $cleaned_details['user_exists']
        ]);

})->setName('admin_edit_user')->add(new \Messages\Authorization($logged_user));

/**
 * Get user details from the database that are to be edited based on the selected user_id
 * @param $app
 * @param $user_id
 * @return mixed
 * @throws \Doctrine\DBAL\DBALException
 */
function getUserDetails($app, $user_id)
{
    $id = $user_id['user_id'];

    $database_connection_settings = $app->getContainer()->get('doctrine_settings');
    $database_queries = $app->getContainer()->get('sqlQueries');
    $database_connection = DriverManager::getConnection($database_connection_settings);


    $queryBuilder = $database_connection->createQueryBuilder();
    try{
        $fetch_result['items'] = $database_queries:: getUserDataToEdit($queryBuilder, $id);
        $fetch_result['message'] = 'User data was successfully fetched';
    }catch (exception $e){
        $fetch_result['items'] = [];
        $fetch_result['message'] = 'An error occurred ' . $e->getMessage();
    }

    return $fetch_result;
}

/**
 * Clean user details that were fetched from the server.
 * @param $tainted_details
 * @param $app
 * @return array
 */
function cleanUserDetailsToEdit($tainted_details, $app)
{
    $cleaned_parameters = [];
    //$test = $app->getContainer()->get('databaseWrapper');
    $validator = $app->getContainer()->get('validator');




    if(!empty($tainted_details))
    {
        $tainted_user_name = $tainted_details[0]['user_name'];
        $tainted_email = $tainted_details[0]['email'];
        $tainted_role = $tainted_details[0]['role'];

        $cleaned_parameters['user_exists'] = true;
        $cleaned_parameters['message'] = 'Please enter the new user details';
        $cleaned_parameters['cleaned_user_name'] = $validator->sanitiseString($tainted_user_name);
        $cleaned_parameters['cleaned_email'] = $validator->sanitiseEmail($tainted_email);
        $cleaned_parameters['cleaned_role'] = $validator->validateUserOption($tainted_role);
    }
    else
    {
        $cleaned_parameters['user_exists'] = false;
        $cleaned_parameters['message'] = 'User does not exist';
        $cleaned_parameters['cleaned_user_name'] = '';
        $cleaned_parameters['cleaned_email'] = '';
        $cleaned_parameters['cleaned_role'] = '';
    }
    return $cleaned_parameters;
}

/**
 * Checks the role of the user to make sure they have authorization to edit a user
 * @param $app
 * @return string
 */
function CheckUserRole($app): string
{
    $role = '';
    $session_wrapper = $app->getContainer()->get('sessionWrapper');
    $session_model = $app->getContainer()->get('sessionModel');
    $session_model->setSessionWrapper($session_wrapper);
    $user_isLogged = $session_model->getStoredValues('isLoggedIn');
    if($user_isLogged === true){
        $user_id = $session_model->getStoredValues('user_id');

        try{
            $database_connection_settings = $app->getContainer()->get('doctrine_settings');
            $database_queries = $app->getContainer()->get('sqlQueries');
            $database_connection = DriverManager::getConnection($database_connection_settings);
            $queryBuilder = $database_connection->createQueryBuilder();
            $fetch_result = $database_queries::RetrieveUserRole($queryBuilder, $user_id);
            //var_dump($fetch_result[0]['role']);
            $validator = $app->getContainer()->get('validator');

            $role = $validator->sanitiseString($fetch_result[0]["role"]);
        }catch (exception $e){
            echo 'An error occurred: ' . $e->getMessage();
        }

    }
    else {
        $role = 'guest';
    }
    $user_role = $role;

    return $user_role;
}


