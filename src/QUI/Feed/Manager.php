<?php

namespace QUI\Feed;

use QUI;
use QUI\Utils\Grid;
use QUI\Utils\DOM as DOMUtils;

/**
 * Class Feed Manager
 *
 * @package quiqqer/feed
 * @author  www.pcsg.de (Henning Leutz)
 * @author  www.pcsg.de (Patrick Müller)
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
     * @param string $typeId - Feed type id
     * @param array $params - Feed attributes
     *
     * @return Feed
     *
     * @throws QUI\Exception
     */
    public function addFeed(string $typeId, $params)
    {
        if (!$this->existsType($typeId)) {
            throw new QUI\Exception([
                'quiqqer/feed',
                'exception.Manager.type_does_not_exist',
                [
                    'typeId' => $typeId
                ]
            ]);
        }

        QUI::getDataBase()->insert(
            QUI::getDBTableName(self::TABLE),
            ['type_id' => $typeId]
        );

        $id   = QUI::getDataBase()->getPDO()->lastInsertId();
        $Feed = new Feed($id);

        $Feed->setAttributes($this->filterFeedParams($typeId, $params));
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
     * Get data of a specific feed type.
     *
     * @param string $typeId
     * @return array
     *
     * @throws QUI\Exception
     */
    public function getType(string $typeId): array
    {
        foreach ($this->getTypes() as $type) {
            if ($typeId === $type['id']) {
                return $type;
            }
        }

        throw new QUI\Exception([
            'quiqqer/feed',
            'exception.Manager.type_does_not_exist',
            [
                'typeId' => $typeId
            ]
        ]);
    }

    /**
     * Get list of all available feed types
     *
     * @return array
     */
    public function getTypes(): array
    {
        $types = [];

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
                $settings     = $Feed->getElementsByTagName('category');
                $settingsHtml = '';

                foreach ($settings as $settingNode) {
                    $settingsHtml .= DOMUtils::parseCategoryToHTML($settingNode);
                    $settingsHtml = \str_replace(
                        'class="description"',
                        'class="field-container-item-desc"',
                        $settingsHtml
                    );
                }

                $type['settingsHtml'] = $settingsHtml;

                // Parse available attributes from settings HTML
                \preg_match_all('#name=[\'|"](\w*)[\'|"]#', $settingsHtml, $matches);
                $type['attributes'] = !empty($matches[1]) ? $matches[1] : [];

                $types[] = $type;
            }
        }

        return $types;
    }

    /**
     * Check if a specific feed type exists.
     *
     * @param string $typeId
     * @return bool
     */
    public function existsType(string $typeId): bool
    {
        try {
            $this->getType($typeId);
            return true;
        } catch (\Exception $Exception) {
            QUI\System\Log::writeDebugException($Exception);
            return false;
        }
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
     * Filter all feed parameters (from frontend/QUIQQER backend)
     *
     * @param string $typeId
     * @param array $params
     * @return array
     */
    public function filterFeedParams(string $typeId, array $params): array
    {
        $feedAttributes = [
            'feedDescription',
            'feedImage',
            'feedName',
            'feedlimit',
            'feedtype',
            'pagesize',
            'project',
            'publish',
            'publish-sites',
            'split'
        ];

        foreach ($this->getTypes() as $type) {
            if ($type['id'] === $typeId) {
                $feedAttributes = \array_merge(
                    $type['attributes'],
                    $feedAttributes
                );

                break;
            }
        }

        $sanitizedAttributes = [];

        foreach ($feedAttributes as $attribute) {
            if (\array_key_exists($attribute, $params)) {
                $sanitizedAttributes[$attribute] = $params[$attribute];
            } else {
                $sanitizedAttributes[$attribute] = null;
            }
        }

        return $sanitizedAttributes;
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
