<?php

namespace Moloni\ES\Controllers\Models;

class Error
{
    private static $request;
    private static $error;

    /**
     * Adds an error to the errors array
     *
     * @param $errorMsg string msg of the error
     */
    public static function addError($errorMsg)
    {
        self::$error = $errorMsg;
    }

    /**
     * Returns the errors array
     *
     * @return array|bool
     */
    public static function getErrors()
    {
        return isset(self::$error) ? self::$error : false;
    }

    /**
     * Adds the last error request from the settings
     *
     * @param $errorRequest array array with the query, variables and result
     */
    public static function addRequest($errorRequest)
    {
        self::$request = $errorRequest;
    }

    /**
     * Returns the failed requests array
     *
     * @return array|bool [['query'=>'...' , 'variables'=>'...', 'result'=>'...']]
     */
    public static function getRequests()
    {
        return isset(self::$request) ? self::$request : false;
    }
}
