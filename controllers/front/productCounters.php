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

include(_PS_ROOT_DIR_ . '/modules/statsproduct/statsproduct.php');
include('api/api.php');

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
            ClaroLogger::errorLog(__METHOD__ . ' : ' . $exception->getMessage() . ' at line ' . $exception->getLine());

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
            $this->json = [
                'date' => date('Y-m-d H:i:s', time()),
                'counters' => []
            ];

            $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'clarobi_products`';
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

            // call encoder
            $this->encodeJson('product_counter', 'PRODUCT_COUNTERS');

            die(json_encode($this->encodedJson));

        } catch (PrestaShopDatabaseException $exception) {
            ClaroLogger::errorLog(__METHOD__ . ' : DBException: ' . $exception->getMessage()
                . ' at line ' . $exception->getLine());

            $this->json = [
                'status' => 'error',
                'message' => $exception->getMessage()
            ];
            die(json_encode($this->json));
        }
    }
}
