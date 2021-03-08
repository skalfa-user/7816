<?php
class WATERMARKPRO_CLASS_Watermark
{
    private static $classInstance;
    private $fontsDir;
	private $l;
	private $thepositions;
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
	
		require_once('image.php');
		$this->fontsDir = OW::getPluginManager()->getPlugin('watermarkpro')->getPluginFilesDir().'fonts/';
		$this->l = OW::getLanguage();		
		$this->config = OW::getConfig()->getValues('watermarkpro');
		
		$p[1] = 'top left';
		$p[2] = 'top';
		$p[3] = 'top right';
		$p[4] = 'left';
		$p[5] = 'center';
		$p[6] = 'right';
		$p[7] = 'bottom left';
		$p[8] = 'bottom';
		$p[9] = 'bottom right';
		$this->thepositions = $p;
    }
	public function startOverlay($image, $opacity, $position, $margin, $save = null){
		
			//echo $image; exit;
			try {
				$img = new WatermarkProImage();
				$sample = OW::getPluginManager()->getPlugin('watermarkpro')->getStaticDir().'sample.jpg';
				
				if ($save)
				{
					$output = OW::getPluginManager()->getPlugin('watermarkpro')->getUserFilesDir();
					$img->load($sample)->overlay($image, $position, $opacity, $margin)->save($output.'sample.jpg');
					
				}else{
					$img->load($sample)->overlay($image, $position, $opacity, $margin)->output();
					
				}/*
				print $image; 
				print '<br>'; 
				print $sample; 
				exit;*/
			} catch (Exception $e) {
				
				echo '<span style="color: red;">'.$e->getMessage().'</span>';
			}
	}
	public function startOverlayText($text, $color, $position, $margin, $font, $size,  $strokecolor, $strokesize, $imagepreview){
			
			try {
				$img = new WatermarkProImage();
				$staticDir = OW::getPluginManager()->getPlugin('watermarkpro')->getStaticDir();
				$output = OW::getPluginManager()->getPlugin('watermarkpro')->getUserFilesDir();
				if ($imagepreview == true){
					$sample = $output.'sample.jpg';
				}else{
					$sample = $staticDir.'sample.jpg';
				}

				//echo $sample; exit;
				$img->load($sample)->text($text, $staticDir.'fonts/'.$font, $size, $color, $position,$margin,0, $strokecolor, $strokesize)->output();
				
			} catch (Exception $e) {
				
				echo '<span style="color: red;">'.$e->getMessage().'</span>';
			}
	}
	
	public function fontNames()
	{
		$result = array(
		  'OpenSans-Regular.ttf' => 'OpenSans',
		  'delicious.ttf' => 'Delicious',
		  'FFF_Tusj.ttf' => 'FFTusj',
		  'Roboto-Regular.ttf' => 'Roboto',
		  'Windsong.ttf' => 'Windsong',
		  'Chunkfive.otf' => 'ChunkFive',
		  'SEASRN__.ttf' => 'SEAS',
		  'Pacifico.ttf' => 'Pacifico',
		  'delicious.ttf' => 'Delicious',
		  'LeagueGothic-Regular.otf' => 'League Gothic',
		  'KaushanScript-Regular.otf' => 'Kaushan Script',
		  'GreatVibes-Regular.otf' => 'Great Vibes',
		  'Exo-Regular.otf' => 'Exo'
		);
		
		return $result;
	}
	private function getPosition($position)
	{
		//$p[10] = 'random'; //we never return this
		/*
		if ($position==10){
			do {   
				$position = rand(1,9);
			} while($position == $exclude);
		}
		*/	
		return $this->thepositions[$position];
	}
	
	public function watermarkPhotoPreview()
    {
		
		$dir = OW::getPluginManager()->getPlugin('watermarkpro')->getUserFilesDir();
		$image = $this->config["watermark_image"];
		if ($image == "watermarkpro.png"){
			$dir = OW::getPluginManager()->getPlugin('watermarkpro')->getStaticDir();
		}
		
		$imamgepreview = "";
		$myposition=0;
		if($this->config["isenabled_image"])
		{
			$myposition = $this->getPosition($this->config["position_image"]);
			$imagepreview = $this->startOverlay(
							$dir.$image, 
							($this->config["watermark_opacity"] / 100), 
							$myposition,
							$this->config["position_image_margin"],
							$this->config["isenabled_text"]);
		}
		if($this->config["isenabled_text"])
		{
			$fnames = $this->fontNames(); $i=1; $f = "";
			foreach ($fnames  as $font => $name )
			{
			  if ($i == $this->config["watermark_font"]){ $f = $font; } $i++;
			}
			$text = $this->getUserText($this->config["watermark_text"], OW::getUser()->getId());
			
			$imagepreview = $this->startOverlayText(
													$text,
													$this->config["watermark_color"],
													$this->getPosition($this->config["position_text"]),
													$this->config["position_text_margin"],
													$f,
													$this->config["watermark_size"],
													$this->config["stroke_color"],
													$this->config["stroke_size"],
													$this->config["isenabled_image"]
										);
		}
		exit;
		//$this->assign("preview",$imamgepreview);		
	}
	
	public function getPhotoPaths($data){
		if ( ($photo = PHOTO_BOL_PhotoDao::getInstance()->findById($data['photoId'])) === NULL )
        {
           // exit();
        }
		
		$path = array();
		if($this->config["watermark_original"]){
			$path[] = PHOTO_BOL_PhotoService::getInstance()->getPhotoPath($photo->id, $photo->hash, 'original');
		}
		if($this->config["watermark_main"]){
			$path[] = PHOTO_BOL_PhotoService::getInstance()->getPhotoPath($photo->id, $photo->hash, 'main');
		}
		if($this->config["watermark_preview"]){
			$path[] = PHOTO_BOL_PhotoService::getInstance()->getPhotoPath($photo->id, $photo->hash, 'preview');
		}
		//$path[] = PHOTO_BOL_PhotoService::getInstance()->getPhotoPath($photo->id, $photo->hash, 'fullscreen');
		//$path[] = PHOTO_BOL_PhotoService::getInstance()->getPhotoPath($photo->id, $photo->hash, 'small');
		return $path;
	}
	private function p($t){
		//print $t;
	}

	public function watermarkPhotoFinal( OW_Event $event )
    {
	
		//$this->p("basta 1");
		$addw = false;
		if ($this->config["isenabled"]){
			//$this->p("basta 2");
			$addw = true;
			if ($this->config["usercandisable"] && isset($_POST['addwatermark'])){
				//$this->p("basta 3");
				if ($_POST['addwatermark'] == true){
					//$this->p("basta 4 - {$_POST['addwatermark']} - ");
					$addw = true;
				}else{
					//$this->p("basta 5");
					$addw = false;
				}
				
				//exit();
			}
		}
	
		//$addw=true;
		if ($addw == true){
			$staticDir = OW::getPluginManager()->getPlugin('watermarkpro')->getStaticDir();
			$dir = OW::getPluginManager()->getPlugin('watermarkpro')->getUserFilesDir();
			$image = $this->config["watermark_image"];
			if ($image == "watermarkpro.png"){
				$dir = OW::getPluginManager()->getPlugin('watermarkpro')->getStaticDir();
			}
			$watermarkImage = $dir.$image;
			
			
			$img = new WatermarkProImage();
			$fnames = $this->fontNames(); $i=1; $f = "";
			foreach ($fnames  as $font => $name )
			{
			  if ($i == $this->config["watermark_font"]){ $fuente  = $staticDir.'fonts/'.$font; } $i++;
			}
			$watermarkopacity = ($this->config["watermark_opacity"] / 100);
			
			foreach ( $event->getParams() as $data )
			{
				$images = $this->getPhotoPaths($data);
				
				//print "paths";
				//print_r($images);
				//exit();
				$imageposition = $this->getPosition($this->config["position_image"]);
				$textposition = $this->getPosition($this->config["position_text"]);
				$text = $this->getImageText($this->config["watermark_text"], $data['photoId']);
			
			
				foreach($images as $i=>$path)
				{	
					//////////////////////WATERMARK IMAGE
					if($this->config["isenabled_image"])
					{
						try {
							$img->load($path)->overlay(
												$watermarkImage, 
												$imageposition, 
												$watermarkopacity,
												$this->config["position_image_margin"])->save();
						} catch (Exception $e) {
							//echo '<span style="color: red;">'.$e->getMessage().'</span>';
							//exit();
						}
					}
					///////////////////////WATERMARK TEXT
					
					if($this->config["isenabled_text"])
					{
						try {				
							$img->load($path)->text(
												$text, 
												$fuente,
												$this->config["watermark_size"], 
												$this->config["watermark_color"], 
												$textposition,
												$this->config["position_text_margin"],
												0, 
												$this->config["stroke_color"],
												$this->config["stroke_size"]
												)->save();
						} catch (Exception $e) {				
							//echo '<span style="color: red;">'.$e->getMessage().'</span>';
							//exit();
						}					
					}
				}
			}
		}
	}
	
	private function getImageText($text, $photoId)
	{
		$photoService = PHOTO_BOL_PhotoService::getInstance();
		$userId = $photoService->findPhotoOwner($photoId);
		
		return $this->getUserText($text, $userId);
	}
	private function getUserText($text, $userId)
	{
		$displayName = BOL_UserService::getInstance()->getDisplayName($userId); //fix for adding displayName too
		$userUrl = BOL_UserService::getInstance()->getUserUrl($userId);
		//$userName =  OW::getUser()->getUserObject()->getUsername($userId);
		$userName =  BOL_UserService::getInstance()->getUsername($userId); //FIX
		
		//app fix
		$invalidUrl = OW_URL_HOME . 'api/INVALID_URI';		
		if ($userUrl == $invalidUrl)
		{			
			$userUrl = OW_URL_HOME . 'user/' . $userName;
		}
		
		
		$vars = array(
			'displayName' => $displayName,
			'userUrl' => $userUrl,
			'userName' => $userName,
		);
		$text = UTIL_String::replaceVars($text, $vars);
		
		return $text;
	}
	
	public function positions()
	{
		$result[1] = $this->lang('position_topleft');
		$result[2] = $this->lang('position_topcenter');
		$result[3] = $this->lang('position_topright');
		$result[4] = $this->lang('position_middleleft');
		$result[5] = $this->lang('position_middlecenter');
		$result[6] = $this->lang('position_middleright');
		$result[7] = $this->lang('position_bottomleft');
		$result[8] = $this->lang('position_bottomcenter');
		$result[9] = $this->lang('position_bottomright');
		//$result[10] = $this->lang('position_random');
		
		return $result;
	}
	public function positionsMargin()
	{
		for ($i=1; $i<=50; $i++){
			$result[$i] = "$i";
		}
		return $result;
	}
	public function opacity(){
		$id=10;
		while ($id<=100){
			$result[$id] = $id;
			$id+=5;
		}
		return $result;
	}
	
	public function strokeSize()
	{
		for ($i=0; $i<=10; $i++){
			$result[$i] = "$i";
		}
		return $result;
	}
	
	public function sizes()
	{
		$result[6] = '6px';
		$result[8] = '8px';
		$result[10] = '10px';
		$result[12] = '12px';
		$result[14] = '14px';
		$result[16] = '16px';
		$result[18] = '18px';
		$result[20] = '20px';
		$result[24] = '24px';
		$result[28] = '28px';
		$result[32] = '32px';
		$result[48] = '48px';
		$result[56] = '56px';
		$result[72] = '72px';		
		return $result;
	}
	public function lang($key, $vars = null)
    {
        return $this->l->text("watermarkpro", $key, $vars);
    }
}		
