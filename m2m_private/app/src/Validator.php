<?php
/**
 * Created by PhpStorm.
 * User: p17204754
 * Date: 06/11/2019
 * Time: 14:41
 */

namespace Messages;

class Validator
{
    public function __construct() { }

    public function __destruct() { }

    /**
     * Validates that a given value is a string
     * @param $string_to_sanitise
     * @return bool|mixed
     */
    public function sanitiseString($string_to_sanitise)
    {
        $sanitised_string = false;

        if (!empty($string_to_sanitise))
        {
            $sanitised_string = filter_var($string_to_sanitise, FILTER_SANITIZE_STRING, FILTER_FLAG_NO_ENCODE_QUOTES);
        }
        return $sanitised_string;
    }

    /**
     * validates that a given value is an email
     * @param string $email_to_sanitise
     * @return string
     */
    public function sanitiseEmail(string $email_to_sanitise): string
    {
        $cleaned_email = false;

        if (!empty($email_to_sanitise))
        {
            $sanitised_email = filter_var($email_to_sanitise, FILTER_SANITIZE_EMAIL);
            $cleaned_email = filter_var($sanitised_email, FILTER_VALIDATE_EMAIL);
        }
        return $cleaned_email;
    }

    /**
     * Validates that a value selected using a radio option is available
     * @param $option_to_check
     * @return bool
     */
    public function validateRadioOption($option_to_check)
    {
        $checked_radio_option = false;
        $option_types = OPTION_TYPES;

        if(in_array($option_to_check, $option_types) === true)
        {
            $checked_radio_option = $option_to_check;
        }

        return $checked_radio_option;
    }

    /**
     * Validates user types against a entered user input
     * @param $option_to_check
     * @return bool
     */
    public function validateUserOption($option_to_check)
    {
        $checked_radio_option = false;
        $option_types = USER_TYPES;

        if(in_array($option_to_check, $option_types) === true)
        {
            $checked_radio_option = $option_to_check;
        }

        return $checked_radio_option;
    }


    /**
     * Validates downloaded data
     * used when retrieving messages from the EE M2M server
     * @param $tainted_data
     * @return mixed|string
     */

    public function validateDownloadedData($tainted_data)
    {
        $validated_string_data = '';

        $validated_string_data = filter_var($tainted_data, FILTER_SANITIZE_STRING);

        return $validated_string_data;
    }

    /**
     * Used to validate date
     * @param $tainted_data date to be validated
     * @return bool
     */
    public function validateDate($tainted_data)
    {
        $validated_date_data = false;
        if(empty($tainted_data)){
            $tainted_date = explode('/', $tainted_data);
            $validated_date_data = checkdate($tainted_date[0], $tainted_date[1], $tainted_date[2]);
        }


        return $validated_date_data;
    }

    /**
     * Used to validate temperature send by messages
     * @param $tainted_data temperature to be validated
     * @return bool
     */
    public function validateTemperature($tainted_data)
    {

        if($tainted_data <= 80 and $tainted_data > 0)
        {
            $validated_temp_value = $tainted_data;
        }
        else{
            $validated_temp_value = false;
        }

        return $validated_temp_value;
    }

    /**
     * Validates country code for retrieving messages
     * @param $tainted_data
     * @return bool|int
     */
    public function validateCountryCode($tainted_data)
    {

        if($tainted_data > 0)
        {
            $validated_code_value = $tainted_data;
        }
        else{
            $validated_code_value = false;
        }
        return $validated_code_value;
    }

    /**
     * validate keypad number making sure it is numeric
     * @param $tainted_data number to valid
     * @return bool|int|string
     */
    public function validateKeypad($tainted_data)
    {
        $validated_keypad_value = false;

        if(is_numeric($tainted_data)){
            $validated_keypad_value = $tainted_data;
        };

        return $validated_keypad_value;
    }
}