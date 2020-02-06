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

    public function __construct()
    {
        parent::__construct();
        $this->simpleMapping = new ClaroMapping();
        $this->utils = new ClaroHelper();
        $this->webServiceKey = Configuration::get('CLAROBI_WS_KEY');
        $this->shopDomain = Configuration::get('CLAROBI_WS_DOMAIN');
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
            ClaroLogger::errorLog(__METHOD__ . ' : ' . $exception->getMessage());
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
            ClaroLogger::errorLog(__METHOD__ . ' : ' . $exception->getMessage());

            $this->json = [
                'status' => 'error',
                'error' => $exception->getMessage()
            ];
            die(json_encode($this->json));
        }

        // Output collection in derived class
    }

    /**
     * Set url params and get collection based on url.
     *
     * @param $from_id
     * @param null $limit
     * @return mixed
     * @throws PrestaShopWebserviceException
     */
    protected function getCollection($from_id, $limit = null)
    {
        $this->params['filter[id]'] = '[' . $from_id . ',' . ($limit ? $limit + $from_id : self::LIMIT + $from_id) . ']';

        return json_decode($this->webService->get([
            'url' => $this->utils->createUrlWithQuery($this->url, $this->params)
        ]));
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
}
