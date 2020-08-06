<?php
/**
 * Created by PhpStorm.
 * User: arron
 * Date: 06/12/2019
 * Time: 00:38
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
$app->get('/sendmessage', function(Request $request, Response $response) use($app)
{
    $session_id = session_id();


    $logger = new Logger('SendMessage');
    $logger->pushHandler(new StreamHandler(LOG_FILE, Logger::INFO));

    $logger->info('Send message page visited');

    return $this->view->render($response,
        'send_message_form.html.twig',
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
            'page_heading_2' => "Send a message",
        ]);

});