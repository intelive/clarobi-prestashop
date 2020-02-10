<?php

include(_PS_MODULE_DIR_ . 'clarobi/controllers/front/api/api.php');

class ClarobiCustomerModuleFrontController extends ClarobiApiModuleFrontController
{

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

                // Fields that are not available in collection
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

            // call encoder
            $this->encodeJson('customer');
            /** @var Customer $customer */
            $this->encodedJson['lastId'] = ($customer ? $customer->id : 0);

            die(json_encode($this->encodedJson));

        } catch (Exception $exception) {
            ClaroLogger::errorLog(__CLASS__.':'. __METHOD__ . ' : ' . $exception->getMessage());

            $this->json = [
                'status' => 'error',
                'error' => $exception->getMessage()
            ];
            die(json_encode($this->json));
        }
    }
}
