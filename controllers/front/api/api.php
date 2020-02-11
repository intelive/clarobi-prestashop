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
    protected $collItems;
    protected $lastId;

    protected $params = [
        'display' => 'full',
        'output_format' => 'JSON',
        'sort' => 'id_ASC'    // return last id = fromId otherwise
    ];

    /** @var ClaroMapping */
    protected $simpleMapping;
    /** @var PrestaShopWebservice webService */
    protected $webService;
    /** @var ClaroHelper */
    public $utils;

    protected $webServiceKey;
    protected $shopDomain;
    protected $url;

    // Customers groups as id => name array
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
            ClaroLogger::errorLog(__METHOD__ . ' : ' . $exception->getMessage() . ' at line ' . $exception->getLine());

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

        // Get the collection
        try {
            if (Tools::getIsset('from_id')) {
                $from_id = Tools::getValue('from_id');
            } else {
                throw new Exception('Parameter \'from_id\' not found!');
            }

            // todo make collection to return 50 items not filter[id]=[fromId, fromId+50] - incorrect
            $this->collection = $this->getCollection($from_id, null);

        } catch (Exception $exception) {
            ClaroLogger::errorLog(__METHOD__ . ' : ' . $exception->getMessage() . ' at line ' . $exception->getLine());

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
        $data = $this->json;
        $responseIsEncoded = $responseIsCompressed = false;

        // Encode and compress the data only if we have it
        if (!empty($data)) {
            $encoded = $this->utils->encode($data);

            if (is_string($encoded)) {
                $responseIsEncoded = true;
                $data = $encoded;
            }

            $compressed = $this->utils->compress($encoded);
            if ($compressed) {
                $responseIsCompressed = true;
                $data = $compressed;
            }
        }

        $this->encodedJson = [
            'isEncoded' => $responseIsEncoded,
            'isCompressed' => $responseIsCompressed,
            'data' => $data,
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
//        $this->params['limit'] = ($from_id == 0 ? $from_id : $from_id - 1) . ',' . ($limit ? $limit : self::LIMIT);

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
            ClaroLogger::errorLog(__METHOD__ . ' : ' . $exception->getMessage() . ' at line ' . $exception->getLine());

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
            ClaroLogger::errorLog(__METHOD__ . ' : ' . $exception->getMessage() . ' at line ' . $exception->getLine());

            return [];
        }
    }

    /**
     * Get currency code based on id.
     *
     * @param int $id_currency
     * @return string
     */
    protected function getCurrencyISOFromId($id_currency)
    {
        $currency = new Currency((int)$id_currency);
        return $currency->iso_code;
    }
}
