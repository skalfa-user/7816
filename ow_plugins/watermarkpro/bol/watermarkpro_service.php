<?php
final class WATERMARKPRO_BOL_WatermarkproService
{
    
    private static $classInstance;
    
    private static $ext = array('jpg', 'jpeg', 'png', 'gif', 'bmp');
	
	private $staticUrl;
	
	private $langService, $currentLang;
		
		
    private function __construct()
    {
        $staticUrl = parse_url(OW::getPluginManager()->getPlugin('watermarkpro')->getStaticUrl().'/watermark/');
		$this->staticUrl = $staticUrl["path"];
		$this->langService = BOL_LanguageService::getInstance();
        $this->currentLang = $this->langService->getCurrent();
    }
    
    public static function getInstance()
    {
        if ( !isset(self::$classInstance) )
            self::$classInstance = new self();

        return self::$classInstance;
    }
	
	//update watermark image from admin panel
    public function updateWatermarkImage( $file )
    {
		$time = time();
        $ext = UTIL_File::getExtension($file['name']);
        
        $pluginFilesPath = $this->getPluginFilesPath('watermarkpro', $time, $ext);
		
        if ( move_uploaded_file($file['tmp_name'], $pluginFilesPath) )
        {
			$file = $this->getFileName('watermarkpro', $time, $ext);
            return $file;
        }
        return false;
    }
	

    public function getPluginFilesPath( $tplId, $hash, $ext )
    {
        $dir = OW::getPluginManager()->getPlugin('watermarkpro')->getUserFilesDir();
        return $dir . $this->getFileName($tplId, $hash, $ext);
    }
	public function getFileName( $tplId, $hash, $ext )
    {
        return $tplId . '_' . $hash . '.' . $ext;
    }
 
    public function extIsAllowed( $ext )
    {
        if ( !mb_strlen($ext) )
        {
            return false;
        }

        return in_array($ext, self::$ext);
    }
}
?>