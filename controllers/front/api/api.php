<?php

include(_PS_MODULE_DIR_ . 'clarobi/classes/ClaroMapping.php');
include(_PS_MODULE_DIR_ . 'clarobi/lib/PSWebServiceLibrary.php');
include(_PS_MODULE_DIR_ . 'clarobi/controllers/front/api/apiAuth.php');

class ClarobiApiModuleFrontController extends ClarobiApiAuthModuleFrontController
{
    const DEBUG = false;
    const LIMIT = 50;

    protected $json = [];
    protected $encodedJson = [];
    protected $collection;
    protected $params = [
        'display' => 'full',
        'output_format' => 'JSON'
    ];

    /** @var ClaroMapping */
    protected $simpleMapping;

    /** @var PrestaShopWebservice webService */
    protected $webService;
    protected $webServiceKey;
    protected $shopDomain;
    protected $url;

    /** @var ClaroHelper */
    public $utils;

    protected $groups;

    public function __construct()
    {
        parent::__construct();
        $this->simpleMapping = new ClaroMapping();
        $this->utils = new ClaroHelper();
        $this->webServiceKey = Configuration::get('CLAROBI_WS_KEY');
        $this->shopDomain = Configuration::get('CLAROBI_WS_DOMAIN');

        $groups = Group::getGroups(1);
        foreach ($groups as $group) {
            $this->groups[$group['id_group']] = $group['name'];
        }
    }

    /**
     * Init PrestaShopWebservice library.
     *
     * @throws PrestaShopException
     */
    public function init()
    {
        parent::init();

        try {
            $this->webService = new PrestaShopWebservice($this->shopDomain, $this->webServiceKey, self::DEBUG);
        } catch (Exception $exception) {
            ClaroLogger::errorLog(__CLASS__ . ':' . __METHOD__ . ' : ' . $exception->getMessage());

            $this->json = [
                'status' => 'error',
                'error' => $exception->getMessage()
            ];
            die(json_encode($this->json));
        }
    }

    /**
     * Get collection.
     * Output and other changes should be made by each controller accordingly.
     */
    public function initContent()
    {
        parent::initContent();

        $from_id = Tools::getValue('from_id');
        $limit = Tools::getValue('limit');

        // Get the collection
        try {
            $this->collection = $this->getCollection($from_id, $limit);

        } catch (Exception $exception) {
            ClaroLogger::errorLog(__CLASS__ . ':' . __METHOD__ . ' : ' . $exception->getMessage());

            $this->json = [
                'status' => 'error',
                'error' => $exception->getMessage()
            ];
            die(json_encode($this->json));
        }

        // Output collection in derived class
    }

    /**
     * Format final json and encrypt data.
     * Must be called by each derived class after specific mapping.
     *
     * @param string $entityName
     * @param null $lastId
     */
    protected function encodeJson($entityName)
    {
        $this->encodedJson = [
            'isEncoded' => true,
            'isCompressed' => true,
            'data' => $this->utils->encode($this->json),
            'license_key' => Configuration::get('CLAROBI_LICENSE_KEY'),
            'entity' => $entityName,
            'type' => 'SYNC'
        ];
    }

    /**
     * Set url params and get collection based on url.
     *
     * @param $from_id
     * @param null $limit
     * @return mixed
     * @throws Exception
     */
    protected function getCollection($from_id, $limit = null)
    {
        $this->params['filter[id]'] = '[' . $from_id . ',' . ($limit ? $limit + $from_id : self::LIMIT + $from_id) . ']';

        return json_decode($this->webService->get([
            'url' => $this->utils->createUrlWithQuery($this->url, $this->params)
        ]));
    }

    /**
     * Get state abbreviation based on id.
     *
     * @param $id_state
     * @return string
     */
    protected function getStateISOFromId($id_state)
    {
        /** @var State $state */
        try {
            $state = new State($id_state);
        } catch (Exception $exception) {
            ClaroLogger::errorLog(__CLASS__ . ':' . __METHOD__ . ' : ' . $exception->getMessage());

            return null;
        }
        return $state->iso_code;
    }

    /**
     * Get address based on id.
     *
     * @param $id_address
     * @return array
     */
    protected function getAddress($id_address)
    {
        /** @var Address $address */
        $address = new Address($id_address);
        $state_code = $this->getStateISOFromId($address->id_state); //iso
        return [
            'postal_code' => $address->postcode,
            'city' => $address->city,
            'state_code' => $state_code,
            'country' => $address->country
        ];
    }

    /**
     * Get specific fields from customer based on id.
     * Needed for order mapping.
     *
     * @param int $id_customer
     * @return array
     */
    protected function getSimpleCustomer($id_customer)
    {
        $customer = new Customer((int)$id_customer);
        return [
            'id' => $customer->id,
            'name' => $customer->firstname . ' ' . $customer->lastname,
            'email' => $customer->email,
            'group' => $this->groups[$customer->id_default_group]
        ];
    }

    /**
     * Get category tree of one product.
     *
     * @param int $id_product
     * @return array
     */
    protected function getCategoryPathTree($id_product)
    {
        try {
            $product = new Product((int)$id_product);
            $categories = $product->getParentCategories();
            $categoriesArray = [];
            foreach ($categories as $category) {
                $categoriesArray[] = [
                    'id' => $category['id_category'],
                    'name' => $category['name']
                ];
            }
            return $categoriesArray;
        } catch (Exception $exception) {
            ClaroLogger::errorLog(__CLASS__ . ':' . __METHOD__ . ' : ' . $exception->getMessage());

            return [];
        }
    }

    /**
     * Get currency code based on id.
     *
     * @param int $id_currency
     * @return string
     */
    protected function getCurrencyISOFromId($id_currency){
        $currency = new Currency((int)$id_currency);
        return $currency->iso_code;
    }
}
