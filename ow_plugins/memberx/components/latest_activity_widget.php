<?php


class MEMBERX_CMP_LatestActivityWidget extends BASE_CLASS_Widget
{
    public function __construct( BASE_CLASS_WidgetParameter $paramObj )
    {
        parent::__construct();

        $activityCmp = new MEMBERX_CMP_MemberWidget();
        $newestCmp = new MEMBERX_CMP_MemberWidget('newest');
        $distanceCmp = new MEMBERX_CMP_MemberWidget('distance');
        
        $newestHtml = $newestCmp->render();
        $newestHtml = '<div class="memberx_widget_newest_user ow_hidden" >' .  $newestHtml . '</div>';
        
        $activityHtml = $activityCmp->render();
        $activityHtml = '<div class="memberx_widget_latest_activity" >' .  $activityHtml . '</div>';
        
        $distanceHtml = $distanceCmp->render();
        $distanceHtml = '<div class="memberx_widget_distance_user ow_hidden" >' .  $distanceHtml . '</div>';
        
      
        $this->assign('cmp', $newestHtml . $activityHtml. $distanceHtml);
        $this->setVisible($activityCmp->numberOfItems);
        
        $orderMenu = $this->searchResultMenu('order_latest_activity');
        if ($orderMenu){
            $this->addComponent('orderMenu', $orderMenu);
        }
        
    }

    public static function getAccess()
    {
        return self::ACCESS_ALL;
    }

    public static function getStandardSettingValueList()
    {
        return array(
            self::SETTING_SHOW_TITLE => true,
            self::SETTING_TITLE => OW::getLanguage()->text('memberx', 'latest_activity_users'),
            self::SETTING_WRAP_IN_BOX => true,
            self::SETTING_ICON => BASE_CLASS_Widget::ICON_USER,
        );
    }
    
    public function searchResultMenu( $order )
    {
        $items = MEMBERX_BOL_Service::getInstance()->getSearchResultMenu(MEMBERX_BOL_Service::LIST_ORDER_LATEST_ACTIVITY);
        $newItems = array();
        foreach($items as $key => $item){
            if ($item['key'] === '' ){
                continue;
            }
            $item['url'] = 'javascript://' . $item['key'];
            $newItems[$key] = $item;
        }
        
        if ( !empty($newItems) )
        {
            return new BASE_CMP_SortControl($newItems);
        }
        
        return null;
    }
}