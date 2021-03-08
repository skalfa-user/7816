<?php

class USERTAGS_CTRL_Save extends OW_ActionController
{

    public function index( $params = array() )
    {
        if (OW::getRequest()->isAjax())
        {
            exit();
        }

        if ( !OW::getUser()->isAuthenticated() )
        {
            throw new AuthenticateException();
        }

        if ( !OW::getUser()->isAuthorized('usertags', 'add_tags') )
        {
           throw new Redirect404Exception();
        }

        $plugin = OW::getPluginManager()->getPlugin('usertags');
//        OW::getNavigation()->activateMenuItem(OW_Navigation::MAIN, 'blogs', 'main_menu_item');

        $this->setPageHeading(OW::getLanguage()->text('usertags', 'save_page_heading'));
        $this->setPageHeadingIconClass('ow_ic_write');

        $id = empty($params['id']) ? 0 : $params['id'];

        $tagService = BOL_TagService::getInstance();

        $form = new SaveForm();

        if ( OW::getRequest()->isPost() && $form->isValid($_POST) )
        {
            $form->process($this);
            OW::getApplication()->redirect(OW::getRouter()->urlForRoute('base_user_profile', array('username' => BOL_UserService::getInstance()->getUserName(OW::getUser()->getId()))));
        }

        $this->addForm($form);
    }

}

class SaveForm extends Form
{
    public function __construct( $tags = array() )
    {
        parent::__construct('save');

        $tagService = BOL_TagService::getInstance();

        $tags = array();

        $arr = $tagService->findEntityTags(OW::getUser()->getId(), 'usertags');

        foreach ( (!empty($arr) ? $arr : array() ) as $dto )
        {
            $tags[] = $dto->getLabel();
        }

        $tf = new TagsInputField('tf');
        $tf->setLabel(OW::getLanguage()->text('usertags', 'tags_field_label'));
        $tf->setValue($tags);

        $this->addElement($tf);

        $saveSubmit = new Submit('save');
        $this->addElement($saveSubmit);
    }

    public function process( $ctrl )
    {
        $data = $this->getValues();

        $tags = array();
        $tags = $data['tf'];

        foreach ($tags as $id => $tag)
        {
            $tags[$id] = UTIL_HtmlTag::stripTags($tag);
        }

        $tagService = BOL_TagService::getInstance();
        $tagService->updateEntityTags(OW::getUser()->getId(), 'usertags', $tags );

        $tagService->setEntityStatus('usertags', OW::getUser()->getId(), true);
    }
}

?>
