<?php

namespace QUI\Feed\Utils;

use QUI;

/**
 * Class Utils
 *
 * General utility methods for quiqqer/feed
 */
class Utils
{
    /**
     * Removes the https protocoll if neccessary
     *
     * @param string $url
     * @return string
     *
     * @throws QUI\Exception
     */
    public static function fixLinkProtocol(string $url): string
    {
        $forceHttp = QUI::getPackage("quiqqer/feed")->getConfig()->get("rss", "http");

        if (!$forceHttp) {
            return $url;
        }

        return str_replace("https://", "http://", $url);
    }

    /**
     * @return string
     */
    public static function getFeedTypeCachePath(): string
    {
        return 'quiqqer/feed/feed_types';
    }
}
