<?php



abstract class MEMBERX_CLASS_AbsSampleSetting extends MEMBERX_CLASS_AbsComponent{
    
    const FORM_NAME = 'help_setting_form';
    const FIELD_SUBMIT = 'submit';
    public $configLinks = array();
    public $form;

    public function __construct() {
        parent::__construct();
        $this->assign('writable', true);
    }
    
    public function instatiate(){
        
        $this->setTemplate($this->plugin->getCmpViewDir() . 'abs_sample_setting.html');
        
        $this->form = $this->createForm();
        $this->assign('title', $this->getTitle());
        $this->assign('configLink', $this->configLinks);
        $this->assign('formName', $this->getFromName());
        $this->assign('elementList', $this->form->getElements());
        $this->addForm($this->form);
        return $this;
    }
    
    abstract public function getTitle();
    abstract public function getFromName();
    abstract public function getKeyArray();
    abstract public function getLabelArray();
    abstract public function getValueArray();
    abstract public function getConfigKey();

    public function setConfigLink($configLinks){
        $this->configLinks = $configLinks;
    }
    
    public function addConfigLink($key, $text, $link){
        $this->configLinks[$key] = self::createConfigLinkItem($text, $link);
    }
    
    public static function createConfigLinkItem($text, $link){
        return array(
            'text' => $text,
            'link' => $link
        );
    }
    
    public function setWritable($writable){
        if (!$this->form){
            return;
        }
        
        if (empty($this->form->getElements())){
            return;
        }
        
        if (!$writable){
            foreach($this->form->getElements() as $element){
                $element->addAttribute('readonly', 'readonly');
                $element->addAttribute('disabled', 'disabled');
            }
        }else{
            foreach($this->form->getElements() as $element){
                $element->removeAttribute('readonly');
                $element->removeAttribute('disabled');
            }
        }
        
        $this->assign('writable', $writable);
    }


    public function createForm(){
       

        $form = new Form($this->getFromName());
        
        $fields = $this->getKeyArray();
        $values = $this->getValueArray();
        $labels = $this->getLabelArray();

        foreach ($fields as $key =>  $value){
            $checkBox = new CheckboxField($key);
            if ($labels){
                $checkBox->setLabel($labels[$key]);
            }else{
                $checkBox->setLabel(OW::getLanguage()->text(self::PLUGIN_KEY, $key));
            }
            
            if (isset($values[$key]) && (int)$values[$key]){
                $checkBox->setValue ('on');
                
            }
            
            $form->addElement($checkBox);
        }
        
        $submit = new Submit(self::FIELD_SUBMIT);
        $form->addElement($submit);
        
        return $form;
    }
    
    public  function process(){
        
        $keys = $this->getKeyArray();
        
        foreach ($keys as $key => $value){
            if (array_key_exists($key, $_POST)){
                
                $keys[$key] = (int)((bool)$_POST[$key]);
            }
        }
        
        if (!OW::getConfig()->configExists(self::PLUGIN_KEY, $this->getConfigKey())){
            OW::getConfig()->addConfig(self::PLUGIN_KEY, $this->getConfigKey(), json_encode(array()));
        }
        
        OW::getConfig()->saveConfig(self::PLUGIN_KEY, $this->getConfigKey(), json_encode($keys));
        
        
        return true;
        
    }
    
}