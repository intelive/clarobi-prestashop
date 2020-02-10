<?php

include(_PS_MODULE_DIR_ . 'clarobi/controllers/front/api/api.php');

class ClarobiInvoiceModuleFrontController extends ClarobiApiModuleFrontController
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

        try {

            foreach ($this->collection->order_invoices as $invoice) {
                // Remove unnecessary keys
                $simpleInvoice = $this->simpleMapping->getSimpleMapping('order_invoice', $invoice);

                // Assign entity_name attribute
                $simpleInvoice['entity_name'] = 'sales_invoice';

                /** @var OrderInvoice $object */
                $object = new OrderInvoice($invoice->id);

                $items =[];
                foreach($object->getProducts() as $product){
                    $items[] = [
                        'product_id'=>$product['product_id'],
                        'product_quantity'=>$product['product_quantity'],
                        'product_price' =>$product['product_price']
                    ];
                }
                $simpleInvoice['id_shop'] = $product['id_shop'];
                $simpleInvoice['currency_code'] = $this->getCurrencyISOFromId($object->getOrder()->id_currency);
                $simpleInvoice['items'] = $items;

                // Set to json
                $this->json[] = $simpleInvoice;
            }

            // call encoder
            $this->encodeJson('sales_invoice');
            /** @var OrderInvoice $invoice */
            $this->encodedJson['lastId'] = ($invoice ? $invoice->id : 0);

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
