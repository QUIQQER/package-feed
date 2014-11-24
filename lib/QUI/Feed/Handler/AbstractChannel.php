<?php

/**
 * This file contains \QUI\Feed\Handler\AbstractFeed
 */

namespace QUI\Feed\Handler;

use QUI\QDOM;
use QUI\Feed\Interfaces\Channel as InterfaceChannel;
use QUI\Feed\Interfaces\FeedItem;

/**
 * Class AbstractFeed
 *
 * @package quiqqer/feed
 * @author www.pcsg.de (Henning Leutz)
 */
abstract class AbstractChannel extends QDOM implements InterfaceChannel
{
    /**
     * Main host
     * @var string
     */
    protected $_host = '';

    /**
     * RSS Channel items
     * @var Array
     */
    protected $_items = array();

    /**
     * constructor
     * @param Array $params
     */
    public function __construct($params=array())
    {
        // defaults
        $this->setAttributes(array(
            'title'         => '',
            'description'   => '',
            'timestamp'     => '',
            'language'      => ''
        ));
    }

    /**
     * Add an item to the channel
     *
     * @param FeedItem $item
     */
    public function addItem(FeedItem $item)
    {
        $this->_items[] = $item;
    }

    /**
     * Return the feed items
     * @return Array
     */
    public function getItems()
    {
        return $this->_items;
    }

    /**
     * Set the title of the channel
     * @param String $title
     */
    public function setTitle($title)
    {
        $this->setAttribute( 'title', $title );
    }

    /**
     * Set the description of the channel
     * @param String $description
     */
    public function setDescription($description)
    {
        $this->setAttribute( 'description', $description );
    }

    /**
     * Set the unix timestamp
     * @param Integer $timestamp - Unix timestamp
     */
    public function setDate($timestamp)
    {
        $this->setAttribute( 'timestamp', $timestamp );
    }

    /**
     * Set the language of the channel
     * @param String $language
     */
    public function setLanguage($language)
    {
        $this->setAttribute( 'language', $language );
    }

    /**
     * Set the main host
     * @param String $host - eq http://www.myhost.com
     */
    public function setHost($host)
    {
        $this->_host = $host;
    }

    /**
     * Return the main host
     * @return String
     */
    public function getHost()
    {
        return $this->_host;
    }
}
