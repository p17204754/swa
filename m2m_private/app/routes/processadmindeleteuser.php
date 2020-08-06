<?php
/**
 * Created by PhpStorm.
 * User: arron
 * Date: 20/12/2019
 * Time: 15:39
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Doctrine\DBAL\DriverManager;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$logged_user = checkUserRole($app);
$app->post('/adminInterface/processadmindeleteuser', function(Request $request, Response $response) use ($app)
{

    $logger = new Logger('adminDeleteUser');
    $logger->pushHandler(new StreamHandler(LOG_FILE, Logger::INFO));
    $logger->pushHandler(new StreamHandler(LOG_FILE, Logger::WARNING));



    $tainted_parameters = $request->getParsedBody();
    $session_id = session_id();
    //var_dump($session_id);
    //var_dump($tainted_parameters);
    // var_dump($clean_parameters);
    //var_dump($hashed_password);
    $save_result = deleteUserDetails($app, $tainted_parameters['user_id'],$logger);
    $store_result = [];

    return $this->view->render($response,
        'admin_delete_user_result.html.twig',
        [
            'css_path' => CSS_PATH,
            'landing_page' => LANDING_PAGE,
            'page_title' => APP_NAME,
            'page_heading_1' => APP_NAME,
            'page_heading_2' => "Save result",
            'save_result' => $save_result['outcome'],
            //'login_result' => $register_result,
        ]);
})->setName('admin_delete_process')->add(new Messages\Authorization($logged_user));


/**
 * @param $app
 * @param $user_id of the user to be deleted
 * @param $logger allows logs to be created when a user is deleted
 * @return array contains information about the outcome of the process
 */
function deleteUserDetails($app, $user_id, $logger): array
{
    $deletion_result = [];
    $store_result = [];

    //$flash = $app->getContainer()->get('flash');
    try{
        $database_connection_settings = $app->getContainer()->get('doctrine_settings');
        $database_queries = $app->getContainer()->get('sqlQueries');
        $database_connection =  DriverManager::getConnection($database_connection_settings);

        $queryBuilder = $database_connection->createQueryBuilder();

        $deletion_result['outcome'] = $database_queries::deleteUser($queryBuilder, $user_id);



        if($deletion_result['outcome'] == 1 )
        {
            //$store_result['message'] = $flash->addMessage('Test', 'SUCCESS');
            $store_result['outcome'] = 'User was successfully deleted!';
            $logger->info('User deleted with the following ID', ['user_id' => $user_id]);
        }
        else
        {
            //$store_result['message'] = $flash->addMessage('Test', 'ERROR');
            $store_result['outcome'] = 'There was a problem deleting the selected user. Please try again';
            $logger->warning('Could not delete the following ID', ['user_id' => $user_id]);
        }

    }
    catch(exception $e){
     $store_result = ' An error occurred please try again ' . $e->getCode();
    }

    return $store_result;
}