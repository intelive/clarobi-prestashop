<?php
/**
* 2007-2021 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2021 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*
* Don't forget to prefix your containers with your own identifier
* to avoid any conflicts with others containers.
*/

include('api/api.php');

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
            $invoice = false;

            if (isset($this->collection->order_invoices)) {
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
                    $simpleInvoice['currency_code'] = $this->getCurrencyISOFromId(
                        $invoiceObject->getOrder()->id_currency
                    );

                    // Add to jsonContent
                    $this->jsonContent[] = $simpleInvoice;
                }
            }

            // call encoder
            $this->encodeJson('sales_invoice');
            /** @var OrderInvoice $invoice */
            $this->encodedJson['lastId'] = ($invoice ? $invoice->id : 0);

            die(json_encode($this->encodedJson));
        } catch (Exception $exception) {
            ClaroLogger::errorLog(__METHOD__ . ' : ' . $exception->getMessage() . ' at line ' . $exception->getLine());

            $this->jsonContent = [
                'status' => 'error',
                'error' => $exception->getMessage()
            ];
            die(json_encode($this->jsonContent));
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

        return ['items' => $items, 'id_shop' => $product['id_shop']];
    }
}
