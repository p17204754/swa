<?php
/**
 * Created by PhpStorm.
 * User: arron
 * Date: 03/12/2019
 * Time: 23:23
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Doctrine\DBAL\DriverManager;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$logged_user = getLoggedInUserRole($app);
$app->get('/adminInterface', function(Request $request, Response $response) use($app)
{
    $session_id = session_id();
    $all_users = getUsers($app);



    $logger = new Logger('adminInterface');
    $logger->pushHandler(new StreamHandler(LOG_FILE, Logger::INFO));

    $logger->info('Admin interface visited', [ 'user_id ' => getUserId($app)]);


    return $this->view->render($response,
        'admin_interface.html.twig',
        [
            'css_path' => CSS_PATH,
            'landing_page' => LANDING_PAGE,
            'method' => 'get',
            'action' => 'adminInterface/edituser',
            'action2' => 'adminInterface/deleteuser',
            'action3' => ADMIN,
            'action4' => SEND,
            'action5' => DOWNLOAD,
            'action6' => STORE,
            'page_title' => APP_NAME,
            'page_heading_1' => APP_NAME,
            'page_heading_2' => "Admin interface",
            'message' => $all_users['message'],
            'users' => $all_users['users']
        ]);

})->setName('admin_interface')->add(new \Messages\Authorization($logged_user));

/**
 * Gets all users from the database
 * @param $app
 * @return mixed
 * @throws \Doctrine\DBAL\DBALException
 */
function getUsers($app)
{
    $database_connection_settings = $app->getContainer()->get('doctrine_settings');
    $database_queries = $app->getContainer()->get('sqlQueries');
    $database_connection = DriverManager::getConnection($database_connection_settings);


    $queryBuilder = $database_connection->createQueryBuilder();
    try{
        $fetch_result['users'] = $database_queries::RetrieveAllUsers($queryBuilder);
        $fetch_result['message'] = 'Users fetched successfully';
    }catch(exception $e){
        $fetch_result['users'] = [];
        $fetch_result['message'] = 'An error occurred: ' . $e->getMessage();
    }


    //var_dump($fetch_result);
    return $fetch_result;
}

/**
 * Checks the role of the logged in user to make sure they have authorization to use
 * the admin panel
 * @param $app
 * @return string
 */
function getLoggedInUserRole($app): string
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
        echo 'An Error occurred: ' . $e->getMessage();
    }
    }
    else {
        $role = 'guest';
    }
    $user_role = $role;
    return $user_role;
}

function getUserId($app){
    $session_wrapper = $app->getContainer()->get('sessionWrapper');
    $session_model = $app->getContainer()->get('sessionModel');
    $session_model->setSessionWrapper($session_wrapper);

    $user_id = $session_model->getStoredValues('user_id');
    return $user_id;
}