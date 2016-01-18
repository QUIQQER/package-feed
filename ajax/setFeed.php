<?php

/**
 * This file contains package_quiqqer_feed_ajax_setFeed
 */

/**
 * Set a feed
 *
 * @author www.pcsg.de (Henning Leutz)
 *
 * @param string|boolean $feedId - ID of the Feed
 * @param string $params - JSON params
 *
 * @return array
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_feed_ajax_setFeed',
    function ($feedId, $params) {
        $FeedManager = new QUI\Feed\Manager();
        $params      = json_decode($params, true);

        if ($feedId) {
            $Feed = $FeedManager->getFeed($feedId);
            $Feed->setAttributes($params);
            $Feed->save();

        } else {
            $Feed = $FeedManager->addFeed($params);
        }

        return $Feed->getAttributes();
    },
    array('feedId', 'params'),
    'Permission::checkAdminUser'
);
