<?php
/**
 * Created by PhpStorm.
 * User: arron
 * Date: 17/12/2019
 * Time: 14:01
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
$app->get('/retrievemessage', function(Request $request, Response $response) use($app)
{
    $session_id = session_id();



    $logger = new Logger('ReceiveMessage');
    $logger->pushHandler(new StreamHandler(LOG_FILE, Logger::INFO));

    $logger->info('Receive message page visited');

    return $this->view->render($response,
        'retrieve_messages_form.html.twig',
        [
            'css_path' => CSS_PATH,
            'landing_page' => LANDING_PAGE,
            'action3' => ADMIN,
            'action4' => SEND,
            'action5' => DOWNLOAD,
            'action6' => STORE,
            'method' => 'post',
            'action' => 'processretrievemessage',
            'page_title' => APP_NAME,
            'page_heading_1' => APP_NAME,
            'page_heading_2' => "Retrieve messages",
        ]);

});