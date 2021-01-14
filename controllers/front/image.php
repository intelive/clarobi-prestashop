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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2021 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*
* Don't forget to prefix your containers with your own identifier
* to avoid any conflicts with others containers.
*/

include('api/api.php');

class ClarobiImageModuleFrontController extends ModuleFrontController
{
    const ERR = 'ERROR:';
    const HEADER_UNITYREPORTS = 'UnityReports: OK';
    const HEADER_CLAROBI = 'ClaroBI: OK';

    protected $id = 0;
    // Add claro default image type - since presta doesn't know about it
    protected $image_types = ['clarobi_default'];

    public function __construct()
    {
        parent::__construct();
    }

    public function init()
    {
        try {
            $this->id = Tools::getValue('id');
            if (!$this->id) {
                die(self::ERR . 'Missing prod ID');
            }

            // Get image type based on name(partial name) and complete(formatted) name is returned
            $this->image_types[] = ImageType::getFormattedName('small');
            $this->image_types[] = ImageType::getFormattedName('cart');
            $this->image_types[] = ImageType::getFormattedName('category');
        } catch (Exception $exception) {
            die(self::ERR . $exception->getMessage());
        }
    }

    public function initContent()
    {
        try {
            $product = new Product((int)$this->id, false, 1);

            // If returned product is null (all fields are set to null)
            if (!$product->id) {
                die(self::ERR . 'Cannot load product');
            }

            // Get image link
            $image_id = $product->getCover($product->id);    // Get image id from product id

            if (!$image_id) {
                die(self::ERR . 'No image found for this product!');
            }

            // Return complete image link
            $link = new Link();

            // Try to get one image
            foreach ($this->image_types as $resizeType) {
                $img_url = $link->getImageLink(
                    isset($product->link_rewrite) ? $product->link_rewrite : $product->name,
                    (int)$image_id['id_image'],
                    $resizeType
                );
                $path = $img_url;

                // Content
                $content = @readfile('http://' . $img_url);
                if (!$content) {
                    echo $resizeType . 'not found';
                    continue;
                }
                // Output image
                $ext = Tools::strtolower(pathinfo($path, PATHINFO_EXTENSION));
                switch ($ext) {
                    case 'gif':
                        $type = 'image/gif';
                        break;
                    case 'jpg':
                    case 'jpeg':
                        $type = 'image/jpeg';
                        break;
                    case 'png':
                        $type = 'image/png';
                        break;
                    default:
                        $type = 'unknown';
                        break;
                }

                if ($type == 'unknown') {
                    continue;
                } else {
                    if ($type != 'unknown') {
                        header('Content-Type:' . $type);
                        header(self::HEADER_UNITYREPORTS);
                        header(self::HEADER_CLAROBI);
                        echo $content;
                    }
                    die();
                }
            }
            die('KO - Image not found');
        } catch (Exception $exception) {
            die(self::ERR . $exception->getMessage());
        }
    }
}
