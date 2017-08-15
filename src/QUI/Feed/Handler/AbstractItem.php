<?php

/**
 * this file contains \QUI\Feed\Handler\AbstractItem
 */

namespace QUI\Feed\Handler;

use QUI;
use QUI\QDOM;
use QUI\Feed\Interfaces\FeedItem as InterfaceItem;

/**
 * Class AbstractItem
 *
 * @package quiqqer/feed
 * @author  www.pcsg.de (Henning Leutz)
 */
abstract class AbstractItem extends QDOM implements InterfaceItem
{
    /**
     * Image for the feed item
     *
     * @var null
     */
    protected $Image = null;

    /**
     * constructor
     *
     * @param array $params
     */
    public function __construct(array $params = array())
    {
        $this->setAttributes($params);
    }

    /**
     * Set the title of the feed item
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->setAttribute('title', $title);
    }

    /**
     * Set the description of the feed item
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->setAttribute('description', $description);
    }

    /**
     * Set the unix timestamp
     *
     * @param integer $timestamp - Unix timestamp
     */
    public function setDate($timestamp)
    {
        $this->setAttribute('time', $timestamp);
    }

    /**
     * Set the link of the feed item
     *
     * @param string $link
     */
    public function setLink($link)
    {
        $this->setAttribute('link', $link);
    }

    /**
     * Set the permalink
     * RSS = GUID
     *
     * @param string $link
     */
    public function setPermaLink($link)
    {
        $this->setAttribute('permalink', $link);
    }

    /**
     * Set the language of the feed item
     *
     * @param string $language
     */
    public function setLanguage($language)
    {
        $this->setAttribute('lang', $language);
    }

    /**
     * Image
     */

    /**
     * Set an image for the feed item
     *
     * @param $Image
     */
    public function setImage(QUI\Projects\Media\Image $Image)
    {
        $this->Image = $Image;
    }

    /**
     * Return the feed image
     *
     * @return null|QUI\Projects\Media\Image
     */
    public function getImage()
    {
        return $this->Image;
    }

    /**
     * Remove the image for the item
     */
    public function removeImage()
    {
        $this->Image = null;
    }
}
