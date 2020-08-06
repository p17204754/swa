<?php
/**
 * Created by PhpStorm.
 * User: p17175791
 * Date: 15/11/2019
 * Time: 19:34
 */

namespace Messages;

class SoapWrapper
{

    public function __construct(){}
    public function __destruct(){}

    /**
     * Function to create a soap client using the $wsdl
     * @return bool|\SoapClient
     */
    public function createSoapClient()
    {
        $soap_client_handle = false;

        $soapclient_settings = ['trace' => true, 'exceptions' => true];
        $wsdl = WSDL;

        try
        {
            $soap_client_handle = new \SoapClient($wsdl, $soapclient_settings);

        }
        catch (\SoapFault $exception)
        {
            trigger_error($exception);
        }

        return $soap_client_handle;
    }

    /**
     * Function to perform soap calls using the soap client method __soapCall
     *
     * @param $soap_client
     * @param $webservice_function
     * @param $webservice_parameters
     * @return \Exception|null|\SoapFaul
     */
    public function performSoapCall($soap_client, $webservice_function, $webservice_parameters)
    {
        $soap_call_result = null;
        $raw_xml = '';

        if ($soap_client)
        {
            try
            {
                $webservice_call_result = $soap_client->__soapCall($webservice_function, $webservice_parameters);
                $soap_call_result = $webservice_call_result;
            }
            catch (\SoapFault $exception)
            {
                $soap_call_result = $exception;
            }
        }
        return $soap_call_result;
    }


}