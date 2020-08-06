<?php


namespace Messages;

//Class for message processing using soap. for example peek method
class messageDetailsModel
{
    private $username;
    private $password;
    private $count;
    private $deviceMsisdn;
    private $countryCode;
    //private $soap_call_parameters;
    private $message_id;
    private $message_content;
    private $switchSetting1;
    private $switchSetting2;
    private $switchSetting3;
    private $switchSetting4;
    private $fanSetting;
    private $temperature;
    private $keypad;
    private $group_id;
    private $result;
    private $soap_wrapper;
    private $message;
    private $isFault;

    public function __construct(){
        $this->username = '';
        $this->password = '';
        $this->count = '';
        $this->deviceMsisdn = '';
        $this->countryCode = '';
        $this->message_id = '';
        $this->message_content = [];
        $this->switchSetting1='';
        $this->switchSetting2='';
        $this->switchSetting3='';
        $this->switchSetting4='';
        $this->fanSetting = '';
        $this->temperature = '';
        $this->keypad = '';
        $this->group_id = '';
        $this->result = [];
        $this->soap_wrapper = null;
        $this->isFault = false;
        $this->message = '';
    }

    public function __destruct(){}

    /**
     * Function to set the soap wrapper
     * @param $soap_wrapper
     */
    public function setSoapWrapper($soap_wrapper){
        $this->soap_wrapper = $soap_wrapper;
    }

    /**
     * function to set the peek parameters that were fetched when retrieving messages
     * @param $peek_parameters
     */
    public function setPeekParameters($peek_parameters)
    {
        $this->username = $peek_parameters['cleaned_var_username'];
        $this->password = $peek_parameters['cleaned_var_password'];
        $this->count = $peek_parameters['cleaned_var_count'];
        $this->deviceMsisdn = $peek_parameters['cleaned_var_deviceMsisdn'];
        $this->countryCode = $peek_parameters['cleaned_var_countryCode'];
    }

    public function getIsFault(){
        return $this->isFault;
    }


    /**
     * Function to set the send parameters that are entered by the user to send
     * @param $cleaned_parameters
     */
    public function setSendParameters($cleaned_parameters)
    {
        $this->username = $cleaned_parameters['sanitised_username'];
        $this->password = $cleaned_parameters['password'];
        $this->deviceMsisdn = $cleaned_parameters['sanitised_deviceMsisdn'];
        $this->switchSetting1 = $cleaned_parameters['sanitised_switch1Value'];
        $this->switchSetting2 = $cleaned_parameters['sanitised_switch2Value'];
        $this->switchSetting3 = $cleaned_parameters['sanitised_switch3Value'];
        $this->switchSetting4 = $cleaned_parameters['sanitised_switch4Value'];
        $this->fanSetting = $cleaned_parameters['sanitised_fandetail'];
        $this->temperature = $cleaned_parameters['sanitised_temperature'];
        $this->keypad = $cleaned_parameters['sanitised_keypad'];
        $this->group_id = $cleaned_parameters['sanitised_groupId'];

        $message = new \StdClass();

       $message->s1 = $this->switchSetting1;
       $message->s2 = $this->switchSetting2;
       $message->s3 = $this->switchSetting3;
       $message->s4 = $this->switchSetting4;
       $message->fs = $this->fanSetting;
       $message->kp = $this->keypad;
       $message->temp = $this->temperature;
       $message->gId = $this->group_id;

       $this->message_content = $this->parseMessageToSend($message);
    }

    private function parseMessageToSend($message){
        $json_output = json_encode($message);
        return $json_output;
    }

    /**
     * Function to retrieve messages from the EE M2M server
     */
    public function retrieveMessages()
    {
        $message_details = [];

        $soap_client_handle = $this->soap_wrapper->createSoapClient();

        if ($soap_client_handle !== false) {
            $soap_function = 'peekMessages';
            $webservice_call_parameters = [
                 $this->username,
                 $this->password,
                 $this->count,
                 $this->deviceMsisdn,
                 $this->countryCode,
            ];
            try{
                $soapcall_result = $this->soap_wrapper->performSoapCall($soap_client_handle,$soap_function, $webservice_call_parameters);
                $this->isFault = false;
            }
            catch(\SoapFault $exception){
                $soapcall_result = $exception->getCode();
                $this->isFault = true;
            }


            $this->result = $soapcall_result;
        }
    }

    /**
     * Function to send a message using the soap client
     */
    public function sendMessage()
    {
        $soap_client_handle = $this->soap_wrapper->createSoapClient();

        if($soap_client_handle !== false){
            $soap_function = 'sendMessage';
            $webservice_call_parameters = [
                $this->username,
                $this->password,
                $this->deviceMsisdn,
                $this->message_content,
                true,
                'SMS',

            ];
            try{
                $soap_result = $this->soap_wrapper->performSoapCall($soap_client_handle, $soap_function, $webservice_call_parameters);
            }catch (\SoapFault $exception){
                $soap_result = $exception->getCode();
            }
            $this->result = $soap_result;
        }
    }

    public function sendDeliveryMessage()
    {
        $soap_client_handle = $this->soap_wrapper->createSoapClient();

        //$recipient = $this->deviceMsisdn;
        //$report = "$this->report + $recipient";

        if($soap_client_handle !== false){
            $soap_function = 'sendMessage';
            $webservice_call_parameters = [
                $this->username,
                $this->password,
                "07889556315",
                'message successful',
                false,
                'SMS'
            ];
            try{
                $soap_result = $this->soap_wrapper->performSoapCall($soap_client_handle, $soap_function, $webservice_call_parameters);
            }catch (\SoapFault $exception){
                $soap_result = $exception;
            }
            $this->result = $soap_result;
        }
    }



    /**
     * Function to get result of the send and retrieve function
     * @return array
     */
    public function getResult()
    {
        return $this->result;
    }



}