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
     * @return void
     */
    static function onRequest($Rewrite, $url)
    {
        if ( stripos( $url, 'feed=' ) === false ) {
            return;
        }

        if ( strpos( $url, '.xml' ) === false ) {
            return;
        }

        $params = str_replace( '.xml', '', $url );
        $params = explode( '=', $params );

        if ( !isset( $params[ 1 ] ) ) {
            return;
        }

        $feedId    = (int)$params[ 1 ];
        $cacheName = 'quiqqer/feed/'. $feedId;

        try
        {

            echo QUI\Cache\Manager::get( $cacheName );
            exit;

        } catch ( QUI\Exception $Exception )
        {

        }

        try
        {
            $Manager = new Manager();
            $Feed = $Manager->getFeed( $feedId );

            header('Content-Type: application/rss+xml; charset=UTF-8');

            $output = $Feed->output();

            QUI\Cache\Manager::set( $cacheName, $output );

            echo $output;
            exit;

        } catch ( QUI\Exception $Exception )
        {

        }
    }

    /**
     * event : site change it
     * @param \QUI\Projects\Site $Site
     */
    static function onSiteChange($Site)
    {
        // get feeds by project
        $Project = $Site->getProject();
        $Manager = new Manager();

        $projectName = $Project->getName();
        $projectLang = $Project->getLang();

        $feedList = $Manager->getList();

        foreach ( $feedList as $feed )
        {
            if ( $projectName != $feed['project'] ) {
                continue;
            }

            if ( $projectLang != $feed['lang'] ) {
                continue;
            }

            // clear cache
            QUI\Cache\Manager::clear( 'quiqqer/feed/'.$feed['id'] );
        }
    }
}
