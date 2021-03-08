<?php


class MEMBERX_MCLASS_QuickSearchForm extends MEMBERX_CLASS_QuickSearchForm
{
    public function __construct( $controller )
    {
        parent::__construct($controller);
        
        $questionNameList = $this->searchService->getQuickSerchQuestionNames();
        $questionDtoList = BOL_QuestionService::getInstance()->findQuestionByNameList($questionNameList);
        
        $list = json_decode(json_encode($questionDtoList), true);
        
        BASE_MCLASS_JoinFormUtlis::setLabels($this, $list);
        BASE_MCLASS_JoinFormUtlis::setInvitations($this, $list);
        BASE_MCLASS_JoinFormUtlis::setColumnCount($this);
    }
}