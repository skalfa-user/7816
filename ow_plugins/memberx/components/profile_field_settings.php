<?php


class MEMBERX_CMP_ProfileFieldSettings extends MEMBERX_CLASS_AbsSampleSetting{
    
    const CONFIG_KEY_PREFIX = 'profile-fields-';
    public $accountType;
    public $questionList;
    


    public function __construct($accountType) {
        parent::__construct();
        $this->accountType = $accountType;
        $this->questionList = BOL_QuestionService::getInstance()->findAllQuestionsForAccountType($accountType);
    }
    
    const FORM_NAME = 'memberx-profle-field-settings';
    
    public function getTitle() {
        return $this->langs->text(self::PLUGIN_KEY, 'profile_field_setting');
    }
    
    public function getConfigKey() {
        $configKey = self::CONFIG_KEY_PREFIX . $this->accountType;
        if (!OW::getConfig()->configExists(self::PLUGIN_KEY, $configKey)){
            OW::getConfig()->addConfig(self::PLUGIN_KEY, $configKey, json_encode(array('username' => 1)));
        }
        return $configKey;
    }

    public function getKeyArray() {
        
        $questionNames = array();
        foreach($this->questionList as $question){
            $questionNames[$question['name']] = 0;
        }
        
        return $questionNames;
    }
    
    public function getLabelArray() {
        $questionWithLabels = array();
        foreach($this->questionList as $question){
            $questionWithLabels[$question['name']] = BOL_QuestionService::getInstance()->getQuestionLang($question['name']);
        }
        
        return $questionWithLabels;
    }


    public function getValueArray() {
        $configKey = $this->getConfigKey();
        return json_decode(OW::getConfig()->getValue(self::PLUGIN_KEY, $configKey), true);
    }
    
    public function getFromName() {
        return self::FORM_NAME;
    }
    
}