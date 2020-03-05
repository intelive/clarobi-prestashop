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

class ClarobiCreditmemoModuleFrontController extends ClarobiApiModuleFrontController
{
    /**
     * ClarobiOrdersModuleFrontController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        // Set entity in url
        $this->url = $this->shopDomain . '/api/order_slip';
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
        parent::initContent();

        try {
            $order_slip = false;

            if (isset($this->collection->order_slips)) {
                foreach ($this->collection->order_slips as $order_slip) {
                    // Remove unnecessary keys
                    $simpleOrderSlip = $this->simpleMapping->getSimpleMapping('order_slip', $order_slip);

                    // Assign entity_name attribute
                    $simpleOrderSlip['entity_name'] = 'sales_creditnote';

                    /** @var Order $order */
                    $order = new Order($order_slip->id_order);
                    $simpleOrderSlip['currency_code'] = $this->getCurrencyISOFromId($order->id_currency);

                    /** @var OrderSlip $orderSlipObject */
                    $orderSlipObject = new OrderSlip((int)$order_slip->id);

                    $result = $this->orderSlipProducts($orderSlipObject);

                    $simpleOrderSlip['id_shop'] = $result['id_shop'];
                    $simpleOrderSlip['associations']->order_slip_details = $result['items'];

                    // Add to jsonContent
                    $this->jsonContent[] = $simpleOrderSlip;
                }
            }

            // call encoder
            $this->encodeJson('sales_creditnote');
            /** @var OrderSlip $order_slip */
            $this->encodedJson['lastId'] = ($order_slip ? $order_slip->id : 0);

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

    /**
     * Map order slip products.
     *
     * @param OrderSlip $orderSlipObject
     * @return array
     * @throws Exception
     */
    private function orderSlipProducts($orderSlipObject)
    {
        $items = [];
        $id_shop = 0;
        if (!empty($orderSlipObject->getProducts())) {
            foreach ($orderSlipObject->getProducts() as $order_slip_product) {
                // extract all the necessary fields (the ones selected are the default from ws)
                $items[] = [
                    'product_id' => $order_slip_product['product_id'],
                    'product_quantity' => $order_slip_product['product_quantity'],
                    'amount_tax_excl' => $order_slip_product['amount_tax_excl'],
                    'amount_tax_incl' => $order_slip_product['amount_tax_incl']
                ];
            }
            if (!empty($order_slip_product)) {
                $id_shop = $order_slip_product['id_shop'];
            }
        }

        return ['id_shop' => $id_shop, 'items' => $items];
    }
}
