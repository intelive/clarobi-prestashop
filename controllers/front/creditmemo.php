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
            foreach ($this->collection->order_slips as $order_slip) {
                // Remove unnecessary keys
                $simpleOrderSlip = $this->simpleMapping->getSimpleMapping('order_slip', $order_slip);

                // Assign entity_name attribute
                $simpleOrderSlip['entity_name'] = 'sales_creditnote';

                /** @var Order $order */
                $order = new Order($order_slip->id_order);
                $simpleOrderSlip['currency_code'] = $this->getCurrencyISOFromId($order->id_currency);

                /** @var OrderSlip $orderSlipObject */
                $orderSlipObject = new OrderSlip($order_slip->id);

                $result = $this->orderSlipProducts($orderSlipObject);

                $simpleOrderSlip['id_shop'] = $result['id_shop'];
                $simpleOrderSlip['associations']->order_slip_details = $result['items'];

                // Set to json
                $this->json[] = $simpleOrderSlip;
            }

            // call encoder
            $this->encodeJson('sales_creditnote');
            /** @var OrderSlip $order_slip */
            $this->encodedJson['lastId'] = ($order_slip ? $order_slip->id : 0);

            die(json_encode($this->encodedJson));

        } catch (Exception $exception) {
            ClaroLogger::errorLog(__METHOD__ . ' : ' . $exception->getMessage() . ' at line ' . $exception->getLine());

            $this->json = [
                'status' => 'error',
                'error' => $exception->getMessage()
            ];
            die(json_encode($this->json));
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
