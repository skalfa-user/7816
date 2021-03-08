<?php



abstract class MEMBERX_CLASS_AbsEditionForm extends MEMBERX_CLASS_AbsComponent{
    
    const FIELD_SUBMIT = 'submit';

    public function __construct($id = null) {
        parent::__construct();
        $this->setTemplate(OW::getPluginManager()->getPlugin(self::PLUGIN_KEY)->getViewDir() . 'components/abs_edition_form.html');
    
        $form = $this->createForm();
        $submit = new Submit(self::FIELD_SUBMIT);
        $form->addElement($submit);
        
        $this->assign('title', $this->getTitle());
        $this->assign('formName', $this->getFromName());
        $this->assign('elementList', $form->getElements());
        $this->addForm($form);
    }
    
    abstract public function getTitle();
    abstract public function getFromName();
    abstract public function createForm();
    abstract public function getConfigKey();
    
    public function process(){
        $form = $this->createForm();
        if (!$form->isValid($_POST)){
            return false;
        }
        
        $values = $form->getValues();
        unset($values['form_name']);
        unset($values['csrf_token']);
        
        if (empty($values)){
            $values = array();
        }
        
        $config = OW::getConfig();
        
        if (!$config->configExists(self::PLUGIN_KEY, $this->getConfigKey())){
            $config->addConfig(self::PLUGIN_KEY, $this->getConfigKey(), json_encode($values));
        }else{
            $config->saveConfig(self::PLUGIN_KEY, $this->getConfigKey(), json_encode($values));
        }
        
        return true;
    }
    
    public static function getConfig($configKey){
        $config = OW::getConfig();
        
        if (!$config->configExists(self::PLUGIN_KEY, $configKey)){
            $config->addConfig(self::PLUGIN_KEY, $configKey, json_encode(array()));
        }
        
        return json_decode($config->getValue(self::PLUGIN_KEY, $configKey), true);
    }
    
}