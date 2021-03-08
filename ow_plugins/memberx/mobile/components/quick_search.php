<?php


class MEMBERX_MCMP_QuickSearch extends OW_Component
{
    public function __construct()
    {
        parent::__construct();

        $form = OW::getClassInstance('MEMBERX_MCLASS_QuickSearchForm', $this);
        
        $this->addForm($form);

        $this->assign('form', $form);
        $this->assign('advancedUrl', OW::getRouter()->urlForRoute('users-search'));
        $this->assign('questions', MEMBERX_BOL_Service::getInstance()->getQuickSerchQuestionNames());
        
        $this->assign('presentationToClass', BASE_MCLASS_JoinFormUtlis::presentationToCssClass());
        
        $this->setTemplate(OW::getPluginManager()->getPlugin('memberx')->getMobileCmpViewDir().'quick_search.html');
    }
}
