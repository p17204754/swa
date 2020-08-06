<?php
/**
 * Created by PhpStorm.
 * User: arron
 * Date: 03/12/2019
 * Time: 01:08
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Doctrine\DBAL\DriverManager;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;


$app->post('/processuserlogout', function(Request $request, Response $response) use ($app)
{

    $logger = new Logger('userLogout');
    $logger->pushHandler(new StreamHandler(LOG_FILE, Logger::INFO));


    $session_id = session_id();
    $result = processLogOut($app, $logger);

    return $this->view->render($response,
        'logout_user.html.twig',
        [
            'css_path' => CSS_PATH,
            'landing_page' => LANDING_PAGE,
            'action3' => ADMIN,
            'action4' => SEND,
            'action5' => DOWNLOAD,
            'action6' => STORE,

            //'method' => 'post',
            //'action' => 'processloginuser',
            'page_title' => APP_NAME,
            'page_heading_1' => APP_NAME,
            'page_heading_2' => "",
            'login_result' => $result,
        ]);
});

/**
 * Function to log user out of account and clear session
 * @param $app
 * @param Logger $logger
 * @return string
 */
function processLogOut($app, Logger $logger)
{
    $store_result = '';
    $session_wrapper = $app->getContainer()->get('sessionWrapper');
    $session_model = $app->getContainer()->get('sessionModel');
    $session_model->setSessionWrapper($session_wrapper);
    $session_model->unsetLoginStatus();
    $result = $session_model->getStoredValues('isLoggedIn');
    $user_id = $session_model->getStoredValues('user_id');
    $username = $session_model->getStoredValues('user_name');

    if($result !== true && $user_id !== true){
        $store_result = 'User was logged out';
        $logger->info('User logged out', ['username' => $username]);
        $session_model-> unsetUsername();
    }else{
        $store_result = 'Could not log user out';
    }
    return $store_result;
}