<?php

/**
 * This file contains \QUI\Feed\Manager
 */

namespace QUI\Feed;

use QUI;
use QUI\Utils\Grid;

/**
 * Class Feed Manager
 *
 * @package quiqqer/feed
 * @author www.pcsg.de (Henning Leutz
 */
class Manager
{
    /**
     * Feed Table
     */
    const TABLE = 'feeds';

    /**
     * Add a new feed
     *
     * @param Array $params - Feed attributes
     * @return Feed
     */
    public function addFeed($params)
    {
        \QUI::getDataBase()->insert(
            QUI::getDBTableName( self::TABLE ),
            array( 'feedtype' => 'rss' )
        );

        $id   = \QUI::getDataBase()->getPDO()->lastInsertId();
        $Feed = new Feed( $id );

        $Feed->setAttributes( $params );
        $Feed->save();

        return $Feed;
    }

    /**
     * Return the Feed
     *
     * @param Integer $feedId - ID of the Feed
     * @return Feed
     */
    public function getFeed($feedId)
    {
        return new Feed( $feedId );
    }

    /**
     * Return the feed entries
     *
     * @param array $params
     * @return Array
     */
    public function getList($params=array())
    {
        if ( empty( $params ) )
        {
            return QUI::getDataBase()->fetch(array(
                'from'  => QUI::getDBTableName( self::TABLE )
            ));
        }

        $Grid = new Grid();

        $params = array_merge( $Grid->parseDBParams( $params ), array(
            'from'  => QUI::getDBTableName( self::TABLE )
        ));

        return QUI::getDataBase()->fetch( $params );
    }

    /**
     * Return the number of the feeds
     *
     * @return int
     */
    public function count()
    {
        $result = QUI::getDataBase()->fetch(array(
            'count' => array(
                'select' => 'id',
                'as'     => 'count'
            ),
            'from' => QUI::getDBTableName( self::TABLE )
        ));

        return (int)$result[ 0 ][ 'count' ];
    }
}