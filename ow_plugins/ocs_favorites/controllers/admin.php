<?php

/**
 * Copyright (c) 2013, Oxwall CandyStore
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

/**
 * Favorites plugin administration action controller
 *
 * @author Oxwall CandyStore <plugins@oxcandystore.com>
 * @package ow.ow_plugins.ocs_favorites.controllers
 * @since 1.5.3
 */
class OCSFAVORITES_CTRL_Admin extends ADMIN_CTRL_Abstract
{
    /**
     * Default action
     */
    public function index()
    {
    	$lang = OW::getLanguage();
        
        $form = new OCSFAVORITES_CLASS_SettingsForm();
        $this->addForm($form);
        
        if ( OW::getRequest()->isPost() && $form->isValid($_POST) )
        {
        	$values = $form->getValues();
            $canView = (int) $values['canView'];

        	OW::getConfig()->saveConfig('ocsfavorites', 'can_view', $canView);

            $authorization = OW::getAuthorization();
            $groupName = 'ocsfavorites';
            if ( $canView )
            {
                $authorization->addAction($groupName, 'view_users', false);
            }
            else
            {
                $authorization->deleteAction($groupName, 'view_users');
            }

        	OW::getFeedback()->info($lang->text('ocsfavorites', 'settings_updated'));
        	$this->redirect();
        }
        
        $form->getElement('canView')->setValue(OW::getConfig()->getValue('ocsfavorites', 'can_view'));
        
        $logo = OW::getPluginManager()->getPlugin('ocsfavorites')->getStaticUrl() . 'img/oxwallcandystore-logo.jpg';
        $this->assign('logo', $logo);
        
        $this->setPageHeading($lang->text('ocsfavorites', 'page_heading_admin'));
    }
}