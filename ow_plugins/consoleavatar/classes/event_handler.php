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

class CONSOLEAVATAR_CLASS_EventHandler
{
	const SPOTLIGHT = 'http://spotlight.ewtnet.us/';

    /**
     * @var CONSOLEAVATAR_CLASS_EventHandler
     */
    private static $classInstance;

    /**
     * @return CONSOLEAVATAR_CLASS_EventHandler
     */
    public static function getInstance()
    {
        if ( self::$classInstance === null )
        {
            self::$classInstance = new self();
        }

        return self::$classInstance;
    }

    private function __construct()
    {
		$this->userService = BOL_UserService::getInstance();
		$this->avatar = BOL_AvatarService::getInstance();
    }

	public function consoleAvatar( )
	{
		$attrs = OW::getRequestHandler()->getHandlerAttributes();

		if( !OW::getUser()->isAuthenticated( ) )
		{
			return;
		}

		$userId = OW::getUser()->getId();
		$configShowNme = OW::getConfig()->getValue('consoleavatar', 'display_name');

		$img = $this->avatar->userHasAvatar( $userId ) ? $this->avatar->getAvatarUrl( $userId ) : $this->avatar->getDefaultAvatarUrl();;

		$displayName = $this->userService->getDisplayName( $userId );
		$html = ' <img alt="'.$displayName.'" src="'.$img.'" /> ';

		$script = " $('.ow_console_item.ow_console_dropdown_hover:eq(0) a.ow_console_item_link').addClass('showName'); ";
		$script .= $configShowNme ? "$('.showName').prepend(' $html ')" : "$('.showName').html('$html')";

		OW::getDocument()->addOnloadScript($script);

		OW::getDocument()->addStyleDeclaration('
			.ow_console_item a.ow_console_item_link.showName img {
				-webkit-border-radius: 20px;
				-moz-border-radius: 20px;
				border-radius: 20px;
				width: 20px;
				vertical-align: top;
			}
			.ow_console_item a.ow_console_item_link.showName{
				background: none;
			}
		');
	}

    public function init()
    {
        OW::getEventManager()->bind(OW_EventManager::ON_BEFORE_DOCUMENT_RENDER, array($this, "consoleAvatar"));
    }
}