<?php
/**
 * 2007-2020 PrestaShop
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License (AFL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/afl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

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
                'status' => 'error',
                'error' => 'Unauthorized: Invalid security token!'
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
            ClaroLogger::errorLog(__METHOD__ . ' : Missing token from request.');

            return false;
        }
        if (empty($token)) {
            ClaroLogger::errorLog(__METHOD__ . ' : API_KEY (token) not found in database (configuration).');

            return false;
        }
        if (trim($token) != trim($authToken)) {
            ClaroLogger::errorLog(__METHOD__ . " : Claro feed request with invalid security token: " . $authToken .
                " compared to stored token: " . $token);

            return false;
        }

        return true;
    }
}
