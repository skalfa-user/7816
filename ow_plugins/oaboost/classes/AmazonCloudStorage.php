<?php

/**
 * EXHIBIT A. Common Public Attribution License Version 1.0
 * The contents of this file are subject to the Common Public Attribution License Version 1.0 (the “License”);
 * you may not use this file except in compliance with the License. You may obtain a copy of the License at
 * http://www.oxwall.org/license. The License is based on the Mozilla Public License Version 1.1
 * but Sections 14 and 15 have been added to cover use of software over a computer network and provide for
 * limited attribution for the Original Developer. In addition, Exhibit A has been modified to be consistent
 * with Exhibit B. Software distributed under the License is distributed on an “AS IS” basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for the specific language
 * governing rights and limitations under the License. The Original Code is Oxwall software.
 * The Initial Developer of the Original Code is Oxwall Foundation (http://www.oxwall.org/foundation).
 * All portions of the code written by Oxwall Foundation are Copyright (c) 2011. All Rights Reserved.

 * EXHIBIT B. Attribution Information
 * Attribution Copyright Notice: Copyright 2011 Oxwall Foundation. All rights reserved.
 * Attribution Phrase (not exceeding 10 words): Powered by Oxwall community software
 * Attribution URL: http://www.oxwall.org/
 * Graphic Image as provided in the Covered Code.
 * Display of Attribution Information is required in Larger Works which are defined in the CPAL as a work
 * which combines Covered Code or portions thereof with code not governed by the terms of the CPAL.
 */

namespace oacompress\classes;

class AmazonCloudStorage extends \BASE_CLASS_AmazonCloudStorage
{
    /**
     * @var \S3
     */
    private $amazon;
    private $bn;

    public function __construct( \S3 $amazon, $bucketName )
    {
        parent::__construct();
        $this->amazon = $amazon;
        $this->bn = $bucketName;
    }

    public function copyFile( $sourcePath, $destPath, $requestHeaders = array() )
    {
        $destPath = $this->getCloudFilePath($destPath);
        
        $obj = $this->amazon->putObjectFile($sourcePath, $this->bn, $destPath, \S3::ACL_PUBLIC_READ,
            array(), $requestHeaders);

        if ( $obj === null )
        {
            return false;
        }

        $object = $this->amazon->getObjectInfo($this->bn, $destPath);
        $this->triggerFileUploadEvent($destPath, $object["size"]);

        return true;
    }

    private function triggerFileUploadEvent( $path, $size )
    {
        if ( empty($path) )
        {
            return;
        }

        $params = array(
            "path" => $path,
            "size" => (int) $size
        );

        $event = new \OW_Event(\BASE_CLASS_AmazonCloudStorage::EVENT_ON_FILE_UPLOAD, $params);
        \OW::getEventManager()->trigger($event);
    }

    private function getCloudFilePath( $path )
    {
        $cloudPath = null;

        $prefixLength = strlen(OW_DIR_ROOT);
        $filePathLength = strlen($path);

        if ( $prefixLength <= $filePathLength && substr($path, 0, $prefixLength) === OW_DIR_ROOT )
        {
            $cloudPath = str_replace(OW_DIR_ROOT, "", $path);
            $cloudPath = str_replace(DS, "/", $cloudPath);
            $cloudPath = $this->removeSlash($cloudPath);
        }
        else
        {
            throw new Exception("Cant find directory `" . $path . "`!");
        }

        return $cloudPath;
    }
    
    private function removeSlash( $path )
    {
        $path = trim($path);

        if ( substr($path, 0, 1) === \BASE_CLASS_AmazonCloudStorage::CLOUD_FILES_DS )
        {
            $path = substr($path, 1);
        }

        if ( substr($path, -1) === \BASE_CLASS_AmazonCloudStorage::CLOUD_FILES_DS )
        {
            $path = substr($path, 0, -1);
        }

        return $path;
    }
}
