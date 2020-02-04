<?php

class ClaroLogger
{
    /**
     * Logs errors in module file.
     *
     * @param $message
     */
    public static function errorLog($message)
    {
        error_log($message . "( " . date('Y-m-d H:i:s') . " )\n",
            3,
            _PS_MODULE_DIR_ . 'clarobi/errors.log'
        );
    }
}