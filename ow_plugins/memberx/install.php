<?php



$path = OW::getPluginManager()->getPlugin('memberx')->getRootDir() . 'langs.zip';
OW::getLanguage()->importPluginLangs($path, 'memberx');

if ( !OW::getConfig()->configExists('memberx', 'quick_search_fields') )
{
    OW::getConfig()->addConfig('memberx', 'quick_search_fields', '');
}

if ( !OW::getConfig()->configExists('memberx', 'order_latest_activity') )
{
    OW::getConfig()->addConfig('memberx', 'order_latest_activity', 1);
}

if ( !OW::getConfig()->configExists('memberx', 'order_recently_joined') )
{
    OW::getConfig()->addConfig('memberx', 'order_recently_joined', 1);
}

if ( !OW::getConfig()->configExists('memberx', 'order_match_compatibitity') )
{
    OW::getConfig()->addConfig('memberx', 'order_match_compatibitity', 1);
}

if ( !OW::getConfig()->configExists('memberx', 'order_distance') )
{
    OW::getConfig()->addConfig('memberx', 'order_distance', 1);
}

if ( !OW::getConfig()->configExists('memberx', 'hide_user_activity_after') )
{
    OW::getConfig()->addConfig('memberx', 'hide_user_activity_after', 400);
}

if ( !OW::getConfig()->configExists('memberx', 'enable_username_search') )
{
    OW::getConfig()->addConfig('memberx', 'enable_username_search', 1);
}


$possibleButtons = array(
            'chat' => 1,
            'mail' => 1,
            'virtual_gift' => 1,
            'invite_to_event' => 1
);

if (!OW::getConfig()->configExists('memberx', 'memberx-possible-button')){
    OW::getConfig()->addConfig('memberx', 'memberx-possible-button', json_encode($possibleButtons));
}

OW::getPluginManager()->addPluginSettingsRouteName('memberx', 'memberx' . '.admin_general_setting');
