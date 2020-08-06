<?php

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app->get('/', function(Request $request, Response $response) use($app)
{
    $session_id = session_id();
    $loginAction = checkLogIn($app);
    return $this->view->render($response,
        'homepage.html.twig',
        [
            'css_path' => CSS_PATH,
            'landing_page' => LANDING_PAGE,
            'method' => 'post',
            'action2' => $loginAction['action'],
            'action3' => ADMIN,
            'action4' => SEND,
            'action5' => DOWNLOAD,
            'action6' => STORE,
            'logInAction' => 'processuserlogin',
            'regAction' => 'registernewuser',
            'actionButtonText2' => $loginAction['text'],
            'logInValue' => $loginAction['value'],
            'method2' => $loginAction['method'],
            'page_title' => APP_NAME,
            'page_heading_1' => APP_NAME,
            'page_heading_2' => "Home",
        ]);

})->setName('homepage');

/**
 * Function to check whether the user is logged in
 * @param $app
 * @return array (array items based on whether user is logged in or not)
 */

function checkLogIn($app)
{
    $loginAction = [];
    $session_wrapper = $app->getContainer()->get('sessionWrapper');
    $session_model = $app->getContainer()->get('sessionModel');
    $session_model->setSessionWrapper($session_wrapper);
    $login_value = $session_model->getStoredValues('isLoggedIn');


    if($login_value == true)
    {
        $loginAction['action'] = 'processuserlogout';
        $loginAction['text'] = 'Logout';
        $loginAction['method'] = 'post';
        $loginAction['value'] = true;
    }
    else{
        $loginAction['action'] = 'loginuser';
        $loginAction['text'] = 'Login';
        $loginAction['method'] = 'get';
        $loginAction['value'] = false;
    }

    return $loginAction;
}