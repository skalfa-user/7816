<?php

class WATERMARKPRO_CTRL_Admin extends ADMIN_CTRL_Abstract
{
	private $config;
	private $w;
    public function __construct()
    {
        parent::__construct();
        $this->setPageHeading('Watermark Pro');
        $this->setPageHeadingIconClass('ow_ic_gear_wheel');
		$this->config = OW::getConfig()->getValues('watermarkpro');
		$this->w = WATERMARKPRO_CLASS_Watermark::getInstance();
    }

    private function getMenu()
    {
        $this->config = OW::getConfig()->getValues('watermarkpro');
        $menuItems = array();
        $item = new BASE_MenuItem();
        $item->setLabel($this->w->lang('config'));
        $item->setUrl(OW::getRouter()->urlForRoute('watermarkpro_admin'));
        $item->setKey('config');
		$item->setIconClass('ow_ic_gear_wheel');
        $item->setOrder(0);
        array_push($menuItems, $item);

			
		$item = new BASE_MenuItem();
		$item->setLabel($this->w->lang('config_image'));
		$item->setUrl(OW::getRouter()->urlForRoute('watermarkpro_adminimage'));
		$item->setKey('config_image');
		$item->setIconClass('ow_ic_picture');
		$item->setOrder(1);
		array_push($menuItems, $item);
		
		$item = new BASE_MenuItem();
		$item->setLabel($this->w->lang('config_text'));
		$item->setUrl(OW::getRouter()->urlForRoute('watermarkpro_admintext'));
		$item->setKey('config_text');
		$item->setIconClass('ow_ic_write');
		$item->setOrder(2);
		array_push($menuItems, $item);
		
		$item = new BASE_MenuItem();
		$item->setLabel($this->w->lang('config_preview'));
		$item->setUrl(OW::getRouter()->urlForRoute('watermarkpro_adminpreview'));
		$item->setKey('config_preview');
		$item->setIconClass('ow_ic_monitor');
		$item->setOrder(3);
		array_push($menuItems, $item);

        $item = new BASE_MenuItem();
        $item->setLabel("Help");
        $item->setUrl("http://www.codemonster.pro/oxwall_watermarkkpro_plugin_help.html");
        $item->setKey('help');
        $item->setIconClass('ow_ic_question');
		$item->setNewWindow(true);
		$item->setOrder(4);
        array_push($menuItems, $item);
        $menu = new BASE_CMP_ContentMenu($menuItems);
        return $menu;
    }
    /**
     * Default action
     */
    public function index()
    {	
        $form = new WATERMARKPRO_AdminForm();
        $form = $this->processthis($form);
        //$this->addForm($form);
        $this->assign("form",$form->getElements());
        $this->addForm($form);

        $menu = $this->getMenu();
        $menu->getElement('config')->setActive(true);
        $this->addComponent('menu', $menu);
		
	}
    public function preview()
    {	
        $menu = $this->getMenu();
        $menu->getElement('config_preview')->setActive(true);
        $this->addComponent('menu', $menu);
		
		$imamgepreview = "";
		if($this->config["isenabled_image"] || $this->config["isenabled_text"])
		{
			$imamgepreview = OW::getRouter()->urlForRoute('watermarkpro_adminpreviewimage');
		}
		
		$this->assign("preview",$imamgepreview);
		
	}
    public function previewimage()
    {
		$this->w->watermarkPhotoPreview();
		exit;
	}
	

    public function image()
    {	
        $form = new WATERMARKPRO_ImageForm();
		$form = $this->processthis($form);

        $this->assign("form",$form->getElements());
        $this->addForm($form);
		
		$menu = $this->getMenu();
        $menu->getElement('config_image')->setActive(true);
        $this->addComponent('menu', $menu);
 
		$dir = OW::getPluginManager()->getPlugin('watermarkpro')->getUserFilesUrl();
		$image = $this->config["watermark_image"];
		if ($image == "watermarkpro.png"){
			$dir = OW::getPluginManager()->getPlugin('watermarkpro')->getStaticUrl();
		}		
		$this->assign("watermark",$dir.$image);
    }
	
    public function text()
    {

		$form = new WATERMARKPRO_TextForm();
		$form = $this->processthis($form);

        $this->assign("form",$form->getElements());
        $this->addForm($form);
		
		$menu = $this->getMenu();
        $menu->getElement('config_text')->setActive(true);
        $this->addComponent('menu', $menu);
		
    }
	private function processthis($form){
        if ( OW::getRequest()->isPost()  && $form->isValid($_POST) ){
			$data = $form->getValues();
            $result = $form->process($data);
            if ( $result ){
                OW::getFeedback()->info($this->w->lang('settings_changed'));
            }else{
                OW::getFeedback()->warning($this->w->lang('settings_not_changed'));
            }
		}
		return $form;
	}
	
	
}

class WATERMARKPRO_AdminForm extends Form
{
	private $w;	
    public function __construct()
    {
        parent::__construct('form');

		$this->w = WATERMARKPRO_CLASS_Watermark::getInstance();
        
        $config = OW::getConfig()->getValues('watermarkpro');

		$isenabled = new CheckboxField("isenabled");
        $isenabled->setLabel($this->w->lang("isenabled"))
				  ->setValue($config["isenabled"]);
        $this->addElement($isenabled);

		$isenabled = new CheckboxField("isenabled_image");
        $isenabled->setLabel($this->w->lang("isenabled_image"))        
				  ->setValue($config["isenabled_image"]);
        $this->addElement($isenabled);
		
		$isenabled = new CheckboxField("isenabled_text");
        $isenabled->setLabel($this->w->lang("isenabled_text"))
				  ->setValue($config["isenabled_text"]);
        $this->addElement($isenabled);
		
		
		$t = "watermark_main";
		$wp = new CheckboxField($t);
        $wp->setLabel($this->w->lang($t))
				  ->setValue($config[$t]);
        $this->addElement($wp);
		
		$t = "watermark_preview";
		$wp = new CheckboxField($t);
        $wp->setLabel($this->w->lang($t))
				  ->setValue($config[$t]);
        $this->addElement($wp);
		
		$t = "watermark_original";
		$wp = new CheckboxField($t);
        $wp->setLabel($this->w->lang($t))
				  ->setValue($config[$t]);
        $this->addElement($wp);
		
	$t = "usercandisable";
	$wp = new CheckboxField($t);
        $wp->setLabel($this->w->lang($t))
				  ->setValue($config[$t]);
        $this->addElement($wp);
		
        $btn = new Submit('submit');
        $btn->setValue($this->w->lang('save_setting_btn_label'));

        $this->addElement($btn);
    }

    public function process( $data )
    {
        $result = false;
        //if ( !empty( $data['watermark_type'] ) )
        //{
			$this->saveConfig("isenabled", $data["isenabled"]);
			$this->saveConfig("isenabled_image", $data["isenabled_image"]);
			$this->saveConfig("isenabled_text", $data["isenabled_text"]);
			$this->saveConfig("watermark_main", $data["watermark_main"]);
			$this->saveConfig("watermark_preview", $data["watermark_preview"]);
			$this->saveConfig("watermark_original", $data["watermark_original"]);
			$this->saveConfig("usercandisable", $data["usercandisable"]);
			$result = true;
           //unset($data['watermark_type']);
        //}
		
        return $result;
    }
	private function saveConfig($var, $val){
		OW::getConfig()->saveConfig('watermarkpro', $var, $val);
	}

}

class WATERMARKPRO_ImageForm extends Form
{
	private $w;
    public function __construct()
    {
        parent::__construct('form');
		
		$this->w = WATERMARKPRO_CLASS_Watermark::getInstance();
        
		$config = OW::getConfig()->getValues('watermarkpro');
		$this->setEnctype('multipart/form-data');
		
		$file = new FileField('file');
        $file->setLabel($this->w->lang('image'));
        //$file->setRequired(true);
        $this->addElement($file);
        
		$positions = new Selectbox('position_image');
		$positions->setLabel($this->w->lang('position_image'))
				  ->setOptions($this->w->positions())
				  ->setValue($config["position_image"]);
		$this->addElement($positions);

		$positions = new Selectbox('position_image_margin');
		$positions->setLabel($this->w->lang('position_image_margin'))
				  ->setOptions($this->w->positionsMargin())
				  ->setValue($config["position_image_margin"]);
		$this->addElement($positions);

		$watermark_opacity = new Selectbox('watermark_opacity');
		$watermark_opacity->setLabel($this->w->lang('watermark_opacity'))
				  ->setOptions($this->w->opacity())
				  ->setValue($config["watermark_opacity"]);
		$this->addElement($watermark_opacity);
		
        $btn = new Submit('submit');
        $btn->setValue($this->w->lang('save_setting_btn_label'));

        $this->addElement($btn);
    }
    public function process( $data )
    {
        $result = false;        
        if ( !empty( $data['watermark_opacity'] ) )
        {
                if ( !empty($_FILES['file']['tmp_name']) )
                {
                    $extension = UTIL_File::getExtension($_FILES['file']['name']);
					$watermarkService = WATERMARKPRO_BOL_WatermarkproService::getInstance();
					
                    if ( $watermarkService->extIsAllowed($extension) )
                    {
                        $file = $watermarkService->updateWatermarkImage($_FILES['file']);
						$this->saveConfig("watermark_image", $file);
                    }
                }
			$this->saveConfig("position_image", $data["position_image"]);
			$this->saveConfig("position_image_margin", $data["position_image_margin"]);
			$this->saveConfig("watermark_opacity", $data["watermark_opacity"]);
			$result = true;
        }
        return $result;
    }
	private function saveConfig($var, $val){
		OW::getConfig()->saveConfig('watermarkpro', $var, $val);
	}
}

class WATERMARKPRO_TextForm extends Form
{
	private $w;
    public function __construct()
    {
        parent::__construct('form');
		
		$this->w = WATERMARKPRO_CLASS_Watermark::getInstance();
        
		$config = OW::getConfig()->getValues('watermarkpro');
		$this->setEnctype('multipart/form-data');
				
		$text = new TextField('watermark_text');
					$text->setLabel($this->w->lang('watermark_text'))
					->setValue($config["watermark_text"])
					->addAttribute('placeholder', 'You can use: {websiteurl} {userurl} {username} {pictureurl} ')
					->setRequired(true);
		$this->addElement($text);

		
		$bColor = new ColorField('watermark_color');
				$bColor->setLabel($this->w->lang('watermark_color'))
				->setValue($config["watermark_color"]);
		$this->addElement($bColor);

		$positions = new Selectbox('position_text');
		$positions->setLabel($this->w->lang('position_text'))
				  ->setOptions($this->w->positions())
				  ->setValue($config["position_text"]);
		$this->addElement($positions);

		$positions = new Selectbox('position_text_margin');
		$positions->setLabel($this->w->lang('position_text_margin'))
				  ->setOptions($this->w->positionsMargin())
				  ->setValue($config["position_text_margin"]);
		$this->addElement($positions);
		
		$fonts = new Selectbox('watermark_font');
				$fonts->setLabel($this->w->lang('watermark_font'))
				->setValue($config["watermark_font"]);  //->setOptions($this->fonts())
				$i=1;
				$fnames = $this->w->fontNames();
				foreach ($fnames  as $font => $name )
				{
				  $fonts->addOption($i, $name);
				  $i++;
				}	
		$this->addElement($fonts);
		
		$sizes = new Selectbox('watermark_size');
				  $sizes->setLabel($this->w->lang('watermark_size'))
				  ->setOptions($this->w->sizes())
				  ->setValue($config["watermark_size"]);
		$this->addElement($sizes);
		
		$bColor = new ColorField('stroke_color');
				$bColor->setLabel($this->w->lang('stroke_color'))
				->setValue($config["stroke_color"]);
		$this->addElement($bColor);

		$sizes = new Selectbox('stroke_size');
				  $sizes->setLabel($this->w->lang('stroke_size'))
				  ->setOptions($this->w->strokeSize())
				  ->setValue($config["stroke_size"]);
		$this->addElement($sizes);
	
		$btn = new Submit('submit');
				$btn->setValue($this->w->lang('save_setting_btn_label'));
        $this->addElement($btn);
    }
    public function process( $data )
    {
        $result = false;
        if ( !empty( $data['watermark_text'] ) )
        {
			$this->saveConfig("position_text", $data["position_text"]);
			$this->saveConfig("position_text_margin", $data["position_text_margin"]);
			
			$this->saveConfig("stroke_color", $data["stroke_color"]);
			$this->saveConfig("stroke_size", $data["stroke_size"]);
			
			$this->saveConfig("watermark_text", $data["watermark_text"]);
			$this->saveConfig("watermark_color", $data["watermark_color"]);
			$this->saveConfig("watermark_font", $data["watermark_font"]);
			$this->saveConfig("watermark_size", $data["watermark_size"]);
			$result = true;
        }
        return $result;
    }
	private function saveConfig($var, $val){
		OW::getConfig()->saveConfig('watermarkpro', $var, $val);
	}
	
}
