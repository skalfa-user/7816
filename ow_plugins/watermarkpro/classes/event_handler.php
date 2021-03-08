<?php
class WATERMARKPRO_CLASS_EventHandler
{
    private static $classInstance;
	private $w;
	public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }
        return self::$classInstance;
    }
    public function __construct()
    {
		$this->w = WATERMARKPRO_CLASS_Watermark::getInstance();
    }
	public function addWatermarkPreferenceMenuItem( BASE_CLASS_EventCollector $event )
    {
        $router = OW_Router::getInstance();
        $language = OW::getLanguage();

        $menuItem = new BASE_MenuItem();
        $menuItem->setKey('watermarkpro');
        $menuItem->setLabel($language->text('watermarkpro', 'watermarkpro_index'));
        $menuItem->setIconClass('ow_ic_lock');
        $menuItem->setUrl($router->urlForRoute('watermarkpro_index'));
        $menuItem->setOrder(6);

        $event->add($menuItem);
    }

	
    public function init()
    {
		$this->genericInit();    
		$em = OW::getEventManager();
		//$em->bind('plugin.photos.add_photo', array($this, 'onPhotoAdd'));
		
		$em->bind(PHOTO_CLASS_EventHandler::EVENT_ON_PHOTO_ADD, array($this, 'onPhotoAdd'));
		
		$config = OW::getConfig()->getValues('watermarkpro');
		if($config["isenabled"] && $config["usercandisable"]){
			$em->bind(PHOTO_CLASS_EventHandler::EVENT_ON_FORM_READY, array($this, 'onPhotoFormReady'));
		}

    }
	public function onPhotoFormReady( OW_Event $event )
    {
		$isenabled = new CheckboxField("addwatermark");
        $isenabled->setLabel(OW::getLanguage()->text('watermarkpro', 'add_watermark'))
				  ->setValue(true);
		
		$params = $event->getParams();
		$params['form']->addElement($isenabled);
		
	}

	public function onPhotoAdd( OW_Event $event )
    {
		$this->w->watermarkPhotoFinal($event);		
    }
	
    public function genericInit()
    {
		$router = OW::getRouter();
		$router->addRoute(new OW_Route('watermarkpro_admin', 'admin/watermarkpro', 'WATERMARKPRO_CTRL_Admin', 'index'));
		$router->addRoute(new OW_Route('watermarkpro_adminimage', 'admin/watermarkpro/image', 'WATERMARKPRO_CTRL_Admin', 'image'));
		$router->addRoute(new OW_Route('watermarkpro_admintext', 'admin/watermarkpro/text', 'WATERMARKPRO_CTRL_Admin', 'text'));
		$router->addRoute(new OW_Route('watermarkpro_adminpreview', 'admin/watermarkpro/preview', 'WATERMARKPRO_CTRL_Admin', 'preview'));
		$router->addRoute(new OW_Route('watermarkpro_adminpreviewimage', 'admin/watermarkpro/preview/image', 'WATERMARKPRO_CTRL_Admin', 'previewimage'));
    }
	
}
