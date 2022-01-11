<?php

/**
 * Recreate a feed
 *
 * @param int $feedId
 * @return void
 */
QUI::$Ajax->registerFunction(
    'package_quiqqer_feed_ajax_backend_recreate',
    function ($feedId) {
        $FeedManager = new QUI\Feed\Manager();
        $Feed        = $FeedManager->getFeed((int)$feedId);

        for ($pageNo = 0; $pageNo <= $Feed->getPageCount(); $pageNo++) {
            $output = $Feed->output($pageNo);

            $cacheName = 'quiqqer/feed/'.$Feed->getId()."/".$pageNo;

            try {
                QUI\Cache\Manager::set($cacheName, $output);
            } catch (\Exception $Exception) {
                QUI\System\Log::writeException($Exception);
            }
        }

        QUI::getMessagesHandler()->addSuccess(
            QUI::getLocale()->get(
                'quiqqer/feed',
                'message.ajax.backend.recreate.success',
                [
                    'feedId' => $Feed->getId()
                ]
            )
        );
    },
    ['feedId'],
    'Permission::checkAdminUser'
);
