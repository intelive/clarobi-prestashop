<?php

include(_PS_MODULE_DIR_ . 'clarobi/controllers/front/api/api.php');

class ClarobiStocksModuleFrontController extends ClarobiApiModuleFrontController
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

        $this->json = [
            'date' => date('Y-m-d H:i:s', time()),
            'stock' => []
        ];

        foreach ($this->collection->stock_availables as $stock_available) {
            // Remove unnecessary keys
            $simpleStockAvailable = $this->simpleMapping->getSimpleMapping('stock_available', $stock_available);
            // Set to json
            $this->json['stock'][] = $simpleStockAvailable;
        }

        // call parent encoder
        parent::encodeJson();
        // set entity name
        $this->encodedJson['entity'] = 'stocks';

        die(json_encode($this->encodedJson));
    }
}
