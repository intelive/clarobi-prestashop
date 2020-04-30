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

class ClaroMapping
{
    private $entityKeys = [
        'customer' => [
            'id_lang', 'newsletter_date_add', 'ip_registration_newsletter', 'last_passwd_gen', 'secure_key', 'deleted',
            'passwd', 'newsletter', 'optin', 'website', 'company', 'siret', 'ape', 'outstanding_allow_amount',
            'show_public_prices', 'id_risk', 'max_payment_days', 'active', 'note', 'is_guest', 'id_shop_group',
            'date_upd', 'reset_password_token', 'reset_password_validity', 'associations'
        ],
        'product' => [
//            'id_supplier', 'id_default_combination', 'id_shop_default', 'reference', 'supplier_reference', 'is_virtual',
//            'on_sale', 'online_only', 'price', 'condition', 'date_add', 'date_upd', 'associations',
            'id_manufacturer', 'id_category_default', 'new', 'cache_default_attribute', 'id_default_image',
            'id_tax_rules_group', 'position_in_category', 'manufacturer_name', 'location', 'width', 'height', 'depth',
            'weight', 'quantity_discount', 'ean13', 'isbn', 'upc', 'cache_is_pack', 'cache_has_attachments', 'state',
            'additional_delivery_times', 'delivery_in_stock', 'delivery_out_stock', 'ecotax', 'minimal_quantity',
            'low_stock_threshold', 'low_stock_alert', 'wholesale_price', 'unity', 'unit_price_ratio',
            'additional_shipping_cost', 'customizable', 'text_fields', 'uploadable_files', 'active', 'redirect_type',
            'id_type_redirected', 'available_for_order', 'available_date', 'show_condition', 'show_price', 'indexed',
            'visibility', 'advanced_stock_management', 'pack_stock_type', 'meta_description', 'meta_keywords',
            'meta_title', 'link_rewrite', 'description', 'description_short', 'available_now', 'available_later',
        ],
        'order' => [
//            'id_currency', 'current_state', 'module', 'invoice_number', 'invoice_date', 'delivery_number', 'date_add',
//            'shipping_number', 'id_shop', 'payment', 'total_discounts', 'total_paid_tax_incl', 'total_paid_tax_excl',
//            'total_products', 'total_shipping_tax_incl', 'total_shipping_tax_excl', 'reference', 'associations',
            'id_address_delivery', 'id_address_invoice', 'id_cart', 'id_lang', 'id_customer', 'id_carrier',
            'delivery_date', 'valid', 'date_upd', 'id_shop_group', 'secure_key', 'recyclable', 'gift', 'gift_message',
            'mobile_theme', 'total_discounts_tax_incl', 'total_discounts_tax_excl', 'total_paid', 'total_paid_real',
            'total_products_wt', 'total_shipping', 'carrier_tax_rate', 'total_wrapping', 'total_wrapping_tax_incl',
            'total_wrapping_tax_excl', 'round_mode', 'round_type', 'conversion_rate',
        ],
        'order_invoice' => [
//            'id_order', 'number', 'delivery_number', 'delivery_date', 'total_discount_tax_excl',
//            'total_discount_tax_incl', 'total_paid_tax_excl', 'total_paid_tax_incl', 'total_products',
//            'total_shipping_tax_excl', 'total_shipping_tax_incl', 'date_add',
            'total_products_wt', 'shipping_tax_computation_method', 'total_wrapping_tax_excl',
            'total_wrapping_tax_incl', 'shop_address', 'note',
        ],
        /**
         * Represents quantities available.
         * It is either synchronized with Stock or manually set by the seller.
         */
        'stock_available' => [
//            'id_product', 'quantity',
            'id_product_attribute', 'id_shop', 'id_shop_group', 'depends_on_stock', 'out_of_stock', 'location',
        ],
        // order_slip
        'order_slip' => [
//            'id_customer', 'id_order', 'total_products_tax_excl', 'total_products_tax_incl',
//            'total_shipping_tax_excl', 'total_shipping_tax_incl', 'amount', 'shipping_cost', 'shipping_cost_amount',
//            'partial', 'date_add', 'associations',
            'conversion_rate', 'date_upd', 'order_slip_type',
        ],
        // carts
        'abandoned_cart' => [
//            'id_customer', 'id_shop', 'date_add',
            'id_address_delivery', 'id_address_invoice', 'id_currency', 'id_guest', 'id_lang', 'id_shop_group',
            'id_carrier', 'recyclable', 'gift', 'gift_message', 'mobile_theme', 'delivery_option', 'secure_key',
            'allow_seperated_package', 'date_upd',
        ]
    ];

    /**
     * ClaroMapping constructor.
     */
    public function __construct()
    {
    }

    /**
     * @param $entity
     * @param $entityData
     * @return array
     */
    public function getSimpleMapping($entityName, $entityData)
    {
        if (array_key_exists($entityName, $this->entityKeys)) {
            return $this->removeUnusedKeys($entityName, $entityData);
        }

        return [];
    }

    /**
     * @param $entityName
     * @param $entityData
     * @return array
     */
    private function removeUnusedKeys($entityName, $entityData)
    {
        $keysToRemove = $this->entityKeys[$entityName];
        $newObject = [];
        if (!empty($entityData)) {
            foreach ($entityData as $objectAttr => $objectValue) {
                (!in_array($objectAttr, $keysToRemove) ? $newObject[$objectAttr] = $objectValue : '');
            }
        }

        return $newObject;
    }
}
