<?php



class MEMBERX_CMP_HighlightRoleSettings extends MEMBERX_CLASS_AbsSampleSetting{
    
    
    const CONFIG_KEY = 'memberx-highlighted-roles';
    public $roleList;
    

    public function __construct() {
        parent::__construct();
        $this->roleList = BOL_AuthorizationService::getInstance()->getRoleList();
    }
    
    const FORM_NAME = 'memberx-highlighted-roles-settings';
    
    public function getTitle() {
        return $this->langs->text(self::PLUGIN_KEY, 'highlighted_roles');
    }
    
    public function getConfigKey() {
        $configKey = self::CONFIG_KEY;
        if (!OW::getConfig()->configExists(self::PLUGIN_KEY, $configKey)){
            OW::getConfig()->addConfig(self::PLUGIN_KEY, $configKey, json_encode(array()));
        }
        return $configKey;
    }

    public function getKeyArray() {
        
        $roleNames = array();
        
        if (!empty($this->roleList)){
            foreach ($this->roleList as $item){
                if ($item->name === 'guest'){
                    continue;
                }
                
                $roleNames[$item->name] = 0;
            }
            
        }
        
        return $roleNames;
    }
    
    public function getLabelArray() {
        $labels = array();
        foreach($this->roleList as $role){
            $labels[$role->name] = BOL_AuthorizationService::getInstance()->getRoleLabel($role->name);
        }
        
        return $labels;
    }


    public function getValueArray() {
        $configKey = $this->getConfigKey();
        return json_decode(OW::getConfig()->getValue(self::PLUGIN_KEY, $configKey), true);
    }
    
    public function getFromName() {
        return self::FORM_NAME;
    }
    
}