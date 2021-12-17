<?php

/**
 * This file contains \QUI\Feed\Manager
 */

namespace QUI\Feed;

use QUI;
use QUI\Utils\Grid;
use QUI\Cache\LongTermCache;
use QUI\Utils\Text\XML;
use QUI\Utils\DOM as DOMUtils;
use QUI\Utils\XML\Settings as XMLSettings;

/**
 * Class Feed Manager
 *
 * @package quiqqer/feed
 * @author  www.pcsg.de (Henning Leutz
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
     * @param array $params - Feed attributes
     *
     * @return Feed
     */
    public function addFeed($params)
    {
        QUI::getDataBase()->insert(
            QUI::getDBTableName(self::TABLE),
            ['feedtype' => 'rss']
        );

        $id   = QUI::getDataBase()->getPDO()->lastInsertId();
        $Feed = new Feed($id);

        $Feed->setAttributes($params);
        $Feed->save();

        return $Feed;
    }

    /**
     * Return the Feed
     *
     * @param integer $feedId - ID of the Feed
     *
     * @return Feed
     * @throws QUI\Exception
     */
    public function getFeed($feedId)
    {
        return new Feed($feedId);
    }

    /**
     * Delete a feed
     *
     * @param integer $feedId - ID of the Feed
     */
    public function deleteFeed($feedId)
    {

        try {
            $feedId = (int)$feedId;

            $this->getFeed($feedId);

            QUI::getDataBase()->delete(
                QUI::getDBTableName(Manager::TABLE),
                [
                    'id' => $feedId
                ]
            );
        } catch (QUI\Exception $Exception) {
            // feed not exist
        }
    }

    /**
     * Return the feed entries
     *
     * @param array $params
     *
     * @return array
     */
    public function getList($params = [])
    {
        if (empty($params)) {
            return QUI::getDataBase()->fetch([
                'from' => QUI::getDBTableName(self::TABLE)
            ]);
        }

        $Grid = new Grid();

        $params = array_merge($Grid->parseDBParams($params), [
            'from' => QUI::getDBTableName(self::TABLE)
        ]);

        return QUI::getDataBase()->fetch($params);
    }

    /**
     * Get list of all available feed types
     *
     * @return array
     */
    public function getTypes(): array
    {
        $types             = [];
        $XMLSettingsParser = XMLSettings::getInstance();

        foreach ($this->getFeedXmlFiles() as $xmlFile) {
            $Document = new \DOMDocument();
            $Document->load($xmlFile);

            $feeds = $Document->getElementsByTagName('feed');

            foreach ($feeds as $Feed) {
                $feedClass = $Feed->getAttribute('class');

                if (empty($feedClass)) {
                    QUI\System\Log::addError(
                        'quiqqer/feed - Feed from feeds.xml file "'.$xmlFile.'"'
                        .' is missing the "class" attribute. Feed is ignored!'
                    );

                    continue;
                }

                if (!\class_exists($feedClass) || !\is_a($feedClass, QUI\Feed\Interfaces\Feed::class, true)) {
                    QUI\System\Log::addError(
                        'quiqqer/feed - Feed class "'.$feedClass.'" from feeds.xml file "'.$xmlFile.'"'
                        .' does not exist or does not implement "QUI\Feed\Interfaces\Feed". Feed is ignored!'
                    );

                    continue;
                }

                $type = [
                    'id' => $this->parseFeedClassToHash($feedClass)
                ];


                // Title (required!)
                $title = $Feed->getElementsByTagName('title');

                if (!$title->length) {
                    QUI\System\Log::addError(
                        'quiqqer/feed - Feed class "'.$feedClass.'" from feeds.xml file "'.$xmlFile.'"'
                        .' does not have a "title" attribute in the <feed> tag. Feed is ignored!'
                    );

                    continue;
                }

                $type['title'] = DOMUtils::getTextFromNode($title->item(0));

                // Description
                $desc = $Feed->getElementsByTagName('description');

                if ($desc->length) {
                    $type['description'] = DOMUtils::getTextFromNode($desc->item(0));
                } else {
                    $type['description'] = false;
                }

                // Settings
                $settings = $Feed->getElementsByTagName('settings');

                foreach ($settings as $settingNode) {
                    $type['settingsHtml'] = $XMLSettingsParser->parseSettings($settingNode);
                }

                $types[] = $type;
            }
        }

        return $types;
    }

    /**
     * Get list of all feed.xml files of installed QUIQQER packages.
     *
     * @return string[]
     */
    protected function getFeedXmlFiles()
    {
        $packages = QUI::getPackageManager()->getInstalled();
        $list     = [];

        /* @var $Package \QUI\Package\Package */
        foreach ($packages as $package) {
            try {
                $Package = QUI::getPackage($package['name']);
            } catch (QUI\Exception $Exception) {
                continue;
            }

            if (!$Package->isQuiqqerPackage()) {
                continue;
            }

            $feedXmlFile = $Package->getDir().'/feeds.xml';

            if (\file_exists($feedXmlFile)) {
                $list[] = $feedXmlFile;
            }
        }

        return $list;
    }

    /**
     * Return the number of the feeds
     *
     * @return integer
     */
    public function count()
    {
        $result = QUI::getDataBase()->fetch([
            'count' => [
                'select' => 'id',
                'as'     => 'count'
            ],
            'from'  => QUI::getDBTableName(self::TABLE)
        ]);

        return (int)$result[0]['count'];
    }

    /**
     * @param string $feedClass
     * @return string
     */
    protected function parseFeedClassToHash(string $feedClass): string
    {
        return \hash('sha256', $feedClass);
    }
}
