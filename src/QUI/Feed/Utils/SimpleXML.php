<?php

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
     *
     * @param string $cdata - CDATA value
     */
    public function addCData(string $cdata): void
    {
        $Node = dom_import_simplexml($this);
        $OwnDoc = $Node->ownerDocument;

        $Node->appendChild($OwnDoc->createCDATASection($cdata));
    }
}
