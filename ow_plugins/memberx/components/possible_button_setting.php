<?php


class MEMBERX_CMP_PossibleButtonSetting extends MEMBERX_CLASS_AbsSampleSetting{
    
    
    const CONFIG_KEY = 'memberx-possible-button';
    const FORM_NAME = 'memberx-possible-buttons';

    public function __construct() {
        parent::__construct();
    }
    
    public function getTitle() {
        return $this->langs->text(self::PLUGIN_KEY, 'possible_buttons');
    }
    
    public function getConfigKey() {
        $configKey = self::CONFIG_KEY;
        if (!OW::getConfig()->configExists(self::PLUGIN_KEY, $configKey)){
            OW::getConfig()->addConfig(self::PLUGIN_KEY, $configKey, json_encode(array()));
        }
        return $configKey;
    }

    public function getKeyArray() {
        
        $keys = array(
            'chat' => 0,
            'mail' => 0,
            'virtual_gift' => 0,
            'invite_to_event' => 0,
            'invite_to_group' => 0,
            'video_call' => 0,
            'wink' => 0,
            'bookmark' => 0
        );
        
        return $keys;
    }
    
    public function getLabelArray() {
        return NULL;
    }


    public function getValueArray() {
        $configKey = $this->getConfigKey();
        return json_decode(OW::getConfig()->getValue(self::PLUGIN_KEY, $configKey), true);
    }
    
    public function getFromName() {
        return self::FORM_NAME;
    }
    
    public static function getSavedValue(){
        $configKey = self::CONFIG_KEY;
        if (!OW::getConfig()->configExists(self::PLUGIN_KEY, $configKey)){
            OW::getConfig()->addConfig(self::PLUGIN_KEY, $configKey, json_encode(array()));
        }
        
        return json_decode(OW::getConfig()->getValue(self::PLUGIN_KEY, $configKey), true);
    }
    
    public static function getBoolean($configKey){
        $config = self::getSavedValue();
        
        if (isset($config[$configKey]) && $config[$configKey]){
            return true;
        }else{
            return false;
        }
    }
    
    
}