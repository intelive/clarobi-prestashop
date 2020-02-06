<?php

include(_PS_MODULE_DIR_ . 'clarobi/controllers/front/api/api.php');

class ClarobiAbandonedCartModuleFrontController extends ClarobiApiModuleFrontController
{
    protected $orders;
    protected $orderCartId;
    protected $collItems;

    /**
     * ClarobiAbandonedCartsModuleFrontController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->url = $this->shopDomain . '/api/carts';
        $this->collItems = 0;
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

        $getCartIdFromOrders = [
            'display' => '[id_cart]',
            'output_format' => 'JSON'
        ];

        // Get last id to know where to stop
        $lastId = 0;
        $sql = 'SELECT c.id_cart FROM ps_cart c
                    WHERE c.id_cart NOT IN (SELECT o.id_cart FROM ps_orders o)
                    ORDER BY c.id_cart DESC LIMIT 1';

        try {
            $this->orders = json_decode($this->webService->get([
                'url' => $this->utils->createUrlWithQuery($this->shopDomain . '/api/orders', $getCartIdFromOrders)
            ]));

            // Get all carts ids that have an order associated.
            foreach ($this->orders->orders as $order) {
                $this->orderCartId[] = $order->id_cart;
            }

            $result = $result = Db::getInstance()->executeS($sql);
            if (isset($result)) {
                $lastId = $result[0]['id_cart'];
            }

            $continue = true;
            $i = 0;
            // try to get 50 items if cart id
            while ($this->collItems < self::LIMIT && $continue) {

                foreach ($this->collection->carts as $cart) {
                    // Get all carts that were NOT transformed into orders.
                    if (!in_array($cart->id, $this->orderCartId)) {
                        $this->collItems++;

                        // Remove unnecessary keys
                        $simpleAbandonedCart = $this->simpleMapping->getSimpleMapping('abandoned_cart', $cart);

                        // Assign entity_name attribute
                        $simpleAbandonedCart['entity_name'] = 'abandonedcart';

                        // Set to json
                        $this->json[] = $simpleAbandonedCart;
                    }
                }
                $this->collection = $this->getCollection($cart->id, self::LIMIT - $this->collItems);
                $continue = ($cart->id < $lastId?true:false);
            }

            // call encoder
            $this->encodeJson('abandonedcart');
            /** @var Cart $cart */
            $this->encodedJson['lastId'] = ($cart ? $cart->id : 0);

            die(json_encode($this->encodedJson));

        } catch (Exception $exception) {
            $this->json = [
                'status' => 'error',
                'error' => $exception->getMessage()
            ];
            die(json_encode($this->json));
        }
    }
}
