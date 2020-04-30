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

include('api/api.php');

class ClarobiStockModuleFrontController extends ClarobiApiModuleFrontController
{
    /**
     * ClarobiStocksModuleFrontController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->url = $this->shopDomain . '/api/stock_availables';
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
                'stock' => []
            ];

            $this->collection = $this->getCollection();

            if (isset($this->collection->stock_availables)) {
                foreach ($this->collection->stock_availables as $stock_available) {
                    if ($stock_available->id_product_attribute == 0) {
                        // Remove unnecessary keys
                        $simpleStockAvailable = $this->simpleMapping->getSimpleMapping(
                            'stock_available',
                            $stock_available
                        );
                        $mappedStock = [
                            'id' => $simpleStockAvailable['id_product'],
                            's' => $simpleStockAvailable['quantity']
                        ];

                        // Add to jsonContent
                        $this->jsonContent['stock'][] = $mappedStock;
                    }
                }
            }

            // call encoder
            $this->encodeJson('stock', 'STOCK');

            die(json_encode($this->encodedJson));
        } catch (Exception $exception) {
            ClaroLogger::errorLog(__METHOD__ . ' : ' . $exception->getMessage() . ' at line ' . $exception->getLine());

            $this->jsonContent = [
                'status' => 'error',
                'error' => $exception->getMessage()
            ];
            die(json_encode($this->jsonContent));
        }
    }
}
