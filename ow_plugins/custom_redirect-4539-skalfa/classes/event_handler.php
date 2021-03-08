<?php

/**
 * Copyright (c) 2018, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.com/
 * and is licensed under Oxwall Store Commercial License.
 *
 * Full text of this license can be found at http://developers.oxwall.com/store/oscl
 */

/**
 * @author Kubatbekov Rahat <kubatbekovdev@gmail.com>
 */
class CUSREDIRECT_CLASS_EventHandler
{
    use OW_Singleton;

    /**
     * Init
     */
    public function init()
    {
        $em = OW::getEventManager();

        $em->bind(OW_EventManager::ON_BEFORE_PLUGIN_UNINSTALL, [$this, 'beforePluginUninstall']);
        $em->bind(OW_EventManager::ON_AFTER_ROUTE, [$this, 'onAfterRoute']);
    }

    /**
     * On after route
     */
    public function onAfterRoute()
    {
        if ( !OW::getUser()->isAuthenticated() )
        {
            OW::getDocument()->addScript(OW::getPluginManager()->getPlugin('cusredirect')->getStaticJsUrl() . 'main.js');

            $url =  OW::getRouter()->urlForRoute('cusredirect.user_search');

            $js = UTIL_JsGenerator::newInstance();
            $js->addScript("
                isAuthenticated('{$url}');
            ");

            OW::getDocument()->addOnloadScript($js);
        }
    }

    /**
     * Before plugin uninstall
     *
     * @param OW_Event $event
     * @throws RedirectException
     */
    public function beforePluginUninstall(OW_Event $event)
    {
        $params = $event->getParams();

        if ( $params['pluginKey'] == 'cusredirect' )
        {
            OW::getFeedback()->warning(OW::getLanguage()->text('cusredirect', 'plugin_delete_warning'));

            throw new RedirectException(OW::getRouter()->urlForRoute('admin_plugins_installed'));
        }
    }
}