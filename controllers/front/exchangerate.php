<?php
/**
* 2007-2021 PrestaShop
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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2021 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*
* Don't forget to prefix your containers with your own identifier
* to avoid any conflicts with others containers.
*/

include('api/api.php');

class ClarobiExchangerateModuleFrontController extends ClarobiApiModuleFrontController
{
    /**
     * ClarobiExchangerateModuleFrontController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->url = $this->shopDomain . '/api/currencies';
    }

    /**
     * @throws PrestaShopException
     */
    public function init()
    {
        parent::init();
    }

    /**
     * Handling GET requests can be done by implementing this method.
     */
    public function initContent()
    {
        try {
            $this->jsonContent = [
                'date' => date('Y-m-d H:i:s', time()),
                'data' => []
            ];

            // other way of setting base currency is to provide input in configuration form
            $base_currency = Currency::getDefaultCurrency();

            $this->collection = $this->getCollection();

            if (isset($this->collection->currencies)) {
                /** @var Currency $currency */
                foreach ($this->collection->currencies as $currency) {
                    // Add to jsonContent
                    $this->jsonContent['data'][] = [
                        'id' => $currency->id,
                        'from_currency' => $currency->iso_code,
                        'base_currency' => ($base_currency ? $base_currency->iso_code : null),
                        'rate' => $currency->conversion_rate,
                        'created_at' => null,   // no date found
                        'updated_at' => null
                    ];
                }
            }

            // call encoder
            $this->encodeJson('exchange_rate', 'EXCHANGE_RATE');

            die(json_encode($this->encodedJson));
        } catch (Exception $exception) {
            $this->jsonContent = [
                'status' => 'error',
                'error' => $exception->getMessage()
            ];
            die(json_encode($this->jsonContent));
        }
    }
}
