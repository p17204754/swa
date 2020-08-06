<?php
/**
 * Created by PhpStorm.
 * User: arron
 * Date: 31/12/2019
 * Time: 13:47
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Doctrine\DBAL\DriverManager;

$app->get('/storedmessages', function(Request $request, Response $response) use($app)
{
    $session_id = session_id();
    $stored_messages = getStoredMessages($app);
    return $this->view->render($response,
        'view_stored_messages.html.twig',
        [
            'css_path' => CSS_PATH,
            'landing_page' => LANDING_PAGE,
            'action3' => ADMIN,
            'action4' => SEND,
            'action5' => DOWNLOAD,
            'action6' => STORE,
            'method' => 'post',
            'action' => 'processsendmessage',
            'page_title' => APP_NAME,
            'page_heading_1' => APP_NAME,
            'page_heading_2' => "Stored messages",
            'stored_messages' => $stored_messages['items'],
            'error_messages' => $stored_messages['error']
        ]);

});


/**
 * Function to get stored messages from the database
 * @param $app
 * @return mixed
 */
function getStoredMessages($app):array
{
    try{
        $database_connection_settings = $app->getContainer()->get('doctrine_settings');
        $database_queries = $app->getContainer()->get('sqlQueries');
        $database_connection = DriverManager::getConnection($database_connection_settings);


        $queryBuilder = $database_connection->createQueryBuilder();
        $fetch_result['items'] = $database_queries::getStoredMessages($queryBuilder);
        $fetch_result['error'] = '';

        if(sizeof($fetch_result['items'] ) == 0){
                $fetch_result['error'] = 'No messages were found';
            }

    }
    catch (exception $e){
        $fetch_result['items'] = [];
        $fetch_result['error'] = 'An error occurred ' . $e->getMessage();
    }

    return $fetch_result;
}