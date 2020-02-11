<?php

include(_PS_MODULE_DIR_ . 'clarobi/controllers/front/api/api.php');

class ClarobiStockModuleFrontController extends ClarobiApiModuleFrontController
{
    /**
     * ClarobiStocksModuleFrontController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->url = $this->shopDomain . '/api/stock_availables';
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
            $this->json = [
                'date' => date('Y-m-d', time()),
                'stock' => []
            ];

            foreach ($this->collection->stock_availables as $stock_available) {
                if ($stock_available->id_product_attribute == 0) {
                    // Remove unnecessary keys
                    $simpleStockAvailable = $this->simpleMapping->getSimpleMapping('stock_available', $stock_available);

                    // Set to json
                    $this->json['stock'][] = $simpleStockAvailable;
                }
            }

            // call encoder
            $this->encodeJson('stock');

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
