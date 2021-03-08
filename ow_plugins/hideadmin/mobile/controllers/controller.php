<?php

class HIDEADMIN_MCTRL_Controller extends OW_MobileActionController
{
    public function newRedirect()
    {         
        $language = OW::getLanguage();
        
        $this->assign('access_denied_text', $language->text( "hideadmin", "access_denied_text" ));
    }
    
}

