<?php
/**
 * Created by PhpStorm.
 * User: arron
 * Date: 06/12/2019
 * Time: 00:39
 */

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use Monolog\Logger;
use Monolog\Handler\StreamHandler;

$app->post('/processsendmessage', function(Request $request, Response $response) use($app)
{


    $logger = new Logger('sendMessage');
    $logger->pushHandler(new StreamHandler(LOG_FILE, Logger::INFO));


    $tainted_parameters = $request->getParsedBody();
    $session_id = session_id();
    $cleaned_parameters = cleanDataToSendParameters($app, $tainted_parameters);
    $send_result = sendMessageDetails($app, $logger, $cleaned_parameters);
    return $this->view->render($response,
        'send_message_result.html.twig',
        [
            'css_path' => CSS_PATH,
            'landing_page' => LANDING_PAGE,
            'action3' => ADMIN,
            'action4' => SEND,
            'action5' => DOWNLOAD,
            'action6' => STORE,
            'page_title' => APP_NAME,
            'page_heading_1' => APP_NAME,
            'page_heading_2' => "Send result",
            'send_result' => $send_result['send_result'],
            'message' => $send_result['message'],

            'sanitised_deviceMsisdn' => $cleaned_parameters['sanitised_deviceMsisdn'],
            'sanitised_switch1value' => $cleaned_parameters['sanitised_switch1Value'],
            'sanitised_switch2value' => $cleaned_parameters['sanitised_switch2Value'],
            'sanitised_switch3value' => $cleaned_parameters['sanitised_switch3Value'],
            'sanitised_switch4value' => $cleaned_parameters['sanitised_switch4Value'],
            'sanitised_temperature' => $cleaned_parameters['sanitised_temperature'],
            'sanitised_fandetail' => $cleaned_parameters['sanitised_fandetail'],
            'sanitised_keypad' => $cleaned_parameters['sanitised_keypad'],
            'sanitised_groupId' => $cleaned_parameters['sanitised_groupId']
        ]);

});

/**
 * Function to clean all data enter in the form by the user
 * @param $app
 * @param $tainted_parameters
 * @return array of validated details
 */
function cleanDataToSendParameters($app, $tainted_parameters)
{
    $cleaned_parameters = [];
    $validator = $app->getContainer()->get('validator');

    $tainted_username = $tainted_parameters['username'];
    $tainted_deviceMsisdn = $tainted_parameters['deviceMsisdn'];
    $tainted_switch1Value = $tainted_parameters['switch1detail'];
    $tainted_switch2Value = $tainted_parameters['switch2detail'];
    $tainted_switch3Value = $tainted_parameters['switch3detail'];
    $tainted_switch4Value = $tainted_parameters['switch4detail'];
    $tainted_fanSettings = $tainted_parameters['fandetail'];
    $tainted_temperature = $tainted_parameters['temperature'];
    $tainted_keypad = $tainted_parameters['keypad'];

    $cleaned_parameters['password'] = $tainted_parameters['password'];
    $cleaned_parameters['sanitised_username'] = $validator->sanitiseString($tainted_username);
    $cleaned_parameters['sanitised_deviceMsisdn'] = $validator->sanitiseString($tainted_deviceMsisdn);
    $cleaned_parameters['sanitised_switch1Value'] = $validator->validateRadioOption($tainted_switch1Value);
    $cleaned_parameters['sanitised_switch2Value'] = $validator->validateRadioOption($tainted_switch2Value);
    $cleaned_parameters['sanitised_switch3Value'] = $validator->validateRadioOption($tainted_switch3Value);
    $cleaned_parameters['sanitised_switch4Value'] = $validator->validateRadioOption($tainted_switch4Value);
    $cleaned_parameters['sanitised_fandetail'] = $validator->validateRadioOption($tainted_fanSettings);
    $cleaned_parameters['sanitised_temperature'] = $validator->validateTemperature($tainted_temperature);
    $cleaned_parameters['sanitised_keypad'] = $validator->validateKeypad($tainted_keypad);
    $cleaned_parameters['sanitised_groupId'] = $validator->sanitiseString('AN');


    return $cleaned_parameters;
}

/**
 * Function to send message details to the provided destination after user details check passes.
 * @param $app
 * @param Logger $logger allows for logs to be created when a new message is sent
 * @param $cleaned_parameters
 * @return mixed
 */
function sendMessageDetails($app, Logger $logger, $cleaned_parameters){
    $soap_wrapper = $app->getContainer()->get('soapWrapper');

    $SMS_model = $app->getContainer()->get('processMessageDetails');
    $SMS_model->setSoapWrapper($soap_wrapper);
    /*checks to make sure no validation failed*/
    if(!in_array(false, $cleaned_parameters)){
        $SMS_model->setSendParameters($cleaned_parameters);
        try{
            $SMS_model->sendMessage();
            $SMS_model->sendDeliveryMessage();
            $logger->info('A new message was sent', ['username' => $cleaned_parameters['sanitised_username'],
                'deviceMsisdn' => $cleaned_parameters['sanitised_deviceMsisdn'],
                'message' => ['Switch1Value' => $cleaned_parameters['sanitised_switch1Value'],
                    'Switch2Value' => $cleaned_parameters['sanitised_switch2Value'],
                    'Switch3Value' => $cleaned_parameters['sanitised_switch3Value'],
                    'Switch4Value' => $cleaned_parameters['sanitised_switch4Value'],
                    'Fan Settings' => $cleaned_parameters['sanitised_fandetail'],
                    'temperature' => $cleaned_parameters['sanitised_temperature'],
                    'keypad' => $cleaned_parameters['sanitised_keypad'],
                    'group_id' => $cleaned_parameters['sanitised_groupId']]]);
            $soap_result = $SMS_model->getResult();
            $send_result['message'] = 'Message Details Entered';
            if(strpos($soap_result, 'exception')){
                $send_result['send_result'] = 'An error occurred: ' .$soap_result->getMessage();
            }
            else{
                $send_result['send_result'] = 'Message was successfully sent. Message REF:' . $soap_result;
            }
        }catch (exception $e){
            $send_result['send_result'] = $SMS_model->getResult();
            $send_result['message'] = 'Something went wrong. Please try again '. $e->getMessage();
        }
    }else{
        if($cleaned_parameters['sanitised_username'] == false or $cleaned_parameters['password'] == false){
            $send_result['message'] = 'Please enter a valid username and password';
            $send_result['send_result'] = '';
        }
       else if($cleaned_parameters['sanitised_deviceMsisdn'] == false){
            $send_result['message'] = 'Please enter a destination number';
            $send_result['send_result'] = '';
        }
       else {
            $send_result['message'] = 'Please enter valid settings';
           $send_result['send_result'] = '';
       }
    }



    return $send_result;
}