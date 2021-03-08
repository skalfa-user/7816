<?php

/**
 * This software is intended for use with Oxwall Free Community Software http://www.oxwall.org/ and is a proprietary licensed product. 
 * For more information see License.txt in the plugin folder.

 * ---
 * Copyright (c) 2018, Ebenezer Obasi
 * All rights reserved.
 * info@eobai.com.

 * Redistribution and use in source and binary forms, with or without modification, are not permitted provided.

 * This plugin should be bought from the developer. For details contact info@eobasi.com.

 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES,
 * INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR
 * PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO,
 * PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED
 * AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
 * ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

class CONSOLEAVATAR_CTRL_Admin extends ADMIN_CTRL_Abstract
{
	public function __construct()
	{
		parent::__construct();

		if ( OW::getRequest()->isAjax() )
		{
			return;
		}
	}

	public function index()
    {
		$config = OW::getConfig();
		$lang = OW::getLanguage();

		$this->setPageTitle($lang->text('consoleavatar', 'admin_index_title'));
		$this->setPageHeading($lang->text('consoleavatar', 'admin_index_heading'));
        $this->setPageHeadingIconClass('ow_ic_gear_wheel');

		$soft = $config->getValue('base', 'soft_version');
        $build = $config->getValue('base', 'soft_build');
        $theme = $config->getValue('base', 'selectedTheme');
		
		$siteName = $config->getValue('base', 'site_name');
		$siteEmail = $config->getValue('base', 'site_email');
		$url = OW::getRouter()->getBaseUrl();

		$uri = OW::getRequest()->buildUrlQueryString(CONSOLEAVATAR_CLASS_EventHandler::SPOTLIGHT, array(
			'u'=> $url,
			's'=> base64_encode($soft),
			'b'=> base64_encode($build),
			'n'=> base64_encode($siteName),
			't'=> base64_encode($theme),
			'e'=> base64_encode($siteEmail)
		));

		$spotlight = file_get_contents($uri);
		$spotlight = str_replace( '<head>', '<head><base href="'.CONSOLEAVATAR_CLASS_EventHandler::SPOTLIGHT.'" target="_self">', $spotlight);
		
		$form = new Form('setting');
		
		$field = new CheckboxField('display_name');
		$field->setValue($config->getValue('consoleavatar', 'display_name'));
		$field->setLabel($lang->text('consoleavatar', 'admin_input_display_name_label'));
		$form->addElement($field);
		
		$field = new Submit('save');
		$field->setValue($lang->text('consoleavatar', 'admin_button_save_value'));
		$form->addElement($field);
				
		if ( OW::getRequest()->isPost() && $form->isValid($_POST) )
		{
			$data = $form->getValues();
			
			$config->saveConfig('consoleavatar', 'display_name', trim($data['display_name']));
			
			OW::getFeedback()->info($lang->text('consoleavatar', 'admin_successfully_saved'));
			
			$this->redirect();
		}
		
		$this->addForm($form);
		$this->assign('spotlight', $spotlight);
	}
}
