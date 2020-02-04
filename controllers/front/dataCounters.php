<?php

include(_PS_MODULE_DIR_ . 'clarobi/controllers/front/api/api.php');

class ClarobiDataCountersModuleFrontController extends ClarobiApiModuleFrontController
{
    protected $sql;

    /**
     * ClarobiDataCountersModuleFrontController constructor.
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

        // Create all the necessarily queries.
        $this->sql = [
            'product' => 'SELECT id_product as `product` FROM `' . _DB_PREFIX_ . 'product` ORDER BY id_product DESC LIMIT 1',
            'customer' => 'SELECT id_customer as `customer` FROM `' . _DB_PREFIX_ . 'customer` ORDER BY id_customer DESC LIMIT 1',
            'order' => 'SELECT id_order as `order` FROM `' . _DB_PREFIX_ . 'orders` ORDER BY id_order DESC LIMIT 1',
            'invoice' => 'SELECT id_order_invoice as `invoice` FROM `' . _DB_PREFIX_ . 'order_invoice` ORDER BY id_order_invoice DESC LIMIT 1',
            'creditmemo' => 'SELECT id_order_slip as `creditmemo` FROM `' . _DB_PREFIX_ . 'order_slip` ORDER BY id_order_slip DESC LIMIT 1',
            'abandonedcart' => 'SELECT ps_cart.id_cart as `abandonedcart` FROM ps_cart 
                                LEFT JOIN ps_orders ON ps_orders.id_cart = ps_cart.id_cart 
                                WHERE ps_orders.id_cart IS NULL 
                                ORDER BY ps_cart.id_cart DESC LIMIT 1'
        ];
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

        foreach ($this->sql as $key => $query) {
            try {
                $result = Db::getInstance()->executeS($query);
                if(isset($result)){
                    $this->json[$key] = $result[0][$key];
                }
            } catch (PrestaShopDatabaseException $exception) {
                error_log('DatabaseException: ' . $exception->getMessage() . ' in ' . __METHOD__,
                    3,
                    _PS_MODULE_DIR_ . 'clarobi/errors.log'
                );

                $this->json = [
                    'status' => 'error',
                    'error' => $exception->getMessage()
                ];
                die(json_encode($this->json));
            }
        }
        die(json_encode($this->json));
    }

}