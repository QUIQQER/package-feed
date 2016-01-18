<?php

/**
 * This file contains QUI\Feed\Interfaces\FeedItem
 */

namespace QUI\Feed\Interfaces;

use QUI\Projects\Media\Image;

/**
 * Interface Feed
 *
 * @package quiqqer/feed
 * @author  www.pcsg.de (Henning Leutz)
 */
interface FeedItem
{
    /**
     * Set the title of the feed item
     *
     * @param string $title
     */
    public function setTitle($title);

    /**
     * Set the description of the feed item
     *
     * @param string $description
     */
    public function setDescription($description);

    /**
     * Set the unix timestamp
     *
     * @param integer $timestamp - Unix timestamp
     */
    public function setDate($timestamp);

    /**
     * Set the link of the feed item
     *
     * @param string $link
     */
    public function setLink($link);

    /**
     * Set the permalink
     * RSS = GUID
     *
     * @param string $link
     */
    public function setPermaLink($link);

    /**
     * Set the language of the feed item
     *
     * @param string $language
     */
    public function setLanguage($language);

    /**
     * Image
     */

    /**
     * Set an image for the feed item
     *
     * @param Image $Image - Image Object
     */
    public function setImage(Image $Image);

    /**
     * Return the feed image
     *
     * @return null|Image
     */
    public function getImage();

    /**
     * Remove the image for the item
     */
    public function removeImage();
}
