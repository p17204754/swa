<?php
/**
 * Created by PhpStorm.
 * User: arron
 * Date: 17/12/2019
 * Time: 14:03
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$app->post('/processretrievemessage', function(Request $request, Response $response) use ($app)
{


    $logger = new Logger('retrieveMessage');
    $logger->pushHandler(new StreamHandler(LOG_FILE, Logger::INFO));

    $tainted_parameters = $request->getParsedBody();
    $cleaned_parameters = cleanReceivedParameters($app, $tainted_parameters);
    $message_detail_result = returnMessage($app, $cleaned_parameters, $logger);
    return $this->view->render($response,
        'retrieve_messages_result.html.twig',
        [
            'css_path' => CSS_PATH,
            'landing_page' => LANDING_PAGE,
            'action3' => ADMIN,
            'action4' => SEND,
            'action5' => DOWNLOAD,
            'action6' => STORE,
            'initial_input_box_value' => null,
            'page_title' => APP_NAME,
            'page_heading_1' => APP_NAME,
            'page_heading_2' => 'Received messages',
            'var_username' => $cleaned_parameters['cleaned_var_username'],
            'var_count' => $cleaned_parameters['cleaned_var_count'],
            'var_deviceMsisdn' => $cleaned_parameters['cleaned_var_deviceMsisdn'],
            'var_countryCode' => $cleaned_parameters['cleaned_var_countryCode'],
            'result' => $message_detail_result['messages'],
            'message' => $message_detail_result['results'],
            's1' => $message_detail_result['messages'],
            'view_stored' => 'storedmessages',
            'method' => 'post',
            'action' => 'storemessagedetails'
        ]);
});
/**
 * Function to clean user details.
 * @param $app
 * @param $tainted_parameters
 * @return array
 */
function cleanReceivedParameters($app, $tainted_parameters)
{
    $cleaned_parameters = [];

    $validator = $app->getContainer()->get('validator');

    $tainted_var_username = $tainted_parameters['var_username'];
    $tainted_var_password = $tainted_parameters['var_password'];
    $tainted_var_count = $tainted_parameters['var_count'];
    $tainted_var_deviceMsisdn = $tainted_parameters['var_deviceMsisdn'];
    $tainted_var_countryCode = $tainted_parameters['var_countryCode'];

    $cleaned_parameters['cleaned_var_username'] = $validator->sanitiseString($tainted_var_username);
    $cleaned_parameters['cleaned_var_password'] = $tainted_var_password;
    $cleaned_parameters['cleaned_var_count'] = $validator->sanitiseString($tainted_var_count);
    $cleaned_parameters['cleaned_var_deviceMsisdn'] = $validator->sanitiseString($tainted_var_deviceMsisdn);
    $cleaned_parameters['cleaned_var_countryCode'] = $validator->validateCountryCode($tainted_var_countryCode);

    return $cleaned_parameters;
}

//function storeInSession($app, $clean_parameters){
    //$session_wrapper = $app->getContainer()->get('sessionWrapper');
    //$session_model = $app->getContainer()->get('sessionModel');
    //$store_result = '';
    //$session_model->setSessionWrapper($session_wrapper);
    //$session_model->setMessageToStore($clean_parameters[]);
   // $session_model->storeMessageDataInSessionFile();
    //$store_result = $session_model->getStorageResult();
   // return $store_result;
//}
/**
 * Function to parse all downloaded messages after checking that entered user details are valid.
 * @param $app
 * @param $cleaned_parameters  -user details
 * @param $logger  -allows for logs to be created when new messages downloaded
 * @return array|string
 */
function returnMessage($app, array $cleaned_parameters, $logger)
{
    $parsed_messages = [];
    $soap_wrapper = $app->getContainer()->get('soapWrapper');
    $SMS_model = $app->getContainer()->get('processMessageDetails');

    $session_model = $app->getContainer()->get('sessionModel');
    /*checks to make sure no validation failed*/
    if(!in_array(false, $cleaned_parameters)){
        try{
            $xml_parser = $app->getContainer()->get('xml_parser');
            $SMS_model->setSoapWrapper($soap_wrapper);
            $SMS_model->setPeekParameters($cleaned_parameters);
            $SMS_model->retrieveMessages();
        }catch (exception $e){
            echo 'An error occurred';
        }
        //this will cause an error
        $validator = $app->getContainer()->get('validator');
        $messages = $SMS_model->getResult();
        if(is_array($messages)){
            for($i = 0; $i < count($messages); $i++)
            {
                //var_dump($messages[$i]);
                $validator->validateDownloadedData($messages[$i]);
                $xml_parser->setXmlStringToParse($messages[$i]);
                $xml_parser->parseTheXmlString();
                $parsed_message_detail_result = $xml_parser->getParsedData();
                /*add message id so we can identify each message*/
                $parsed_message_detail_result += array('message_id' => $i);
                $parsed_messages[$i] =  $parsed_message_detail_result;
            }
            //var_dump($parsed_messages);

            //var_dump($session_model->getStorageResult());

            //var_dump($items);

            if($messages == false){
                $message_detail_result['results'] = 'not available';
                $logger->warning('Something went wrong when downloading messages:');
            }
            $message_detail_result['messages'] =  checkForMessageCode($app, $parsed_messages);
            if(count($message_detail_result['messages']) > 0){
                $message_detail_result['results'] = 'Messages found: ' . count($message_detail_result['messages']);
            }
            else{
                $message_detail_result['results'] = 'No messages found';
                $message_detail_result['messages'] = [];
            }


            $session_model->setDownloadedMessages($message_detail_result['messages']);
            $session_model->storeMessageDataInSessionFile();
            $logger->info('Messages were downloaded. Number of messages download:', [count($message_detail_result['messages'])]);
        }
        else{
            $message_detail_result['results'] = 'An error occurred. Please try again: ' . $messages->getMessage();
            $message_detail_result['messages'] = [];
            //$validator->validateDownloadedData($messages);
            //$xml_parser->setXmlStringToParse($messages);
            //$xml_parser->parseTheXmlString();
        }
    }else{
        if($cleaned_parameters['cleaned_var_username'] == false or $cleaned_parameters['cleaned_var_password'] == false){
            $message_detail_result['results'] = 'Please enter a valid EE username and password ';
            $message_detail_result['messages'] = [];
        }
        else if($cleaned_parameters['cleaned_var_count'] == false){
            $message_detail_result['results'] = 'Please enter a valid number of messages to download ';
            $message_detail_result['messages'] = [];
        }
        else if($cleaned_parameters['cleaned_var_deviceMsisdn'] == false){
            $message_detail_result['results'] = 'Please enter a valid phone number ';
            $message_detail_result['messages'] = [];
        }
        else if($cleaned_parameters['cleaned_var_countryCode'] == false){
            $message_detail_result['results'] = 'Please enter a valid country code';
            $message_detail_result['messages'] = [];
        }
        else{
            $message_detail_result['results'] = 'Something went wrong. Please try again';
            $message_detail_result['messages'] = [];
        }
    }

    return $message_detail_result;
}

/**
 * Function to filter messages based on group id
 * @param $messages
 * @return mixed
 */
function checkForMessageCode($app, $messages){
    $checked_messages = [];
    foreach($messages as $message){
        $message_output = json_decode($message['MESSAGE']);
        //var_dump($message_output);

        if(isset($message_output->gId)){
            if(strpos($message_output->gId,"AN") !== false) {
                //var_dump('true');
                //var_dump($message);
                $message['MESSAGE'] =
                    ['s1' => $message_output->s1, 's2' => $message_output->s2,
                        's3' => $message_output->s3, 's4' => $message_output->s4, 'fs' => $message_output->fs,
                        'temp' => $message_output->temp, 'kp' => $message_output->kp, 'gId' => $message_output->gId];
                array_push($checked_messages, $message);
            }
            else{
                unset($message);
            }
        }else{
            unset($message);
        }

    }

   return $checked_messages;
}