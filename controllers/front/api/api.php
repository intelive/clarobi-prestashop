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
    protected $params;

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

    public function init()
    {
        parent::init();
        try {
            $this->webService = new PrestaShopWebservice($this->shopDomain, $this->webServiceKey, self::DEBUG);
        } catch (PrestaShopWebserviceException $e) {
            ClaroLogger::errorLog(__METHOD__. ' : '. $e->getMessage());
            $this->json = [
                'status' => 'error',
                'message' => $e->getMessage()
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
        $this->params = [
            'display' => 'full',
            'filter[id]' => '[' . $from_id . ',' . ($limit ? $limit + $from_id : self::LIMIT + $from_id) . ']',
            'output_format' => 'JSON'
        ];

        // Get the collection
        try {
            $this->collection = json_decode($this->webService->get([
                'url' => $this->utils->createUrlWithQuery($this->url, $this->params)
            ]));
        } catch (PrestaShopWebserviceException $e) {
            ClaroLogger::errorLog(__METHOD__. ' : '. $e->getMessage());

            $this->json = ['status' => 'error',
                'message' => $e->getMessage()
            ];
            die(json_encode($this->json));
        }

        // Output collection in specific class
    }

    /**
     * Encode json.
     * Must be called be each "dedicated" class after specific mapping.
     */
    protected function encodeJson()
    {
        $this->encodedJson = [
            'isEncoded' => true,
            'isCompressed' => true,
            'data' => $this->utils->encode($this->json),
            'license_key'=>Configuration::get('CLAROBI_LICENSE_KEY'),
            'entity'=>'',
            'type'=>'SYNC'
        ];
    }
}