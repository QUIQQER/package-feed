<?php

/**
 * This file contains \QUI\Feed\Events
 */

namespace QUI\Feed;

use QUI;

/**
 * Class Events -> System Events
 *
 * @package quiqqer/feed
 * @author www.pcsg.de (Henning Leutz)
 */

class Events
{
    /**
     * event : on request
     *
     * @param \QUI\Rewrite $Rewrite
     * @param String $url
     */
    static function onRequest($Rewrite, $url)
    {
        if ( stripos( $url, 'feed=' ) === false ) {
            return;
        }

        if ( strpos( $url, '.rss' ) === false ) {
            return;
        }

        $params = str_replace( '.rss', '', $url );
        $params = explode( '=', $params );

        if ( !isset( $params[ 1 ] ) ) {
            return;
        }

        $feedId = (int)$params[ 1 ];

        try
        {
            $Manager = new Manager();
            $Feed = $Manager->getFeed( $feedId );

            header('Content-Type: application/rss+xml; charset=UTF-8');

            echo $Feed->output();
            exit;

        } catch ( QUI\Exception $Exception )
        {

        }
    }
}