<?php

class ClaroHelper
{
    /**
     * ClaroHelper constructor.
     */
    public function __construct()
    {
    }

    /**
     * Return random string with specific length.
     * $type values: 0 - numbers
     *               1 - characters
     *               2 - alphanumeric
     *
     * @param int $length
     * @param int $type
     * @return string
     */
    public static function getRandomString($length = 25, $type = 2)
    {
        switch ($type) {
            case 0:
                $characters = '0123456789';
                break;
            case 1:
                $characters = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
            default:
                $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
                break;
        }
        $string = '';

        for ($i = 0; $i < $length; $i++) {
            $string .= $characters[mt_rand(0, strlen($characters) - 1)];
        }

        return $string;
    }

    /**
     * @param string $url
     * @param array $params
     * @return string
     */
    public function createUrlWithQuery($url, $params)
    {
        $query = '?';
        foreach ($params as $param => $value) {
            $query .= $param . '=' . $value . '&';
        }
        $url .= substr($query, 0, -1);
        return $url;
    }

    /**
     * @param $payload
     * @return string
     */
    public function encode($payload)
    {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt(json_encode($payload), 'aes-256-cbc',Configuration::get('CLAROBI_API_SECRET'), 0, $iv);

        return base64_encode($encrypted . '::' . $iv);
    }

    /**
     * @param $payload
     * @return string
     */
    public function decode($payload)
    {
        list($encryptedData, $iv) = explode('::', base64_decode($payload), 2);
        return json_decode(openssl_decrypt($encryptedData, 'aes-256-cbc', Configuration::get('CLAROBI_API_SECRET'), 0, $iv));
    }
}
