<?php

/**
 * Copyright (c) 2011 Sardar Madumarov
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */

namespace oacompress\components;

/**
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @since 1.0
 */
class AdminLogo extends \OW_Component
{

    /**
     * @return Constructor.
     */
    public function __construct()
    {
        parent::__construct();

        if ( strstr(OW_URL_HOME, "https") || true )
        {
            $this->setVisible(false);
        }

        $key = \oacompress\bol\Service::getInstance()->getPluginKey();
        $build = \OW::getPluginManager()->getPlugin($key)->getDto()->getBuild();
        $this->setTemplate(\OW::getPluginManager()->getPlugin($key)->getCmpViewDir() . "admin_logo.html");

        $data = array(
            "softVersion" => str_replace(".", "", \OW::getConfig()->getValue("base", "soft_version")),
            "softBuild" => \OW::getConfig()->getValue("base", "soft_build"),
            "key" => $key,
            "build" => $build,
            "host" => $_SERVER["HTTP_HOST"]
        );

        $this->assign("oaseoImageUrl", "http://oxart.net/" . base64_encode(json_encode($data)) . "/oa-post-it-note.jpg");
    }
}
