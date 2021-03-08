<?php

class HIDEADMIN_CTRL_Controller extends OW_ActionController
{
    public function newRedirect()
    {         
        $language = OW::getLanguage();
        
        $this->assign('access_denied_text', $language->text( "hideadmin", "access_denied_text" ));
    }
}

