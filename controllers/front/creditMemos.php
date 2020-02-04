<?php

include(_PS_MODULE_DIR_ . 'clarobi/controllers/front/api/api.php');

class ClarobiCreditMemosModuleFrontController extends ClarobiApiModuleFrontController
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

        foreach ($this->collection->order_slips as $order_slip) {
            // Remove unnecessary keys
            $simpleOrderSlip= $this->simpleMapping->getSimpleMapping('order_slip', $order_slip);

            // Assign entity_name attribute
            $simpleOrderSlip['entity_name'] = 'sales_creditnote';

            // Set to json
            $this->json[] = $simpleOrderSlip;
        }

        // call parent encoder
        parent::encodeJson();
        // set entity name
        $this->encodedJson['entity'] = 'sales_creditnote';

        die(json_encode($this->encodedJson));
    }
}
