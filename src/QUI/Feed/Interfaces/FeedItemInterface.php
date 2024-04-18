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
interface FeedItemInterface
{
    /**
     * Set the title of the feed item
     *
     * @param string $title
     */
    public function setTitle(string $title);

    /**
     * Set the description of the feed item
     *
     * @param string $description
     */
    public function setDescription(string $description);

    /**
     * Set the unix timestamp
     *
     * @param integer $timestamp - Unix timestamp
     */
    public function setDate(int $timestamp);

    /**
     * Set the link of the feed item
     *
     * @param string $link
     */
    public function setLink(string $link);

    /**
     * Set the permalink
     * RSS = GUID
     *
     * @param string $link
     */
    public function setPermaLink(string $link);

    /**
     * Set the language of the feed item
     *
     * @param string $language
     */
    public function setLanguage(string $language);

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
    public function getImage(): ?Image;

    /**
     * Remove the image for the item
     */
    public function removeImage();
}
