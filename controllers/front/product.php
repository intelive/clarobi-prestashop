<?php

include(_PS_MODULE_DIR_ . 'clarobi/controllers/front/api/api.php');

class ClarobiProductModuleFrontController extends ClarobiApiModuleFrontController
{
    /**
     * ClarobiProductsModuleFrontController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->url = $this->shopDomain . '/api/products';
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
            foreach ($this->collection->products as $product) {
                // Remove unnecessary keys
                $simpleProduct = $this->simpleMapping->getSimpleMapping('product', $product);

                // Assign entity_name attribute
                $simpleProduct['entity_name'] = 'product';

                // Set to json
                $this->json[] = $simpleProduct;
            }

            // call parent encoder
            parent::encodeJson();
            // set entity name
            $this->encodedJson['entity'] = 'product';

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
