<?php

include(_PS_MODULE_DIR_ . 'clarobi/classes/ClaroLogger.php');

abstract class ClarobiApiAuthModuleFrontController extends ModuleFrontController
{
    /**
     * ClarobiAuthApiModuleFrontController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function init()
    {
        parent::init();
    }

    /**
     * Verify token.
     *
     * @return bool|void
     */
    public function initContent()
    {
        parent::initContent();

        if (!$this->isAuthorized()) {
            $response = [
                'status' => 401,
                'message' => 'Unauthorized: Invalid security token!'
            ];
            die(json_encode($response));
        }

        return true;
    }

    /**
     * Get token and compare it with the one from db.
     *
     * @return bool
     */
    protected function isAuthorized()
    {
        $token = Configuration::get('CLAROBI_API_KEY');
        $authToken = (isset($_SERVER['HTTP_X_CLARO_TOKEN']) ? $_SERVER['HTTP_X_CLARO_TOKEN'] : []);

        if (empty($authToken)) {
            ClaroLogger::errorLog(__METHOD__. ' : Missing token from request');
            return false;
        }
        if(empty($token)){
            ClaroLogger::errorLog(__METHOD__. ' : API_KEY (token) not found in database (configuration)');
            return false;
        }

        if (trim($token) != trim($authToken)) {
            ClaroLogger::errorLog(__METHOD__. " : Claro feed request with invalid security token: " . $authToken .
             "compared to stored token: " . $token);
            return false;
        }
        return true;
    }
}