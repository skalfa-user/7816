<?php

OW::getRouter()->addRoute( 
    new OW_Route('usertags.admin', 'admin/plugins/usertags', 'USERTAGS_CTRL_Admin', 'index')
);

function usertags_add_userlist_data( BASE_CLASS_EventCollector $event )
{
    $event->add(
        array(
            'label' => OW::getLanguage()->text('usertags', 'user_list_menu_item_usertags'),
            'url' => OW::getRouter()->urlForRoute('base_user_lists', array('list' => 'usertags')),
            'iconClass' => 'ow_ic_tag',
            'key' => 'usertags',
            'order' => 5,
            'dataProvider' => array(USERTAGS_BOL_Service::getInstance(), 'getUserListData')
        )
    );
}
OW::getEventManager()->bind('base.add_user_list', 'usertags_add_userlist_data');
$router = OW::getRouter();
//$router->addRoute(new OW_Route('users-by-tags', 'users/usertags/', 'USERTAGS_CTRL_Usertags', 'viewTaggedList'));
$router->addRoute(new OW_Route('users-by-tags', 'users/usertags/', 'USERTAGS_CTRL_Usertags', 'viewTaggedList'));
$router->addRoute(new OW_Route('users-by-tags_list', 'users/usertags/:tag', 'USERTAGS_CTRL_Usertags', 'viewTaggedList'));
$router->addRoute(new OW_Route('usertags-edit', 'users/usertags_edit', "USERTAGS_CTRL_Save", 'index'));

function usertags_add_auth_labels( BASE_CLASS_EventCollector $event )
{
    $language = OW::getLanguage();
    $event->add(
        array(
            'usertags' => array(
                'label' => $language->text('usertags', 'auth_group_label'),
                'actions' => array(
                    'add_tags' => $language->text('usertags', 'auth_action_label_add_tags')
                )
            )
        )
    );
}

OW::getEventManager()->bind('admin.add_auth_labels', 'usertags_add_auth_labels');
