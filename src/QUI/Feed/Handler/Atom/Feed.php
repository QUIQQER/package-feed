<?php

/**
 * This file contains \QUI\Feed\Handler\Atom\Feed
 */

namespace QUI\Feed\Handler\Atom;

use QUI\Feed\Handler\AbstractFeedType;
use \QUI\Feed\Utils\SimpleXML;

/**
 * Class Feed
 *
 * @package quiqqer/feed
 * @author  www.pcsg.de (Henning Leutz)
 */
class Feed extends AbstractFeedType
{
    /**
     * Creat a channel
     *
     * @return Channel
     */
    public function createChannel()
    {
        $Channel = new Channel();

        $this->addChannel($Channel);

        return $Channel;
    }

    /**
     * Return the XML of the feed
     *
     * @return SimpleXML
     */
    public function getXML()
    {
        $XML = new SimpleXML(
            '<?xml version="1.0" encoding="UTF-8" ?>
             <feed xmlns="http://www.w3.org/2005/Atom"  />'
        );

        $channels = $this->getChannels();
        $Channel  = $channels[0];


        $host = $Channel->getHost();
        $date = date(
            \DateTime::RFC3339,
            (int)$Channel->getAttribute('timestamp')
        );

        $Atomlink = $XML->addChild(
            'link',
            '',
            'http://www.w3.org/2005/Atom'
        );
        $Atomlink->addAttribute('href', $Channel->getAttribute('link'));
        $Atomlink->addAttribute('rel', "self");

        $XML->addChild('id', $Channel->getAttribute('link'));

        $FeedUrlLink = $XML->addChild('link', "");
        $FeedUrlLink->addAttribute("href", $Channel->getAttribute('link'));

        $XML->addChild('updated', $date);


        $XML->addChild(
            'generator',
            'quiqqer.com (https://www.quiqqer.com)'
        );


        $XML->addChild('title')
            ->addCData($Channel->getAttribute('title'));

        if ($Channel->getAttribute('subtitle')) {
            $XML->addChild('subtitle')
                ->addCData($Channel->getAttribute('subtitle'));
        }


//        if ($Channel->getAttribute('author')) {
//            $AuthorNode = $XML->addChild(
//                'author',
//                $Channel->getAttribute('author')
//            );
//            
//            $AuthorNode->addChild("name","Test"); // TODO ################################################
//        }

        // channel feed items
        $items = $Channel->getItems();

        foreach ($items as $Item) {
            /* @var $Item Item */
            $date = date(
                \DateTime::RFC3339,
                (int)$Item->getAttribute('date')
            );

            $ItemXml = $XML->addChild('entry');

            $UrlLink = $ItemXml->addChild('link', "");
            $UrlLink->addAttribute("href", $Item->getAttribute('link'));

            $ItemXml->addChild('published', $date);
            $ItemXml->addChild('updated', $date); // TODO ################################################

            $ItemXml->addChild('id', $Item->getAttribute('permalink'));

            $ItemXml->addChild('title')
                ->addCData($Item->getAttribute('title'));

            if ($Item->getAttribute('description')) {
                $ItemXml->addChild('summary')
                    ->addCData($Item->getAttribute('description'));
            }


            $AuthorNode = $ItemXml->addChild(
                'author',
                ''
            );

            $AuthorNode->addChild("name", $Item->getAttribute("author"));

            /* @var $Image \QUI\Projects\Media\Image */
            $Image = $Item->getImage();

            if (!$Image) {
                continue;
            }

            if (!$Image->isActive()) {
                continue;
            }

            $maxSize = \QUI::getPackage("quiqqer/feed")->getConfig()->get("images", "maxsize");
            $Image->setAttribute("maxheight", $maxSize);
            $Image->setAttribute("maxwidth", $maxSize);


            $Enclosure = $ItemXml->addChild("link");
            $Enclosure->addAttribute("rel", "enclosure");
            $Enclosure->addAttribute("type", $Image->getAttribute('mime_type'));
            $Enclosure->addAttribute("length", $Image->getAttribute('filesize'));
            $Enclosure->addAttribute("title", $Image->getAttribute("title"));
            $Enclosure->addAttribute("href", $host . trim($Image->getUrl(false), '/'));
        }


        return $XML;
    }
}
