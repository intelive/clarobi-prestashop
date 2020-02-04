<?php

include _PS_ROOT_DIR_ . '/modules/statsproduct/statsproduct.php';
include(_PS_MODULE_DIR_ . 'clarobi/controllers/front/api/api.php');

class ClarobiProductCountersModuleFrontController extends ClarobiApiModuleFrontController
{
    protected $counters;

    /**
     * ClarobiProductCountersModuleFrontController constructor.
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * @throws PrestaShopException
     */
    public function init()
    {
        parent::init();

        $this->json['date'] = date('Y-m-d H:i:s', time());

        try {
            $sql = 'SELECT cp.`id_product` FROM `' . _DB_PREFIX_ . 'clarobi_products` cp';
            $result = Db::getInstance()->executeS($sql);

            $stats = new statsproduct();

            if (isset($result)) {
                foreach ($result as $productId) {
                    $id_product = $productId['id_product'];

                    $sql = 'UPDATE `' . _DB_PREFIX_ . 'clarobi_products` 
                                SET `views` = ' . (int)$stats->getTotalViewed($id_product) . ',
                                `date_update` = NOW() 
                                 WHERE `id_product` = ' . (int)$id_product;
                    Db::getInstance()->execute($sql);
                }
            }
        } catch (PrestaShopDatabaseException $exception) {
            $this->json = [
                'status' => 'error',
                'error' => $exception->getMessage()
            ];
            die(json_encode($this->json));
        }
    }

    /**
     * Handling GET requests can be done by implementing this method.
     */
    public function initContent()
    {
        /**
         * !!! Do not call parent::initContent().
         * Use it only for collections.
         * Custom output do not have an id and will generate error.
         */

        try {
            $sql = 'SELECT * FROM `'._DB_PREFIX_.'clarobi_products`';
            $result = Db::getInstance()->executeS($sql);
            // get result from clarobi_products table
            if (isset($result)) {
                foreach ($result as $row) {
                    $this->counters[] = [
                        'product_id' => $row['id_product'],
                        'event_name' => 'catalog_product_view',
                        'viewed' => $row['views']
                    ];
                    $this->counters[] = [
                        'product_id' => $row['id_product'],
                        'event_name' => 'checkout_cart_add_product',
                        'viewed' => $row['add_to_cart']
                    ];
                }
                $this->json['counters'] = $this->counters;
            }
        } catch (PrestaShopDatabaseException $e) {
            ClaroLogger::errorLog( __METHOD__. ' : DBException: ' . $e->getMessage());

            $this->json = [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
            die(json_encode($this->encodedJson));
        }

        // call parent encoder
        parent::encodeJson();
        // set entity name
        $this->encodedJson['entity'] = 'productCounters';

        die(json_encode($this->encodedJson));
    }
}