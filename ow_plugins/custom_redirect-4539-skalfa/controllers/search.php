<?php

/**
 * Copyright (c) 2018, Skalfa LLC
 * All rights reserved.
 *
 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.com/
 * and is licensed under Oxwall Store Commercial License.
 *
 * Full text of this license can be found at http://developers.oxwall.com/store/oscl
 */

/**
 * @author Kubatbekov Rahat <kubatbekovdev@gmail.com>
 */
class CUSREDIRECT_CTRL_Search extends OW_ActionController
{
    public function index()
    {
        if ( OW::getRequest()->isAjax() )
        {
            $response = '';

            if ( !OW::getUser()->isAuthenticated() )
            {
                $response = OW::getRouter()->urlForRoute('base_join');

                exit(json_encode($response));
            }

            exit(json_encode($response));
        }

        exit();
    }
}
