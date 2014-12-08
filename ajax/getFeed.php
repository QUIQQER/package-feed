<?php

/**
 * This file contains package_quiqqer_feed_ajax_getFeed
 */

/**
 * Returns the feed list
 *
 * @author www.pcsg.de (Henning Leutz)
 * @param Integer $feedId - ID of the Feed
 * @return Array
 */
function package_quiqqer_feed_ajax_getFeed($feedId)
{
    $FeedManager = new \QUI\Feed\Manager();
    return $FeedManager->getFeed( $feedId )->getAttributes();
}

\QUI::$Ajax->register(
    'package_quiqqer_feed_ajax_getFeed',
    array( 'feedId' ),
    'Permission::checkAdminUser'
);
