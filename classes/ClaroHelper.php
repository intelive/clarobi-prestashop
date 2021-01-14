<?php
/**
 * 2007-2021 PrestaShop
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
 * @copyright 2007-2021 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 *
 * Don't forget to prefix your containers with your own identifier
 * to avoid any conflicts with others containers.
 */

class ClaroHelper
{
    protected $secret;

    /**
     * ClaroHelper constructor.
     */
    public function __construct()
    {
        $this->secret = Configuration::get('CLAROBI_API_SECRET');
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
            $string .= $characters[mt_rand(0, Tools::strlen($characters) - 1)];
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
        $url .= Tools::substr($query, 0, -1);

        return $url;
    }

    /**
     * Compress encoded data if lib and functions exist.
     *
     * @param $data
     * @return string
     */
    public function compress($data)
    {
        if (extension_loaded('zlib') &&
            function_exists('gzcompress') &&
            function_exists('base64_encode')
        ) {
            return base64_encode(gzcompress(serialize(($data))));
        } else {
            ClaroLogger::errorLog(__METHOD__ . ' : ' . 'Extensions zlib or gzcompress or base64_encode do not exist');
        }

        return false;
    }

    /**
     * Encode data with API_SECRET from configuration.
     *
     * @param $payload
     * @return string
     */
    public function encode($payload)
    {
        $iv = openssl_random_pseudo_bytes(openssl_cipher_iv_length('aes-256-cbc'));
        $encrypted = openssl_encrypt(json_encode($payload), 'aes-256-cbc', $this->secret, 0, $iv);

        return base64_encode($encrypted . '::' . $iv);
    }

    /**
     * Decode data with API_SECRET from configuration.
     *
     * @param $payload
     * @return string
     */
    public function decode($payload)
    {
        list($encryptedData, $iv) = explode('::', $payload, 2);

        return json_decode(openssl_decrypt($encryptedData, 'aes-256-cbc', $this->secret, 0, $iv));
    }
}
