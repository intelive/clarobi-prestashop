<?php
/**
 * 2007-2020 PrestaShop
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
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2020 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

use Symfony\Component\HttpFoundation\JsonResponse;

include('api/api.php');

class ClarobiProductModuleFrontController extends ClarobiApiModuleFrontController
{
    /**
     * ClarobiProductsModuleFrontController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        // Set entity in url
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
            $product = null;

            if (isset($this->collection->products)) {
                foreach ($this->collection->products as $product) {
                    // Remove unnecessary keys
                    $simpleProduct = $this->simpleMapping->getSimpleMapping('product', $product);

                    // Assign entity_name attribute
                    $simpleProduct['entity_name'] = 'product';
                    $simpleProduct['options'] = $this->assotiationsOptionsMapping($product);

                    // Add to jsonContent
                    $this->jsonContent[] = $simpleProduct;
                }
            }

            // call encoder
            $this->encodeJson('product');
            /** @var Product $product */
            $this->encodedJson['lastId'] = ($product ? $product->id : 0);

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
     * Map product combinations.
     *
     * @param $product
     * @return array
     * @throws Exception
     */
    private function assotiationsOptionsMapping($product)
    {
        $options = [];
        if (isset($product->associations->combinations)) {
            /** @var Product $object */
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
        }

        return $options;
    }
}
