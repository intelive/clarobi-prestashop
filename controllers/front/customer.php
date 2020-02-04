<?php

include(_PS_MODULE_DIR_ . 'clarobi/controllers/front/api/api.php');

class ClarobiCustomerModuleFrontController extends ClarobiApiModuleFrontController
{
    protected $groups;

    /**
     * ClarobiCustomerModuleFrontController constructor.
     */
    public function __construct()
    {
        parent::__construct();
        $this->url = $this->shopDomain . '/api/customers';
    }

    /**
     * @throws PrestaShopException
     */
    public function init()
    {
        parent::init();

        $groups = Group::getGroups(1);
        foreach ($groups as $group) {
            $this->groups[$group['id_group']] = $group['name'];
        }
    }

    /**
     * Handling GET requests can be done by implementing this method.
     */
    public function initContent()
    {
        parent::initContent();

        try {

            foreach ($this->collection->customers as $customer) {
                // Remove unnecessary keys
                $simpleCustomer = $this->simpleMapping->getSimpleMapping('customer', $customer);

                // Assign entity_name attribute
                $simpleCustomer['entity_name'] = 'customer';

                // Fields that need to be set
                $simpleCustomer['group'] = $this->groups[$customer->id_default_group];
                $customerObject = new Customer($customer->id);
                $country = ($customerObject->getAddresses(1) ? $customerObject->getAddresses(1)[0]['country'] : null);
                $simpleCustomer['bill_country'] = $country;
                $simpleCustomer['ship_country'] = $country;

                // Fields that need to be add
                $simpleCustomer['source'] = '(untracked)';
                $simpleCustomer['medium'] = '(untracked)';
                $simpleCustomer['campaign'] = '(untracked)';
                $simpleCustomer['content'] = '(untracked)';
                $simpleCustomer['gclid'] = '(untracked)';

                // Set to json
                $this->json[] = $simpleCustomer;
            }

            // call parent encoder
            parent::encodeJson();
            // set entity name
            $this->encodedJson['entity'] = 'customer';

            die(json_encode($this->encodedJson));
        } catch (Exception $exception) {
            $this->json = [
                'status' => 'error',
                'error' => $exception->getMessage()
            ];
            die(json_encode($this->json));
        }
    }
}
