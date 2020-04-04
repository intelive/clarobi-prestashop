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

class ClarobiAbandonedcartModuleFrontController extends ClarobiApiModuleFrontController
{
    private $orders;
    private $orderCartId;

    private $getCartIdFromOrders = [
        'display' => '[id_cart]',
        'output_format' => 'JSON',
        'sort' => 'id_cart_ASC'
    ];


    /**
     * ClarobiAbandonedCartsModuleFrontController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        // Set entity in url
        $this->url = $this->shopDomain . '/api/carts';

        $this->collItems = 0;
        $this->lastId = 0;
    }

    /**
     * @throws PrestaShopException
     */
    public function init()
    {
        parent::init();

        // Sql to get last id from carts
        $sql = 'SELECT c.id_cart FROM `' . _DB_PREFIX_ . 'cart` c
                    WHERE c.id_cart NOT IN (SELECT o.id_cart FROM `' . _DB_PREFIX_ . 'orders` o)
                    ORDER BY c.id_cart DESC LIMIT 1';
        try {
            // Get orders
            $this->orders = json_decode($this->webService->get([
                'url' => $this->utils->createUrlWithQuery($this->shopDomain . '/api/orders', $this->getCartIdFromOrders)
            ]));

            if (isset($this->orders->orders)) {
                // Get all carts ids that have an order associated.
                foreach ($this->orders->orders as $order) {
                    $this->orderCartId[] = $order->id_cart;
                }
            }

            $result = $result = Db::getInstance()->executeS($sql);
            if (isset($result)) {
                // Get last cart id
                $this->lastId = $result[0]['id_cart'];
            }
        } catch (Exception $exception) {
            ClaroLogger::errorLog(__METHOD__ . ' : ' . $exception->getMessage() . ' at line ' . $exception->getLine());

            $this->jsonContent = [
                'status' => 'error',
                'error' => $exception->getMessage()
            ];
            die(json_encode($this->jsonContent));
        }

        return true;
    }

    /**
     * Handling GET requests can be done by implementing this method.
     */
    public function initContent()
    {
        parent::initContent();

        try {
            $continue = true;
            $simpleAbandonedCart = false;

            // coll. items are less than the setup limit and last id is not exceeded
            while ($this->collItems < self::LIMIT && $continue) {
                if (isset($this->collection->carts)) {
                    foreach ($this->collection->carts as $cart) {
                        // Get all carts that were NOT transformed into orders.
                        if (!in_array($cart->id, $this->orderCartId)) {
                            // Remove unnecessary keys
                            $simpleAbandonedCart = $this->simpleMapping->getSimpleMapping('abandoned_cart', $cart);

                            // Assign entity_name attribute
                            $simpleAbandonedCart['entity_name'] = 'abandonedcart';
                            $simpleAbandonedCart['associations'] = $this->associationsCartRowsMapping($cart);

                            // Add to jsonContent
                            $this->jsonContent[] = $simpleAbandonedCart;
                            // Increment coll items count
                            $this->collItems++;
                        }
                    }
                    // Verify if the last id is exceeded
                    $continue = ($cart->id < $this->lastId ? true : false);
                    if ($continue) {
                        $this->collection = $this->getCollection($cart->id, self::LIMIT - $this->collItems);
                    }
                }
            }

            // call encoder
            $this->encodeJson('abandonedcart');
            /** @var Cart $cart */
            $this->encodedJson['lastId'] = ($simpleAbandonedCart ? $simpleAbandonedCart['id'] : 0);

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
     * Map cart products.
     *
     * @param $cart
     * @return array
     * @throws Exception
     */
    private function associationsCartRowsMapping($cart)
    {
        $items = [];
        if (isset($cart->associations)) {
            foreach ($cart->associations->cart_rows as $cart_row) {
                /** @var Product $product */
                $product = new Product($cart_row->id_product);
                $cart_row->product_name = $product->name[1];
                $cart_row->product_price = $product->price;
                $cart_row->product_reference = $product->reference;

                $items[] = $cart_row;
            }
        }

        return ['cart_rows' => $items];
    }
}
