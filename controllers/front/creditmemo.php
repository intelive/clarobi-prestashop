<?php

include(_PS_MODULE_DIR_ . 'clarobi/controllers/front/api/api.php');

class ClarobiCreditmemoModuleFrontController extends ClarobiApiModuleFrontController
{
    /**
     * ClarobiOrdersModuleFrontController constructor.
     */
    public function __construct()
    {
        parent::__construct();
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
            foreach ($this->collection->order_slips as $order_slip) {
                // Remove unnecessary keys
                $simpleOrderSlip = $this->simpleMapping->getSimpleMapping('order_slip', $order_slip);

                // Assign entity_name attribute
                $simpleOrderSlip['entity_name'] = 'sales_creditnote';

                /** @var Order $order */
                $order = new Order($order_slip->id_order);
                $simpleOrderSlip['currency_code'] = $this->getCurrencyISOFromId($order->id_currency);

                /** @var OrderSlip $object */
                $object = new OrderSlip($order_slip->id);

                $items = [];
                $id_shop = 0;
                if (!empty($object->getProducts())) {
                    foreach ($object->getProducts() as $order_slip_product) {
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

                $simpleOrderSlip['id_shop'] = $id_shop;
                $simpleOrderSlip['associations']->order_slip_details = $items;

                // Set to json
                $this->json[] = $simpleOrderSlip;
            }

            // call encoder
            $this->encodeJson('sales_creditnote');
            /** @var OrderSlip $order_slip */
            $this->encodedJson['lastId'] = ($order_slip ? $order_slip->id : 0);

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
