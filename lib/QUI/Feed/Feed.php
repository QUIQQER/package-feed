<?php

/**
 * This file contains \QUI\Feed\Feed;
 */

namespace QUI\Feed;

use QUI;

/**
 * Class Feed
 * One Feed, you can edit and save the feed, and get the feed as an XML / RSS Feed
 *
 * @author www.pcsg.de (Henning Leutz)
 * @package QUI\Feed
 */

class Feed extends \QUI\QDOM
{
    /**
     * ID of the Feed
     * @var Integer
     */
    protected $_feedId;

    /**
     * Constructor
     *
     * @param Integer $feedId
     * @throws \QUI\Exception
     */
    public function __construct($feedId)
    {
        $this->_feedId = (int)$feedId;

        $data = QUI::getDataBase()->fetch(array(
            'from' => QUI::getDBTableName( Manager::TABLE ),
            'where' => array(
                'id' => $this->_feedId
            ),
            'limit' => 1
        ));

        if ( !isset( $data[ 0 ] ) )
        {
            throw new QUI\Exception(
                'Feed not found'
            );
        }

        $this->setAttributes( $data[ 0 ] );
    }

    /**
     * Return the Feed-ID
     * @return Integer
     */
    public function getId()
    {
        return $this->_feedId;
    }

    /**
     * Return the feed tyoe
     *
     * @return String (rss|atom|googleSitemap)
     */
    public function getFeedType()
    {
        $feedtype = 'rss';

        switch ( $this->getAttribute( 'feedtype' ) )
        {
            case 'atom':
            case 'googleSitemap':
                $feedtype = $this->getAttribute( 'feedtype' );
            break;
        }

        return $feedtype;
    }

    /**
     * Return the feed attributes
     * @return Array
     */
    public function getAttributes()
    {
        $attributes = parent::getAttributes();
        $attributes['id'] = $this->_feedId;

        return $attributes;
    }

    /**
     * Save the feed
     */
    public function save()
    {
        $table     = QUI::getDBTableName( Manager::TABLE );
        $feedlimit = (int)$this->getAttribute('feedlimit');

        \QUI::getDataBase()->update($table, array(
            'project'   => $this->getAttribute('project'),
            'lang'      => $this->getAttribute('lang'),
            'feedsites' => $this->getFeedType(),
            'feedlimit' => $feedlimit ? $feedlimit : ''
        ), array(
            'id' => $this->getId()
        ));
    }
}
