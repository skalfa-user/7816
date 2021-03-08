<?php


class MEMBERX_MCLASS_MainSearchForm extends MEMBERX_CLASS_MainSearchForm
{
    /**
     * @param OW_ActionController $controller
     */
    public function __construct( $controller )
    {
        parent::__construct($controller);
        $questionService = BOL_QuestionService::getInstance();
        
        $list = $questionService->findSearchQuestionsForAccountType('all');
        
        BASE_MCLASS_JoinFormUtlis::setLabels($this, $list);
        BASE_MCLASS_JoinFormUtlis::setInvitations($this, $list);
        BASE_MCLASS_JoinFormUtlis::setColumnCount($this);
    }
}