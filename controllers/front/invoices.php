<?php

include(_PS_MODULE_DIR_ . 'clarobi/controllers/front/api/api.php');

class ClarobiInvoicesModuleFrontController extends ClarobiApiModuleFrontController
{
    /**
     * ClarobiInvoicesModuleFrontController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->url = $this->shopDomain . '/api/order_invoices';
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

        foreach ($this->collection->order_invoices as $invoice){
            // Remove unnecessary keys
            $simpleInvoice = $this->simpleMapping->getSimpleMapping('order_invoice', $invoice);

            // Assign entity_name attribute
            $simpleInvoice['entity_name'] = 'sales_invoice';

            // Set to json
            $this->json[] = $simpleInvoice;
        }

        // call parent encoder
        parent::encodeJson();
        // set entity name
        $this->encodedJson['entity'] = 'sales_invoice';

        die(json_encode($this->encodedJson));
    }
}
