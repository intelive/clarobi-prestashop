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

                $options = [];
                if (isset($product->associations->combinations)) {
                    $object = new Product($product->id);
                    foreach ($object->getAttributeCombinations() as $attributeCombination) {
                        $options[] = [
                            'id_product_attribute' => $attributeCombination['id_product_attribute'],
                            'id_product' => $attributeCombination['id_product'],
                            'price' => $attributeCombination['price'],
                            'id_shop' => $attributeCombination['id_shop'],
                            'id_attribute_group' => $attributeCombination['id_attribute_group'],
                            'group_name' => $attributeCombination['group_name'],
                            'attribute_name' => $attributeCombination['attribute_name'],
                            'id_attribute' => $attributeCombination['id_attribute'],
                        ];
                    }
                    $this->getCategoryPath($object->getCategories());
                }
                $simpleProduct['options'] = $options;

                // Set to json
                $this->json[] = $simpleProduct;
            }

            // call encoder
            $this->encodeJson('product');
            /** @var Product $product */
            $this->encodedJson['lastId'] = ($product ? $product->id : 0);

            die(json_encode($this->encodedJson));

        } catch (Exception $exception) {
            ClaroLogger::errorLog(__CLASS__.':'. __METHOD__ . ' : ' . $exception->getMessage());

            $this->json = [
                'status' => 'error',
                'error' => $exception->getMessage()
            ];
            die(json_encode($this->json));
        }
    }
}
