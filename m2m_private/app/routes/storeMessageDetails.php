<?php
/**
 * Created by PhpStorm.
 * User: p17175791
 * Date: 27/11/2019
 * Time: 16:21
 */


use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Doctrine\DBAL\DriverManager;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$app->post(
    '/storemessagedetails',
    function(Request $request, Response $response) use ($app)
    {


        $logger = new Logger('Store message');
        $logger->pushHandler(new StreamHandler(LOG_FILE, Logger::DEBUG));
        //$logger->pushHandler(new StreamHandler($log_file, Logger::WARNING));




        $tainted_parameters = $request->getParsedBody();
        //var_dump($tainted_parameters);
        $cleaned_parameters = cleanStorageParameters($app, $tainted_parameters);
        $store_result = storeMessageIntoDatabase($app, $cleaned_parameters, $logger);
        //var_dump($store_result);
        #$sid = session_id();

        $storage_result_message = '';

        return $this->view->render($response,
            'display_storage_result.html.twig',
            [
                'landing_page' => $_SERVER["SCRIPT_NAME"],
                'action3' => ADMIN,
                'action4' => SEND,
                'action5' => DOWNLOAD,
                'action6' => STORE,
                'css_path' => CSS_PATH,
                'page_title' => APP_NAME,
                'page_heading_1' => APP_NAME,
                'page_heading_2' => 'Result',
                'store_result_message' => $store_result,
                'storage_text' => 'The values entered were:',
                'message_number' => $cleaned_parameters['sanitised_number'],
                'message_destination' => $cleaned_parameters['sanitised_destination'],
                'message_time' => $cleaned_parameters['sanitised_time'],
                'message_bearer' => $cleaned_parameters['sanitised_bearer'],
                'message_ref' => $cleaned_parameters['sanitised_ref'],
                'message_switch1' => $cleaned_parameters['sanitised_switch1'],
                'message_switch2' => $cleaned_parameters['sanitised_switch2'],
                'message_switch3' => $cleaned_parameters['sanitised_switch3'],
                'message_switch4' => $cleaned_parameters['sanitised_switch4'],
                'message_fan' => $cleaned_parameters['sanitised_fanSettings'],
                'message_temp' => $cleaned_parameters['sanitised_temp'],
                'message_group' => $cleaned_parameters['sanitised_group'],
                'message_keypad' => $cleaned_parameters['sanitised_keypad'],
                'storage_result_message' => $storage_result_message,
            ]);

    });


/**
 * Function to clear selected message details.
 * @param $app
 * @param $tainted_parameters
 * @return array
 */
function cleanStorageParameters($app, $tainted_parameters): array
{
    $cleaned_parameters = [];
    $message_to_store = '';
    #$tainted_server_type = $tainted_parameters['server_type'];
    $validator = $app->getContainer()->get('validator');
    $session_wrapper = $app->getContainer()->get('sessionWrapper');
    $tainted_value = $session_wrapper->getSessionVar('downloaded_messages');

    foreach($tainted_value as $message){
        if($message['message_id'] == $tainted_parameters['store_message']){
            $message_to_store = $message;
        }
    }
    date_default_timezone_set('Europe/London');
    $newDate = date_create($message_to_store['RECEIVEDTIME']);
    $date = date_format($newDate,'Y-m-d H:i:s');

    $cleaned_parameters['sanitised_number'] = $validator->sanitiseString($message_to_store['SOURCEMSISDN']);
    $cleaned_parameters['sanitised_destination'] = $validator->sanitiseString($message_to_store['DESTINATIONMSISDN']);
    $cleaned_parameters['sanitised_time'] = $date;
    $cleaned_parameters['sanitised_bearer'] = $message_to_store['BEARER'];
    $cleaned_parameters['sanitised_ref'] = $message_to_store['MESSAGEREF'];
    $cleaned_parameters['sanitised_switch1'] = $validator->sanitiseString($message_to_store['MESSAGE']['s1']);
    $cleaned_parameters['sanitised_switch2'] = $validator->sanitiseString($message_to_store['MESSAGE']['s2']);
    $cleaned_parameters['sanitised_switch3'] = $validator->sanitiseString($message_to_store['MESSAGE']['s3']);
    $cleaned_parameters['sanitised_switch4'] = $validator->sanitiseString($message_to_store['MESSAGE']['s4']);
    $cleaned_parameters['sanitised_fanSettings'] = $validator->sanitiseString($message_to_store['MESSAGE']['fs']);
    $cleaned_parameters['sanitised_temp'] = $validator->sanitiseString($message_to_store['MESSAGE']['temp']);
    $cleaned_parameters['sanitised_keypad'] = $validator->sanitiseString($message_to_store['MESSAGE']['kp']);
    $cleaned_parameters['sanitised_group'] = $validator->sanitiseString($message_to_store['MESSAGE']['gId']);
    $cleaned_parameters['message_id'] = $message_to_store['message_id'];
    #$cleaned_parameters['cleaned_password'] = $tainted_parameters['password'];
    #$cleaned_parameters['cleaned_server_type'] = $validator->validateServerType($tainted_server_type);
    return $cleaned_parameters;
}

/**
 * Function to store selected message details to database
 * @param $app
 * @param $cleaned_parameters
 * @param $logger
 * @return string
 */
function storeMessageIntoDatabase($app, $cleaned_parameters, $logger){
    $message_to_output = '';
    $session_model = $app->getContainer()->get('sessionModel');


    $database_connection_settings = $app->getContainer()->get('doctrine_settings');
    $database_queries = $app->getContainer()->get('sqlQueries');
    try{
        $database_connection =  DriverManager::getConnection($database_connection_settings);

        $queryBuilder = $database_connection->createQueryBuilder();
        $checkDuplicates = $database_queries::getCheckMessageItems($queryBuilder, $cleaned_parameters);

        if(count($checkDuplicates) > 0){
            $message_to_output = 'Message has already been stored';
        }
        elseif (count($checkDuplicates) == 0){
            try{
                $database_queries::StoreMessageData($queryBuilder, $cleaned_parameters);
                $message_to_output = 'The message was successfully stored!';
                $session_model->unsetDownloadedMessages('download_messages');
                $logger->info('A new message was stored', $cleaned_parameters);
            }catch (exception $e){
                $message_to_output = 'There was a problem storing this message: '. $e->getMessage() .' Please try again';
                $logger->warning('Something went wrong when trying to store a new message', $cleaned_parameters);
            }
        }
    }catch (exception $e){
        $message_to_output = 'An error occurred when trying to store your message' .$e->getMessage();
    }

    return $message_to_output;
}