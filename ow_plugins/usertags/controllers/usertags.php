<?php

class USERTAGS_CTRL_Usertags extends OW_ActionController
{
    /**
     * @var OW_PluginManager
     */
    private $plugin;
    /**
     * @var string
     */

    private $menu;
    private $service;
    private $pluginJsUrl;
    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();

        $this->plugin = OW::getPluginManager()->getPlugin('usertags');
        $this->pluginJsUrl = $this->plugin->getStaticJsUrl();
	$this->menu = BASE_CTRL_UserList::getMenu('usertags');
	$this->service = USERTAGS_BOL_Service::getInstance();
    }

    public function viewTaggedList( array $params = null )
    {
        if ( isset($params['tag']) )
        {
            $tag = htmlspecialchars(urldecode($params['tag']));
        }
        
        $this->addComponent('menu', $this->menu);

        $this->menu->getElement('usertags')->setActive(true);

        $this->setTemplate(OW::getPluginManager()->getPlugin('usertags')->getCtrlViewDir() . 'user_view_list-tagged.html');

        $listUrl = OW::getRouter()->urlForRoute('users-by-tags');

        OW::getDocument()->addScript($this->pluginJsUrl . 'usertags_tag_search.js');

        $objParams = array(
            'listUrl' => $listUrl
        );

        $script =
            "$(document).ready(function(){
                var usertagsSearch = new usertagsTagSearch(" . json_encode($objParams) . ");
            }); ";

        OW::getDocument()->addOnloadScript($script);

        $configs = OW::getConfig()->getValues('usertags');
	$tagsLimit = isset($configs['tags_in_cloud']) ? $configs['tags_in_cloud'] : 20;
        $tags = new BASE_CMP_EntityTagCloud('usertags', '', (int)$tagsLimit);
        $tags->setRouteName('users-by-tags_list');
        $this->addComponent('tags', $tags);            

        if ( isset($tag) )
        {
            $this->assign('tag', $tag);
            $usersPerPage = (int)OW::getConfig()->getValue('base', 'users_count_on_page');
            $page = (!empty($_GET['page']) && intval($_GET['page']) > 0 ) ? intval($_GET['page']) : 1;

            $list = USERTAGS_BOL_Service::getInstance()->findUsersByTags(($page - 1) * $usersPerPage, $usersPerPage, $tag);
            $itemCount = USERTAGS_BOL_Service::getInstance()->countUsersByTags($tag);

            $cmp = new BASE_Members($list, $itemCount, $usersPerPage, true, 'usertags');
            $this->addComponent('cmp', $cmp);

            OW::getDocument()->setTitle(OW::getLanguage()->text('usertags', 'meta_title_usertags_tagged_as', array('tag' => $tag)));
            OW::getDocument()->setDescription(OW::getLanguage()->text('usertags', 'meta_description_usertags_tagged_as', array('tag' => $tag)));
        }
        else
        {
            OW::getDocument()->setTitle(OW::getLanguage()->text('usertags', 'meta_title_usertags_tagged'));
            $tagsArr = BOL_TagService::getInstance()->findMostPopularTags('usertags', (int)$tagsLimit);
            foreach ( $tagsArr as $t )
            {
                $labels[] = $t['label'];
            }
            $tagStr = $tagsArr ? implode(', ', $labels) : '';
            OW::getDocument()->setDescription(OW::getLanguage()->text('usertags', 'meta_description_usertags_tagged', array('topTags' => $tagStr)));

        }

        $this->assign('listType', 'tagged');

        OW::getDocument()->setHeading(OW::getLanguage()->text('usertags', 'page_title_browse_users'));
        OW::getDocument()->setHeadingIconClass('ow_ic_tag');
    }

}