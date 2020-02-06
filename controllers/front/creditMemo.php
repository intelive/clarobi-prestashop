<?php

include(_PS_MODULE_DIR_ . 'clarobi/controllers/front/api/api.php');

class ClarobiCreditMemoModuleFrontController extends ClarobiApiModuleFrontController
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
