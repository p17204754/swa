<?php
/**
 * Created by PhpStorm.
 * User: arron
 * Date: 20/12/2019
 * Time: 03:06
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Doctrine\DBAL\DriverManager;
use \Slim\Router as Router;

$logged_user = checkUserRole($app);
$app->get('/adminInterface/deleteuser', function(Request $request, Response $response) use($app)
{

    $route = $request->getAttribute('route');
    $user_id = $request->getQueryParams('user_id');


    //$route->getArgument('user_id');
    $session_id = session_id();
    $tainted_details = getSelectedUserDetails($app, $user_id);
    $cleaned_details = cleanUserDetailsToDelete($tainted_details['items'], $app);
    return $this->view->render($response,
        'admin_delete_user.html.twig',
        [
            'css_path' => CSS_PATH,
            'landing_page' => LANDING_PAGE,
            'method' => 'post',
            'action' => 'processadmindeleteuser',
            'page_title' => APP_NAME,
            'page_heading_1' => APP_NAME,
            'page_heading_2' => "Delete user",
            'user_id' => $user_id['user_id'],
            'message' => $cleaned_details['message'],
            'user_name' => $cleaned_details['cleaned_user_name'],
            'email' => $cleaned_details['cleaned_email'],
            'role' => $cleaned_details['cleaned_role'],
            'user_exists' => $cleaned_details['user_exists'] ,
            'back' => '',
        ]);

})->setName('admin_delete_user')->add(new \Messages\Authorization($logged_user));

/**
 * get selected user details from the database that needed to be deleted
 * @param $app
 * @param $user_id
 * @return mixed
 * @throws \Doctrine\DBAL\DBALException
 */
function getSelectedUserDetails($app, $user_id)
{
    $id = $user_id['user_id'];

    $database_connection_settings = $app->getContainer()->get('doctrine_settings');
    $database_queries = $app->getContainer()->get('sqlQueries');
    $database_connection = DriverManager::getConnection($database_connection_settings);


    $queryBuilder = $database_connection->createQueryBuilder();
    try{
        $fetch_result = $database_queries:: getUserDataToEdit($queryBuilder, $id);
    }catch (exception $e){
        $fetch_result = [];
        echo 'An error occurred: ' . $e->getMessage();
    }

    return $fetch_result;
}

/**
 * Clean the user details that are to be deleted in the database
 * @param $tainted_details
 * @param $app
 * @return array
 */
function cleanUserDetailsToDelete($tainted_details,  $app)
{
    $cleaned_parameters = [];
    //$test = $app->getContainer()->get('databaseWrapper');
    $validator = $app->getContainer()->get('validator');

    if(!empty($tainted_details))
    {
        $tainted_user_name = $tainted_details[0]['user_name'];
        $tainted_email = $tainted_details[0]['email'];
        $tainted_role = $tainted_details[0]['role'];

        $cleaned_parameters['user_exists']= true;
        $cleaned_parameters['message'] = 'Are you sure you want to delete the selected user?';
        $cleaned_parameters['cleaned_user_name'] = $validator->sanitiseString($tainted_user_name);
        $cleaned_parameters['cleaned_email'] = $validator->sanitiseEmail($tainted_email);
        $cleaned_parameters['cleaned_role'] = $validator->sanitiseString($tainted_role);
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