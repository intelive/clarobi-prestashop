<?php

include(_PS_MODULE_DIR_. 'clarobi/classes/ClaroHelper.php');

class ClaroConfig
{
    /**
     * Available configurations for this module.
     */
    const CONFIG = [
        'CLAROBI_API_KEY',
        'CLAROBI_API_SECRET',
        'CLAROBI_WS_KEY',
        'CLAROBI_WS_DOMAIN',
        'CLAROBI_LICENSE_KEY'
    ];

    /**
     * Remove all configurations on uninstall.
     */
    public static function removeAllConfig()
    {
        foreach (self::CONFIG as $configKey){
            Configuration::deleteByName($configKey);
        }
    }

    public static function setConfig(){
        Configuration::updateValue('CLAROBI_WS_KEY', ClaroHelper::getRandomString(32));
    }
}
