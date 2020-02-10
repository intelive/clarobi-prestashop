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
            $i = 0;
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

                $items = [];
                foreach ($order->associations->order_rows as $order_row) {
                    $order_row->categories = $this->getCategoryPathTree($order_row->product_id);
                    $product = new Product($order_row->product_id);
                    $order_row->product_type = (!empty($product->getAttributeCombinations()) ? 'configurable' : $product->getWsType());

                    $items[] = $order_row;
                }

                $simpleOrder['associations']->order_rows = $items;

                // todo delete this fields since you can get google analytics only if set it up
                $simpleOrder['source'] = '(untracked)';
                $simpleOrder['medium'] = '(untracked)';
                $simpleOrder['campaign'] = '(untracked)';
                $simpleOrder['content'] = '(untracked)';
                $simpleOrder['gclid'] = '(untracked)';

                // Set to json
                $this->json[] = $simpleOrder;
            }

            // call encoder
            $this->encodeJson('order');
            /** @var Order $order */
            $this->encodedJson['lastId'] = ($order ? $order->id : 0);

            die(json_encode($this->encodedJson));

        } catch (Exception $exception) {
            ClaroLogger::errorLog(__CLASS__ . ':' . __METHOD__ . ' : ' . $exception->getMessage());

            $this->json = [
                'status' => 'error',
                'error' => $exception->getMessage()
            ];
            die(json_encode($this->json));
        }
    }
}
