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

include('api/api.php');

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
            'product' => 'SELECT id_product as `product` FROM `' . _DB_PREFIX_
                . 'product` ORDER BY id_product DESC LIMIT 1;',
            'customer' => 'SELECT id_customer as `customer` FROM `' . _DB_PREFIX_
                . 'customer` ORDER BY id_customer DESC LIMIT 1;',
            'order' => 'SELECT id_order as `order` FROM `' . _DB_PREFIX_
                . 'orders` ORDER BY id_order DESC LIMIT 1;',
            'invoice' => 'SELECT id_order_invoice as `invoice` FROM `' . _DB_PREFIX_
                . 'order_invoice` ORDER BY id_order_invoice DESC LIMIT 1;',
            'creditmemo' => 'SELECT id_order_slip as `creditmemo` FROM `' . _DB_PREFIX_
                . 'order_slip` ORDER BY id_order_slip DESC LIMIT 1;',
            'abandonedcart' => 'SELECT c.`id_cart` as `abandonedcart` 
                                FROM `' . _DB_PREFIX_ . 'cart` c
                                LEFT JOIN `' . _DB_PREFIX_ . 'orders` o ON o.id_cart = c.id_cart 
                                WHERE o.`id_cart` IS NULL 
                                ORDER BY c.`id_cart` DESC LIMIT 1;'
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
                if (isset($result)) {
                    $this->json[$key] = $result[0][$key];
                }
            } catch (PrestaShopDatabaseException $exception) {
                ClaroLogger::errorLog(__METHOD__ . ' : DBException: ' . $exception->getMessage()
                    . ' at line ' . $exception->getLine());

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
