<?php

/**
 * This file contains \QUI\Feed\Utils\SimpleXML
 */

namespace QUI\Feed\Utils;

/**
 * SimpleXMLElement extentent
 *
 * @auth
 */
class SimpleXML extends \SimpleXMLElement
{
    /**
     * Add an ![CDATA[ ]]> Entry
     * @param String $cdata - CDATA value
     */
    public function addCData($cdata)
    {
        $Node   = dom_import_simplexml( $this );
        $OwnDoc = $Node->ownerDocument;

        $Node->appendChild( $OwnDoc->createCDATASection( $cdata ) );
    }
}