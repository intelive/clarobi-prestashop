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
        // Set entity in url
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

                /** @var OrderInvoice $invoiceObject */
                $invoiceObject = new OrderInvoice($invoice->id);

                $result = $this->invoiceProductsMapping($invoiceObject);

                $simpleInvoice['id_shop'] = $result['id_shop'];
                $simpleInvoice['items'] = $result['items'];
                $simpleInvoice['currency_code'] = $this->getCurrencyISOFromId($invoiceObject->getOrder()->id_currency);

                // Set to json
                $this->json[] = $simpleInvoice;
            }

            // call encoder
            $this->encodeJson('sales_invoice');
            /** @var OrderInvoice $invoice */
            $this->encodedJson['lastId'] = ($invoice ? $invoice->id : 0);

            die(json_encode($this->encodedJson));

        } catch (Exception $exception) {
            ClaroLogger::errorLog(__METHOD__ . ' : ' . $exception->getMessage() . ' at line ' . $exception->getLine() . ' at line ' . $exception->getLine());

            $this->json = [
                'status' => 'error',
                'error' => $exception->getMessage()
            ];
            die(json_encode($this->json));
        }
    }

    /**
     * Map invoice products.
     *
     * @param OrderInvoice $invoice
     * @return array
     */
    private function invoiceProductsMapping($invoice)
    {
        $items = [];
        /** @var array $product */
        foreach ($invoice->getProducts() as $product) {
            $items[] = [
                'product_id' => $product['product_id'],
                'product_quantity' => $product['product_quantity'],
                'product_price' => $product['product_price']
            ];
        }

        return ['items' => $items, 'id_shop' =>  $product['id_shop']];
    }
}
