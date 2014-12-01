<?php

/**
 * This file contains package_quiqqer_feed_ajax_getList
 */

/**
 * Returns the feed list
 *
 * @author www.pcsg.de (Henning Leutz)
 * @param string $feedIds - json array, array of feed ids
 */
function package_quiqqer_feed_ajax_delete($feedIds)
{
    $FeedManager = new \QUI\Feed\Manager();
    $feedIds     = json_decode( $feedIds, true );

    foreach ( $feedIds as $feedId ) {
        $FeedManager->deleteFeed( $feedId );
    }
}

\QUI::$Ajax->register(
    'package_quiqqer_feed_ajax_delete',
    array( 'feedIds' ),
    'Permission::checkAdminUser'
);
