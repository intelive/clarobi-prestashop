<?php

include('api/api.php');

class ClarobiExchangerateModuleFrontController extends ClarobiApiModuleFrontController
{
    public function __construct()
    {
        parent::__construct();
        $this->url = $this->shopDomain . '/api/currencies';
    }

    public function init()
    {
        parent::init();
    }

    public function initContent()
    {
        try {
            $this->json = [
                'date' => date('Y-m-d H:i:s', time()),
                'data' => []
            ];

            // todo other way of setting base currency is to provide input in configuration form
            $base_currency = Currency::getDefaultCurrency();

            $this->collection = $this->getCollection();

            /** @var Currency $currency */
            foreach ($this->collection->currencies as $currency) {
                // Set to json
                $this->json['data'][] = [
                    'id' =>$currency->id,
                    'from_currency' => $currency->iso_code,
                    'base_currency' => ($base_currency ? $base_currency->iso_code : null),
                    'rate' => $currency->conversion_rate,
                    'created_at' => null,   // no date found
                    'updated_at' => null
                ];
            }

            // call encoder
            $this->encodeJson('exchange_rate','EXCHANGE_RATE');

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