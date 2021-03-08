<?php

/**
 * Copyright (c) 2013, Oxwall CandyStore
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

/**
 * IFavorites settings form
 *
 * @author Oxwall CandyStore <plugins@oxcandystore.com>
 * @package ow.ow_plugins.ocs_favorites.classes
 * @since 1.5.3
 */

class OCSFAVORITES_CLASS_SettingsForm extends Form
{
    public function __construct()
    {
        parent::__construct('settings-form');

        $lang = OW::getLanguage();

        $canView = new CheckboxField('canView');
        $canView->setLabel($lang->text('ocsfavorites', 'can_view_favorites'));
        $this->addElement($canView);

        // submit
        $submit = new Submit('save');
        $submit->setValue($lang->text('ocsfavorites', 'btn_save'));
        $this->addElement($submit);
    }
}