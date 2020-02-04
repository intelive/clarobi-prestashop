<?php

include(_PS_MODULE_DIR_ . 'clarobi/controllers/front/api/api.php');

class ClarobiAbandonedCartModuleFrontController extends ClarobiApiModuleFrontController
{
    protected $orders;
    protected $orderCartId;

    /**
     * ClarobiAbandonedCartsModuleFrontController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->url = $this->shopDomain . '/api/carts';
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

        try {
            $this->orders = json_decode($this->webService->get([
                'url' => $this->utils->createUrlWithQuery($this->shopDomain . '/api/orders', $getCartIdFromOrders)
            ]));

            // Get all carts ids that have an order associated.
            foreach ($this->orders->orders as $order) {
                $this->orderCartId[] = $order->id_cart;
            }

            foreach ($this->collection->carts as $cart) {
                // Get all carts that were not transformed into orders.
                if (!in_array($cart->id, $this->orderCartId)) {
                    // Remove unnecessary keys
                    $simpleAbandonedCart = $this->simpleMapping->getSimpleMapping('abandoned_cart', $cart);

                    // Assign entity_name attribute
                    $simpleAbandonedCart['entity_name'] = 'abandonedcart';

                    // Set to json
                    $this->json[] = $simpleAbandonedCart;
                }
            }

            // call encoder
            $this->encodeJson();
            // set entity name
            $this->encodedJson['entity'] = 'abandonedcart';

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
