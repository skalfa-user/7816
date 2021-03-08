<?php


class MEMBERX_MCTRL_Search extends MEMBERX_CTRL_Search
{
    public function form()
    {
        $url = OW::getPluginManager()->getPlugin('memberx')->getStaticCssUrl() . 'search.css';
        OW::getDocument()->addStyleSheet($url);

        $mainSearchForm = OW::getClassInstance('MEMBERX_MCLASS_MainSearchForm', $this);
        $mainSearchForm->process($_POST);        
        $this->addForm($mainSearchForm);
        
        $this->setTemplate(OW::getPluginManager()->getPlugin('memberx')->getMobileCtrlViewDir() . 'search_form.html');
        $this->assign('presentationToClass', BASE_MCLASS_JoinFormUtlis::presentationToCssClass());
    }
    
    public function quickSearch()
    {
        $this->addComponent("searchCmp", OW::getClassInstance('MEMBERX_MCMP_QuickSearch'));
        $this->setTemplate(OW::getPluginManager()->getPlugin('memberx')->getMobileCtrlViewDir() . 'search_quick_search.html');
    }
    
    public function searchResult($params, $data = null)
    {
        parent::searchResult($params);
        $orderType = $this->getOrderType($params);
        $this->assign('listLabel', $this->getListLabel($orderType));
        $this->setTemplate(OW::getPluginManager()->getPlugin('memberx')->getMobileCtrlViewDir() . 'search_search_result.html');
        
        $this->assign('noUsersTxt', strip_tags(OW::getLanguage()->text('memberx', 'no_users_found')));
        
        $activeMenu = $this->assignedVars['activeMenu'];
        if ($activeMenu === 'featured_users'){
            $this->removeComponent('searchResultMenu');
        }
        
    }
    
    public function map()
    {
        $searchResultMenu = $this->searchResultMenu(MEMBERX_BOL_Service::LIST_ORDER_WITHOUT_SORT);
        
        if ( !empty($searchResultMenu) )
        {
            $this->addComponent('searchResultMenu', $searchResultMenu);
        }
        
        parent::map();
        $this->setTemplate(OW::getPluginManager()->getPlugin('memberx')->getMobileCtrlViewDir() . 'search_map.html');
    }
    
    protected function getListLabel($order)
    {
        $items = MEMBERX_BOL_Service::getInstance()->getSearchResultMenu($order);
        
        foreach($items as $item) {
            if( !empty($item['isActive']) ) 
            {
                return $item['label'];
            }
        }
        
        return null;
    }
    
    public function searchResultMenu( $orderType )
    {
        $items = MEMBERX_BOL_Service::getInstance()->getSearchResultMenu($orderType);
        
        $list = array();
        $order = 0;
        if ( !empty($items) )
        {
            foreach( $items as $item ) {
                $list[] = array( 
                    'label' => $item['label'], 
                    'href' => $item['url'],
                    'class' => '', 
                    'order' => $order++
                );
            }
        }
        
        $list[] = array( 
                    'label' => OW::getLanguage()->text('memberx', 'map'), 
                    'href' => OW::getRouter()->urlForRoute('memberx.map'),
                    'class' => '', 
                    'order' => $order++
                );
            
        $list[] = array( 
                    'label' => OW::getLanguage()->text('memberx', 'new_search'), 
                    'href' => OW::getRouter()->urlForRoute('users-search'),
                    'class' => '', 
                    'order' => $order++
                );
        
        $actions = new BASE_MCMP_ContextAction($list);
        
        return $actions;
    }
}