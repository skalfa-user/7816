<?php


class MEMBERX_MCMP_SearchResultList extends MEMBERX_CMP_SearchResultList
{
    public function __construct( $items, $page, $orderType = null, $actions = false )
    {
        parent::__construct($items, $page, $orderType, $actions);
        $this->setTemplate(OW::getPluginManager()->getPlugin('memberx')->getMobileCmpViewDir() . 'search_result_list_updown.html');
        $this->assign('orderType', strip_tags($orderType));
    }
}