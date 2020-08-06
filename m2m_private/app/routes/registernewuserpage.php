<?php
/**
 * Created by PhpStorm.
 * User: arron
 * Date: 30/11/2019
 * Time: 18:09
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;



$app->get('/registernewuser', function(Request $request, Response $response)
{
    $session_id = session_id();
    return $this->view->render($response,
        'register_user.html.twig',
        [
            'css_path' => CSS_PATH,
            'landing_page' => LANDING_PAGE,
            'action3' => ADMIN,
            'action4' => SEND,
            'action5' => DOWNLOAD,
            'action6' => STORE,
            'method' => 'post',
            'action' => 'processregisteruser',
            'page_title' => APP_NAME,
            'page_heading_1' => APP_NAME,
            'page_heading_2' => "Register",
        ]);

})->setName('registernewuser');