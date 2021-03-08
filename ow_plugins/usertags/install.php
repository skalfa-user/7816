<?php

$plugin = OW::getPluginManager()->getPlugin('usertags');
OW::getPluginManager()->addPluginSettingsRouteName('usertags', 'usertags.admin');

$authorization = OW::getAuthorization();
$groupName = 'usertags';
$authorization->addGroup($groupName);
$authorization->addAction($groupName, 'add_tags');

BOL_LanguageService::getInstance()->importPrefixFromZip($plugin->getRootDir() . 'langs.zip', 'usertags');

if ( !OW::getConfig()->configExists('usertags', 'tags_in_cloud') )
{
    OW::getConfig()->addConfig( 'usertags', 'tags_in_cloud', 20 );
}
