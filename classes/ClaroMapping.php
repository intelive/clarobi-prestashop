<?php

class ClaroMapping
{
    private $entityKeys = [
        'customer' => [
            'id_lang',
            'newsletter_date_add',
            'ip_registration_newsletter',
            'last_passwd_gen',
            'secure_key',
            'deleted',
            'passwd',
            'newsletter',
            'optin',
            'website',
            'company',
            'siret',
            'ape',
            'outstanding_allow_amount',
            'show_public_prices',
            'id_risk',
            'max_payment_days',
            'active',
            'note',
            'is_guest',
            'id_shop_group',
            'date_upd',
            'reset_password_token',
            'reset_password_validity',
            'associations'
        ],
        'product' => [
            'id_manufacturer',
//            'id_supplier',
            'id_category_default',
            'new',
            'cache_default_attribute',
            'id_default_image',
            'id_default_combination',
            'id_tax_rules_group',
            'position_in_category',
            'manufacturer_name',
//            'id_shop_default',
//            'reference',
//            'supplier_reference',
            'location',
            'width',
            'height',
            'depth',
            'weight',
            'quantity_discount',
            'ean13',
            'isbn',
            'upc',
            'cache_is_pack',
            'cache_has_attachments',
//            'is_virtual',
            'state',
            'additional_delivery_times',
            'delivery_in_stock',
            'delivery_out_stock',
//            'on_sale',
//            'online_only',
            'ecotax',
            'minimal_quantity',
            'low_stock_threshold',
            'low_stock_alert',
//            'price',
            'wholesale_price',
            'unity',
            'unit_price_ratio',
            'additional_shipping_cost',
            'customizable',
            'text_fields',
            'uploadable_files',
            'active',
            'redirect_type',
            'id_type_redirected',
            'available_for_order',
            'available_date',
            'show_condition',
//            'condition',
            'show_price',
            'indexed',
            'visibility',
            'advanced_stock_management',
//            'date_add',
            'date_upd',
            'pack_stock_type',
            'meta_description',
            'meta_keywords',
            'meta_title',
            'link_rewrite',
            'description',
            'description_short',
            'available_now',
            'available_later',
//            'associations'
        ],
        'order' => [
            'id_address_delivery',
            'id_address_invoice',
            'id_cart',
//            'id_currency',
            'id_lang',
            'id_customer',
            'id_carrier',
            'current_state',
            'module',
//            'invoice_number',
//            'invoice_date',
//            'delivery_number',
//            'delivery_date',
            'valid',
//            'date_add',
            'date_upd',
//            'shipping_number',
            'id_shop_group',
//            'id_shop',
            'secure_key',
//            'payment',
            'recyclable',
            'gift',
            'gift_message',
            'mobile_theme',
            'total_discounts',
            'total_discounts_tax_incl',
            'total_discounts_tax_excl',
//            'total_paid',
//            'total_paid_tax_incl',
//            'total_paid_tax_excl',
//            'total_paid_real',
//            'total_products',
            'total_products_wt',
//            'total_shipping',
//            'total_shipping_tax_incl',
//            'total_shipping_tax_excl',
            'carrier_tax_rate',
            'total_wrapping',
            'total_wrapping_tax_incl',
            'total_wrapping_tax_excl',
            'round_mode',
            'round_type',
//            'conversion_rate',
//            'reference',
//            'associations'
        ],
        'order_invoice' => [
//            'id_order',
//            'number',
//            'delivery_number',
//            'delivery_date',
//            'total_discount_tax_excl',
//            'total_discount_tax_incl',
//            'total_paid_tax_excl',
//            'total_paid_tax_incl',
//            'total_products',
            'total_products_wt',
//            'total_shipping_tax_excl',
//            'total_shipping_tax_incl',
            'shipping_tax_computation_method',
            'total_wrapping_tax_excl',
            'total_wrapping_tax_incl',
            'shop_address',
            'note',
//            'date_add'
        ],
        /**
         * Represents quantities available.
         * It is either synchronized with Stock or manually set by the seller.
         */
        'stock_available' => [
//            'id_product',
            'id_product_attribute',
            'id_shop',
            'id_shop_group',
//            'quantity',
            'depends_on_stock',
            'out_of_stock',
            'location',
        ],
        // order_slip
        'order_slip' => [
//            'id_customer',
//            'id_order',
//            'conversion_rate',
            'total_products_tax_excl',
//            'total_products_tax_incl',
//            'total_shipping_tax_excl',
            'total_shipping_tax_incl',
//            'amount',
//            'shipping_cost',
//            'shipping_cost_amount',
//            'partial',
//            'date_add',
            'date_upd',
//            'order_slip_type',
//            'associations'
        ],
        // carts
        'abandoned_cart' => [
            'id_address_delivery',
            'id_address_invoice',
//            'id_currency',
//            'id_customer',
            'id_guest',
            'id_lang',
            'id_shop_group',
//            'id_shop',
            'id_carrier',
            'recyclable',
            'gift',
            'gift_message',
            'mobile_theme',
            'delivery_option',
            'secure_key',
            'allow_seperated_package',
//            'date_add',
            'date_upd',
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