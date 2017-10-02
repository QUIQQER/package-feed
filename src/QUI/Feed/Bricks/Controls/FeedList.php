<?php

namespace QUI\Feed\Bricks\Controls;

use QUI;

class FeedList extends QUI\Control
{

    public function __construct($attributes = array())
    {

        $this->setAttributes(array(
            'title'    => '',
            'text'     => '',
            'class'    => 'qui-feeds-brick-FeedList',
            'nodeName' => 'div'
        ));

        $this->addCSSFile(
            dirname(__FILE__) . '/FeedList.css'
        );


        parent::__construct($attributes);
    }


    public function getBody()
    {
        $Engine = QUI::getTemplateManager()->getEngine();


        if ($this->getAttribute("layout") == "icons") {
            $this->addCSSFile(
                dirname(__FILE__) . '/FeedListIcons.css'
            );
        }

        $Engine->assign(array(
            'this'      => $this,
            'feeds'     => $this->getFeeds(),
            'newwindow' => $this->getAttribute("newwindow"),
            'layout'    => $this->getAttribute("layout")
        ));


        $template = dirname(__FILE__) . '/FeedList.html';
        if ($this->getAttribute("layout") == "icons") {
            $template = dirname(__FILE__) . '/FeedListIcons.html';
        }

        return $Engine->fetch($template);
    }

    /**
     * Gets all currently configured feeds
     *
     * @return array
     */
    protected function getFeeds()
    {
        $Manager = new QUI\Feed\Manager();

        $configuredFeeds = $Manager->getList();

        $result = array();

        foreach ($configuredFeeds as $feed) {
            $feedID      = $feed['id'];
            $name        = $feed['feedName'];
            $type        = $feed['feedtype'];
            $description = $feed['feedDescription'];
            $project     = $feed['project'];
            $language    = $feed['lang'];
            $publish     = $feed['publish'];

            $curProject = QUI::getRewrite()->getProject();

            if (!$publish) {
                continue;
            }

            if ($curProject->getName() != $project) {
                continue;
            }

            if ($curProject->getLang() != $language) {
                continue;
            }

            if ($type == "googleSitemap") {
                continue;
            }


            $projectHost = $curProject->getVHost(true, true);
            $url         = $projectHost . URL_DIR . 'feed=' . $feedID . '.xml';

            $result[] = array(
                "feedID" => $feedID,
                "name"   => $name,
                "type"   => $type,
                "desc"   => $description,
                "url"    => $url
            );
        }


        if ($this->getAttribute("limit") > 0) {
            $result = array_slice($result, 0, $this->getAttribute("limit"));
        }


        return $result;
    }
}
