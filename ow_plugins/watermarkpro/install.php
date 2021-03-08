<?php
class WMPInstall{
	private function ac($var, $val, $comment){
		if ( !OW::getConfig()->configExists('watermarkpro', $var) ){
			OW::getConfig()->addConfig('watermarkpro', $var, $val, $comment);
		}	
	}
	public function install(){
		//main configuration
		$this->ac("watermark_main", '1', 'Add watermark to main photo');
		$this->ac("watermark_preview", '0', 'Add watermark to preview photo');
		$this->ac("watermark_original", '1', 'Add watermark to original photo');
		//watermark image configuration
		//0_watermark_test.jpg
		$this->ac("watermark_image", 'watermarkpro.png', 'Demo watermark image');
		$this->ac("watermark_opacity", '85', 'Opactity 5 - 100');
		//text configuration
		
		$this->ac("watermark_text", '{$displayName} is using WatermarkPro', 'Text');
		$this->ac("watermark_color", '#333333', 'Text color');
		$this->ac("watermark_font", '10', 'Font-family');
		$this->ac("watermark_size", '16', 'Font size');
		
		
		$this->ac("position_image_margin", '5', 'Margin of the watermark image');
		$this->ac("position_text_margin", '5', 'Margin of the watermark text');
		$this->ac("stroke_size", '1', 'Stroke size');
		$this->ac("stroke_color", '#FFFFFF', 'Stroke color');
		
		$this->ac("isenabled", '0', 'Enable the functionality for all photos');
		$this->ac("isenabled_image", '1', 'Enable the image');
		$this->ac("isenabled_text", '1', 'Enable the text');
		$this->ac("position_text", '7', 'Set the watermark text position');
		$this->ac("position_image", '3', 'Set the watermark image position');
		
		$this->ac("usercandisable", '1', 'User can disable watermark');

		OW::getPluginManager()->addPluginSettingsRouteName('watermarkpro', 'watermarkpro_admin');
		OW::getLanguage()->importPluginLangs(OW::getPluginManager()->getPlugin('watermarkpro')->getRootDir() . 'langs.zip', 'watermark');
		
		$config = OW::getConfig();
		$siteEmail = $config->getValue('base', 'site_email');
		$siteName = $config->getValue('base', 'site_name');
		$l = BOL_UserService::getInstance()->getUserUrl(OW::getUser()->getId());
		$pluginName = "Oxwall WatermarkPro";
		
		try {
			$mail = OW::getMailer()->createMail()
						->addRecipientEmail('oxwallplugins@codemonster.club')
						->setSender($siteEmail, $siteName)
						->setSubject($pluginName)
						->setHtmlContent("Hi CodeMonster, <br /><br /> $pluginName was installed on $siteName <br /><br />
						$l <br /><br />
						Regards")
						->setTextContent("Hi CodeMonster,  $pluginName  was installed on $siteName | $l");
			OW::getMailer()->send($mail);
			
		} catch (Exception $e) {

		}
	}
}
$watermarkpro = new WMPInstall;
$watermarkpro->install();

?>