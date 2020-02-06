<?php

include(_PS_MODULE_DIR_ . 'clarobi/controllers/front/api/api.php');

class ClarobiOrderModuleFrontController extends ClarobiApiModuleFrontController
{
    /**
     * ClarobiOrdersModuleFrontController constructor.
     */
    public function __construct()
    {
        parent::__construct();
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


            foreach ($this->collection->orders as $order) {
                // Remove unnecessary keys
                $simpleOrder = $this->simpleMapping->getSimpleMapping('order', $order);

                // Assign entity_name attribute
                $simpleOrder['entity_name'] = 'order';

                // Set to json
                $this->json[] = $simpleOrder;
            }

            // call encoder
            $this->encodeJson('order');
            /** @var Order $order */
            $this->encodedJson['lastId'] = ($order ? $order->id : 0);

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
