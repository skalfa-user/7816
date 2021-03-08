<?php

/**
 * Copyright (c) 2011 Sardar Madumarov
 * All rights reserved.

 * ATTENTION: This commercial software is intended for use with Oxwall Free Community Software http://www.oxwall.org/
 * and is licensed under Oxwall Store Commercial License.
 * Full text of this license can be found at http://www.oxwall.org/store/oscl
 */
/**
 * @author Sardar Madumarov <madumarov@gmail.com>
 * @package oaseo
 * @since 1.0
 */

$dbo = Updater::getDbo();
$sqlErrors = array();

$queries = array( 
    "DELETE FROM `".\OW_DB_PREFIX."\OACOMPRESS_item`"
);

foreach ( $queries as $query )
{
    try
    {
        $dbo->query($query);
    }
    catch( Exception $e )
    {
        $sqlErrors[] = $e;
    }
}
