<?php

$placeWidget = BOL_ComponentAdminService::getInstance()->addWidgetToPlace(
        BOL_ComponentAdminService::getInstance()->addWidget('USERTAGS_CMP_MyTagsWidget', false),
        BOL_ComponentAdminService::PLACE_PROFILE
);

BOL_ComponentAdminService::getInstance()->addWidgetToPosition($placeWidget, BOL_ComponentAdminService::SECTION_BOTTOM);
