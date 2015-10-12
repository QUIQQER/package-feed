<?php

/**
 * This file contains package_quiqqer_feed_ajax_setFeed
 */

/**
 * Set a feed
 *
 * @author www.pcsg.de (Henning Leutz)
 *
 * @param String|Bool $feedId - ID of the Feed
 * @param String      $params - JSON params
 *
 * @return Array
 */
function package_quiqqer_feed_ajax_setFeed($feedId, $params)
{
    $FeedManager = new \QUI\Feed\Manager();
    $params = json_decode($params, true);

    if ($feedId) {
        $Feed = $FeedManager->getFeed($feedId);
        $Feed->setAttributes($params);
        $Feed->save();

    } else {
        $Feed = $FeedManager->addFeed($params);
    }

    return $Feed->getAttributes();
}

\QUI::$Ajax->register(
    'package_quiqqer_feed_ajax_setFeed',
    array('feedId', 'params'),
    'Permission::checkAdminUser'
);
