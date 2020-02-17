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
 *  @author    PrestaShop SA <contact@prestashop.com>
 *  @copyright 2007-2020 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

class ClaroWebService
{
    /**
     * Set endpoints permissions.
     * Default: GET=true for all endpoints
     */
    const PERMISSIONS = [
        'addresses' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'carriers' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'cart_rules' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'carts' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'categories' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'combinations' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'configurations' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'contacts' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'content_management_system' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'countries' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'currencies' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'customer_messages' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'customer_threads' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'customers' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'customizations' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'deliveries' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'employees' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'groups' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'guests' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'image_types' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'images' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'languages' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'manufacturers' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'messages' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'order_carriers' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'order_details' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'order_histories' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'order_invoices' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'order_payments' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'order_slip' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'order_states' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'orders' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'price_ranges' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'product_customization_fields' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'product_feature_values' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'product_features' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'product_option_values' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'product_options' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'product_suppliers' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'products' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'search' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'shop_groups' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'shop_urls' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'shops' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'specific_price_rules' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'specific_prices' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'states' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'stock_availables' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'stock_movement_reasons' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'stock_movements' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'stocks' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'stores' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'suppliers' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'supply_order_details' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'supply_order_histories' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'supply_order_receipt_histories' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'supply_order_states' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'supply_orders' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'tags' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'tax_rule_groups' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'tax_rules' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'taxes' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'translated_configurations' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'warehouse_product_locations' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'warehouses' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'weight_ranges' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
        'zones' => ['GET' => 1, 'POST' => 0, 'PUT' => 0, 'DELETE' => 0, 'HEAD' => 0],
    ];

    /**
     * Create a web service key for all the endpoints,
     * only GET.
     */
    public static function setWebServiceKey()
    {
        $apiAccess = new WebserviceKey();
        $apiAccess->key = Configuration::get('CLAROBI_WS_KEY');
        $apiAccess->description = 'ClaroBi permissions. DO NOT DELETE!';
        $apiAccess->isModule = 1;
        $apiAccess->module_name = 'clarobi';
        $apiAccess->add();
        $apiAccess->save();

        WebserviceKey::setPermissionForAccount($apiAccess->id, self::PERMISSIONS);
    }
}
