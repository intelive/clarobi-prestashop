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

class ClarobiCustomerModuleFrontController extends ClarobiApiModuleFrontController
{
    /**
     * ClarobiCustomerModuleFrontController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        // Set entity in url
        $this->url = $this->shopDomain . '/api/customers';
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
            $customer = false;

            if (isset($this->collection->customers)) {
                foreach ($this->collection->customers as $customer) {
                    // Remove unnecessary keys
                    $simpleCustomer = $this->simpleMapping->getSimpleMapping('customer', $customer);

                    // Assign entity_name attribute
                    $simpleCustomer['entity_name'] = 'customer';
                    // Fields that are not available in collection
                    $simpleCustomer['group'] = $this->groups[$customer->id_default_group];

                    $customerObject = new Customer($customer->id);

                    $id_address = (!empty($customerObject->getAddresses(1))
                        ? $customerObject->getAddresses(1)[0]['id_address'] : null);
                    $address = $this->getAddress($id_address);

                    $simpleCustomer['bill_country'] = $address['country'];
                    $simpleCustomer['ship_country'] = $address['country'];

                    // Fields that need to be add
                    $simpleCustomer['source'] = '(untracked)';
                    $simpleCustomer['medium'] = '(untracked)';
                    $simpleCustomer['campaign'] = '(untracked)';
                    $simpleCustomer['content'] = '(untracked)';
                    $simpleCustomer['gclid'] = '(untracked)';

                    // Add to jsonContent
                    $this->jsonContent[] = $simpleCustomer;
                }
            }

            // call encoder
            $this->encodeJson('customer');
            /** @var Customer $customer */
            $this->encodedJson['lastId'] = ($customer ? $customer->id : 0);

            die(json_encode($this->encodedJson));
        } catch (Exception $exception) {
            ClaroLogger::errorLog(__METHOD__ . ' : ' . $exception->getMessage() . ' at line ' . $exception->getLine());

            $this->jsonContent = [
                'status' => 'error',
                'error' => $exception->getMessage()];
            die(json_encode($this->jsonContent));
        }
    }
}
