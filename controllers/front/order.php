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

class ClarobiOrderModuleFrontController extends ClarobiApiModuleFrontController
{
    /**
     * ClarobiOrdersModuleFrontController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        // Set entity in url
        $this->url = $this->shopDomain . '/api/orders';
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
            $order = false;

            if (isset($this->collection->orders)) {
                foreach ($this->collection->orders as $order) {
                    // Remove unnecessary keys
                    $simpleOrder = $this->simpleMapping->getSimpleMapping('order', $order);

                    // Assign entity_name attribute
                    $simpleOrder['entity_name'] = 'sales_order';

                    $orderObject = new Order($order->id);
                    $simpleOrder['state'] = $orderObject->getCurrentOrderState()->template[1];

                    $coupon = null;
                    if (!empty($orderObject->getCartRules())) {
                        $coupon = $orderObject->getCartRules()[0]['name'];
                    }
                    $simpleOrder['coupon_code'] = $coupon;
                    $simpleOrder['currency_code'] = $this->getCurrencyISOFromId($order->id_currency);
                    $simpleOrder['customer'] = $this->getSimpleCustomer($order->id_customer);
                    $simpleOrder['delivery_address'] = $this->getAddress($order->id_address_delivery);
                    $simpleOrder['invoice_address'] = $this->getAddress($order->id_address_invoice);

                    $simpleOrder['associations']->order_rows = $this->associationsOrderRowsMapping($order);

                    // todo delete this fields since you can get google analytics only if set it up
                    $simpleOrder['source'] = '(untracked)';
                    $simpleOrder['medium'] = '(untracked)';
                    $simpleOrder['campaign'] = '(untracked)';
                    $simpleOrder['content'] = '(untracked)';
                    $simpleOrder['gclid'] = '(untracked)';

                    // Add to jsonContent
                    $this->jsonContent[] = $simpleOrder;
                }
            }

            // call encoder
            $this->encodeJson('sales_order');
            /** @var Order $order */
            $this->encodedJson['lastId'] = ($order ? $order->id : 0);

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
     * Map order products.
     *
     * @param $order
     * @return array
     * @throws Exception
     */
    private function associationsOrderRowsMapping($order)
    {
        $items = [];
        foreach ($order->associations->order_rows as $order_row) {

            /**
             * @todo add options for each product in order
             * "options":{
             *      "attribute_id": "1",
             *      "item_id": "381", #order item id,
             *      "label": "Manufacturer",
             *      "value": "Made In China"
             * }
             */
            $order_row->categories = $this->getCategoryPathTree($order_row->product_id);
            /** @var Product $product */
            $product = new Product($order_row->product_id);
            $type = (!empty($product->getAttributeCombinations()) ? 'configurable' : $product->getWsType());
            $order_row->product_type = $type;

            $order_row->options = $this->getProductOptions($product);

            $items[] = $order_row;
        }

        return $items;
    }

    /**
     * Get product options.
     *
     * @param Product $product
     * @return array
     * @throws Exception
     */
    private function getProductOptions($product)
    {
        $options = [];

        if ($product->getAttributeCombinations()) {
            foreach ($product->getAttributeCombinations() as $attributeCombination) {
                $options[] = [
                    'attribute_id' => $attributeCombination['id_attribute'],
                    'label' => $attributeCombination['group_name'],
                    'value' => $attributeCombination['attribute_name']
                ];
            }
        }

        return $options;
    }
}
