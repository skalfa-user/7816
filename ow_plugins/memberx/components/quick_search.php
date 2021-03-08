<?php


class MEMBERX_CMP_QuickSearch extends OW_Component
{
    public function __construct()
    {
        parent::__construct();

        $form = OW::getClassInstance('MEMBERX_CLASS_QuickSearchForm', $this);
        $this->addForm($form);

        $this->assign('form', $form);
        $this->assign('advancedUrl', OW::getRouter()->urlForRoute('users-search'));
        $this->assign('questions', MEMBERX_BOL_Service::getInstance()->getQuickSerchQuestionNames());
    }
}
