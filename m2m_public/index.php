<?php
/**
 * Created by PhpStorm.
 * User: p17204754
 * Date: 06/11/2019
 * Time: 14:37
 */
ini_set('xdebug.trace_output_name', 'temperatures');
ini_set('display_errors', 'On');
ini_set('html_errors', 'On');
ini_set('xdebug.trace_format', 1);

if (function_exists(xdebug_start_trace()))
{
    xdebug_start_trace();
}

include 'includes/m2m_private/bootstrap.php';

if (function_exists(xdebug_stop_trace()))
{
    xdebug_stop_trace();
}

