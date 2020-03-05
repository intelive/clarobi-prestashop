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

if (!defined('_PS_VERSION_')) {
    exit;
}

include(dirname(__FILE__) . '/classes/ClaroConfig.php');
include(dirname(__FILE__) . '/classes/ClaroWebService.php');

class Clarobi extends Module
{
    private $html = '';

    public function __construct()
    {
        $this->name = 'clarobi';
        $this->tab = 'administration';
        $this->version = '1.0.1';
        $this->author = 'Interlive Metrics';
        $this->need_instance = 1;
        $this->module_key = 'c55ed1148592523f3abc22e1b064993c';

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('ClaroBi');
        $this->description = $this->l('API to provide the necessary statistics for ClaroBi 
                                                analytics that are not made available by PrestaShop');

        $this->confirmUninstall = $this->l('Are you sure you want to uninstall ClaroBi analytics and statistics?');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     *
     * @return bool
     */
    public function install()
    {
//        Configuration::updateValue('CLAROBI_LIVE_MODE', false);

        include(dirname(__FILE__) . '/sql/install.php');

        // Set Clarobi configurations
        ClaroConfig::setConfig();

        // Set Clarobi web service key
        ClaroWebService::setWebServiceKey();

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('actionCartUpdateQuantityBefore') &&
            $this->registerHook('AdminStatsModules');
    }

    /**
     * Delete all the configurations for the module.
     * Delete WebServiceKey.
     * @return bool
     */
    public function uninstall()
    {
        ClaroConfig::removeAllConfig();

        include(dirname(__FILE__) . '/sql/uninstall.php');

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitClaroBiModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');

        return $output . $this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitClaroBiModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'col' => 6,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-home"></i>',
                        'desc' => $this->l('Enter website domain'),
                        'name' => 'CLAROBI_WS_DOMAIN',
                        'label' => $this->l('Domain'),
                    ],
                    [
                        'col' => 6,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-key"></i>',
                        'desc' => $this->l('Enter license key provided by ClaroBi'),
                        'name' => 'CLAROBI_LICENSE_KEY',
                        'label' => $this->l('License key'),
                    ],
                    [
                        'col' => 6,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-key"></i>',
                        'desc' => $this->l('Enter API_KEY provided by ClaroBi.'),
                        'name' => 'CLAROBI_API_KEY',
                        'label' => $this->l('Api key'),
                    ],
                    [
                        'col' => 6,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-key"></i>',
                        'desc' => $this->l('Enter API_SECRET provided by ClaroBi.'),
                        'name' => 'CLAROBI_API_SECRET',
                        'label' => $this->l('Api secret'),
                    ]
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'CLAROBI_API_KEY' => Configuration::get('CLAROBI_API_KEY'),
            'CLAROBI_API_SECRET' => Configuration::get('CLAROBI_API_SECRET'),
            'CLAROBI_WS_DOMAIN' => Configuration::get('CLAROBI_WS_DOMAIN'),
            'CLAROBI_LICENSE_KEY' => Configuration::get('CLAROBI_LICENSE_KEY'),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be loaded in the BO.
     */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path . 'views/js/back.js');
            $this->context->controller->addCSS($this->_path . 'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path . '/views/js/front.js');
        $this->context->controller->addCSS($this->_path . '/views/css/front.css');
    }

    /**
     * Get resoult from module table to display in admin panel.
     *
     * @return array|false|mysqli_result|PDOStatement|resource|null
     */
    private function getProducts()
    {
        $sql = 'SELECT *
				FROM `' . _DB_PREFIX_ . 'clarobi_products` cp';

        try {
            return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        } catch (PrestaShopDatabaseException $exception) {
            ClaroLogger::errorLog(__METHOD__ . ' : ' . $exception->getMessage() . ' at line ' . $exception->getLine());
        }

        return [];
    }

    /*
     * Display statistics in admin panel.
     */
    public function hookAdminStatsModules()
    {
        $this->html = '
			<div class="panel-heading">
				' . $this->displayName . '
			</div>
			<h4>' . $this->trans('Guide', array(), 'Admin.Global') . '</h4>
			<div class="alert alert-info">
				<h4>Number of adds to cart compared to number of views</h4>
				<p class="font-italic">'
            . $this->trans(
                'This are the calculated numbers since module installation.',
                array(),
                'Modules.Clarobi.Admin'
            )
            . '</p>
			</div>';
        $this->html .= '
			<h4>ClaroBi calculated data</h4>
			<table class="table" style="border: 0; cellspacing: 0;">
				<thead>
					<tr>
						<th>
							<span class="title_box  active">' . $this->trans('Product id', array(), 'Admin.Global') . '</span>
						</th>
						<th>
							<span class="title_box  active">' . $this->trans('Total adds to cart', array(), 'Admin.Global') . '</span>
						</th>
						<th>
							<span class="title_box  active">' . $this->trans('Total views', array(), 'Admin.Global') . '</span>
						</th>
						<th>
							<span class="title_box  active">' . $this->trans('Date add', array(), 'Admin.Global') . '</span>
						</th>
						<th>
							<span class="title_box  active">' . $this->trans('Date update', array(), 'Admin.Global') . '</span>
						</th>
					</tr>
				</thead>
				<tbody>';

        foreach ($this->getProducts() as $product) {
            $this->html .= '
				<tr>
					<td>' . $product['id_product'] . '</td>
					<td>' . $product['add_to_cart'] . '</td>
					<td>' . $product['views'] . '</td>
					<td>' . $product['date_add'] . '</td>
					<td>' . $product['date_update'] . '</td>
				</tr>';
        }

        $this->html .= '
				</tbody>
			</table>';

        return $this->html;
    }

    /**
     * Update module table on cart update.
     *
     * @param $params
     */
    public function hookActionCartUpdateQuantityBefore($params)
    {
        $product = $params['product'];
        $id_product = $product->id;
        $quantity = $params['quantity'];
        $operator = $params['operator'];

        $this->insertUpdateAddToCartClaro($id_product, $quantity, $operator);
    }

    /**
     * Insert/Update quantity in module table on cart-update-quantity.
     *
     * @param $id_product
     * @param $quantity
     * @param $operator
     */
    protected function insertUpdateAddToCartClaro($id_product, $quantity, $operator)
    {
        ($operator == 'up' ? $operator_sign = '+' : $operator_sign = '-');

        $sqlInsert = 'INSERT INTO `' . _DB_PREFIX_ . 'clarobi_products` 
                        (`id_product`, `add_to_cart`,`date_add`)
                        VALUES (' . (int)$id_product . ',' . $quantity . ',NOW()) 
                        ON DUPLICATE KEY UPDATE
                        `add_to_cart` = `add_to_cart` ' . (string)$operator_sign . ' ' . (int)$quantity;

        if (!Db::getInstance()->execute($sqlInsert)) {
            ClaroLogger::errorLog(__METHOD__ . ' : Query could not be executed.');
        }
    }
}
