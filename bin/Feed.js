/**
 * Feed settings
 *
 * @module package/quiqqer/feed/bin/Feed
 * @author www.pcsg.de (Henning Leutz)
 * @author www.pcsg.de (Patrick Müller)
 */
define('package/quiqqer/feed/bin/Feed', [

    'qui/QUI',
    'qui/controls/Control',
    'qui/controls/loader/Loader',

    'qui/utils/Form',
    'utils/Controls',
    'Ajax',
    'Locale',
    'Mustache',

    'text!package/quiqqer/feed/bin/Feed.html',
    'css!package/quiqqer/feed/bin/Feed.css'

], function (QUI, QUIControl, QUILoader, QUIFormUtils, ControlUtils, QUIAjax, QUILocale, Mustache,
             template) {
    'use strict';

    const lg = 'quiqqer/feed';

    return new Class({

        Extends: QUIControl,
        Type   : 'package/quiqqer/feed/bin/Feed',

        Binds: [
            '$toogglePageSizeVisibility',
            '$togglePublishSitesVisibility',
            '$detectSplitOption',
            '$onFeedTypeChange',
            '$onProjectChange'
        ],

        initialize: function (options) {
            // defaults
            this.setAttributes({
                feedId   : false,
                project  : '',
                lang     : '',
                feedsites: '',
                feedtype : 'rss',
                feedlimit: '10',
                pageSize : 0,
                publish  : 0
            });

            this.parent(options);

            this.$FeedTypeSelect            = null;
            this.$FeedTypeDescription       = null;
            this.$FeedTypeSettingsContainer = null;

            this.$Project              = null;
            this.$ProjectSelectControl = null;

            this.$Limit                      = null;
            this.$Sites                      = null;
            this.$Name                       = null;
            this.$Desc                       = null;
            this.$PageSize                   = null;
            this.$SplitCheckbox              = null;
            this.$Image                      = null;
            this.$ImageButton                = null;
            this.$PublishSiteSelect          = null;
            this.$PublishSiteSelectContainer = null;
            this.$FeedTypes                  = {};

            this.Loader = new QUILoader();

        },

        /**
         * create the DOMNode
         *
         * @return {HTMLElement}
         */
        create: function () {
            this.$Elm = new Element('div', {
                'class': 'qui-control-feed',
                html   : Mustache.render(template, {
                    locales: {
                        feedType                   : QUILocale.get(lg, 'quiqqer.feed.feedtype'),
                        feedName                   : QUILocale.get(lg, 'quiqqer.feed.feedName'),
                        feedDescription            : QUILocale.get(lg, 'quiqqer.feed.feedDescription'),
                        project                    : QUILocale.get('quiqqer/system', 'project'),
                        feedlimit                  : QUILocale.get(lg, 'quiqqer.feed.feedlimit'),
                        feedSites                  : QUILocale.get(lg, 'quiqqer.feed.feedSites'),
                        feedSitesPlaceholder       : QUILocale.get(lg, 'quiqqer.feed.sites.placeholder'),
                        feedSitesExcludePlaceholder: QUILocale.get(lg, 'quiqqer.feed.sites_exclude.placeholder'),
                        split                      : QUILocale.get(lg, 'quiqqer.feed.split'),
                        pagesize                   : QUILocale.get(lg, 'quiqqer.feed.pageSize'),
                        publish                    : QUILocale.get(lg, 'quiqqer.feed.publish'),
                        publishSitesPlaceholder    : QUILocale.get(lg, 'quiqqer.feed.publish.sites.placeholder'),
                        feedImage                  : QUILocale.get(lg, 'quiqqer.feed.image'),
                        publishSitesLabel          : QUILocale.get(lg, 'quiqqer.feed.publishSitesLabel'),
                        headerGeneralSettings      : QUILocale.get(lg, 'quiqqer.feed.headerGeneralSettings')
                    }
                })
            });

            this.Loader.inject(this.$Elm);

            this.$FeedTypeSelect            = this.$Elm.getElement('[name="feedtype"]');
            this.$FeedTypeDescription       = this.$Elm.getElement('.qui-control-feed-type-description');
            this.$FeedTypeSettingsContainer = this.$Elm.getElement('.qui-control-feed-type-settings');

            this.$Project                    = this.$Elm.getElement('[name="project"]');
            this.$Limit                      = this.$Elm.getElement('[name="feedlimit"]');
            this.$Sites                      = this.$Elm.getElement('[name="feedsites"]');
            this.$SitesExclude               = this.$Elm.getElement('[name="feedsites_exclude"]');
            this.$Name                       = this.$Elm.getElement('[name="feedName"]');
            this.$Desc                       = this.$Elm.getElement('[name="feedDescription"]');
            this.$SplitCheckbox              = this.$Elm.getElement('[name="split"]');
            this.$PageSize                   = this.$Elm.getElement('[name="pagesize"]');
            this.$PublishCheckbox            = this.$Elm.getElement('[name="publish"]');
            this.$Image                      = this.$Elm.getElement('[name="feedImage"]');
            this.$ImageButton                = this.$Elm.getElement('.qui-control-feed-btn-image');
            this.$PublishSiteSelect          = this.$Elm.getElement('[name="publish-sites"]');
            this.$PublishSiteSelectContainer = this.$Elm.getElement('.qui-feed-publish-sites-container');

            var self = this;
            // Split Feed event
            //self.$Elm.getElement('.qui-feed-feedwindow-split-label').addEvent('click', function () {
            //    self.$SplitCheckbox.checked = !self.$SplitCheckbox.checked;
            //    self.$toogglePageSizeVisibility();
            //});
            this.$SplitCheckbox.addEvent('change', self.$toogglePageSizeVisibility);

            // Publish sites event
            this.$PublishCheckbox.addEvent('change', self.$togglePublishSitesVisibility);

            //this.$Elm.getElement('.qui-feed-feedwindow-publish-label').addEvent('click', function () {
            //    self.$PublishCheckbox.checked = !self.$PublishCheckbox.checked;
            //    self.$togglePublishSitesVisibility();
            //});

            // Project select event
            this.$Project.addEvent('change', this.$onProjectChange);
            this.$onProjectChange();

            // Hide the split option, if it is not supported for the feed type
            //this.$FeedTypeSelect.addEvent('change', self.$detectSplitOption);
            this.$FeedTypeSelect.addEvent('change', this.$onFeedTypeChange);
            this.$detectSplitOption();

            this.Loader.show();

            this.$getFeedTypes().then((feedTypes) => {
                for (const FeedType of Object.values(feedTypes)) {
                    this.$FeedTypes[FeedType.id] = FeedType;

                    new Element('option', {
                        value: FeedType.id,
                        html : FeedType.title
                    }).inject(this.$FeedTypeSelect);
                }

                this.$onFeedTypeChange();
            }).then(
                ControlUtils.parse(this.$Elm)
            ).then(
                QUI.parse(this.$Elm)
            ).then(() => {
                this.$ProjectSelectControl = QUI.Controls.getById(
                    self.$Project.get('data-quiid')
                );

                this.Loader.hide();
            });

            return this.$Elm;
        },

        /**
         * refresh the data
         */
        refresh: function () {
            var self = this;
            QUIAjax.get('package_quiqqer_feed_ajax_getFeed', function (result) {
                var quiid, Cntrl;

                self.$Sites.value             = result.feedsites;
                self.$SitesExclude.value      = result.feedsites_exclude;
                self.$FeedTypeSelect.value    = result.feedtype;
                self.$Limit.value             = result.feedlimit;
                self.$Name.value              = result.feedName;
                self.$Desc.value              = result.feedDescription;
                self.$PublishCheckbox.checked = result.publish === '1';
                self.$PublishSiteSelect.value = result.publish_sites;
                self.$Image.value             = result.feedImage;

                if (result.pageSize > 0) {
                    self.$PageSize.value        = result.pageSize;
                    self.$SplitCheckbox.checked = true;
                    self.$toogglePageSizeVisibility();
                }

                self.$detectSplitOption();
                self.$togglePublishSitesVisibility();

                self.$Project.value = JSON.encode([
                    {
                        project: result.project,
                        lang   : result.lang
                    }]);

                // project
                quiid = self.$Project.get('data-quiid');
                Cntrl = QUI.Controls.getById(quiid);

                if (result.project !== '') {
                    if (Cntrl) {
                        Cntrl.addProject(result.project, result.lang);
                    }
                }

                // site types
                quiid = self.$Sites.get('data-quiid');
                Cntrl = QUI.Controls.getById(quiid);

                self.$Sites.set('data-project', result.project);
                self.$Sites.set('data-lang', result.lang);

                if (Cntrl) {
                    Cntrl.setAttribute('placeholder', QUILocale.get(lg, 'quiqqer.feed.publish.sites.placeholder'));

                    Cntrl.setProject(result.project, result.lang);
                    Cntrl.setValue(self.$Sites.value);
                    Cntrl.refresh();
                }

                // site types (exclude)
                quiid = self.$SitesExclude.get('data-quiid');
                Cntrl = QUI.Controls.getById(quiid);

                self.$SitesExclude.set('data-project', result.project);
                self.$SitesExclude.set('data-lang', result.lang);

                if (Cntrl) {
                    //Cntrl.setAttribute('placeholder', QUILocale.get(lg, 'quiqqer.feed.publish.sites.placeholder'));

                    Cntrl.setProject(result.project, result.lang);
                    Cntrl.setValue(self.$SitesExclude.value);
                    Cntrl.refresh();
                }

                // Refresh the PublishSites control
                var PublishSitesControl = QUI.Controls.getById(self.$PublishSiteSelect.get('data-quiid'));
                if (PublishSitesControl) {
                    PublishSitesControl.setAttribute('placeholder', QUILocale.get(lg, 'quiqqer.feed.sites.placeholder'));
                    PublishSitesControl.setProject(result.project, result.lang);
                    PublishSitesControl.setValue(self.$PublishSiteSelect.value);
                    PublishSitesControl.refresh();
                }

            }, {
                'package': 'quiqqer/feed',
                feedId   : this.getAttribute('feedId')
            });
        },

        /**
         * Return the feed attributes
         *
         * @returns {Object}
         */
        getFeedData: function () {
            const Data     = QUIFormUtils.getDataFromNode(this.$Elm);
            const FeedType = this.$FeedTypes[Data.feedtype];

            const allowedAttributes = [
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

            allowedAttributes.append(FeedType.attributes);

            const FeedData = {};

            allowedAttributes.forEach((feedAttribute) => {
                if (feedAttribute in Data) {
                    FeedData[feedAttribute] = Data[feedAttribute];
                }
            });

            return FeedData;
        },

        /**
         * Save the feed
         *
         * @return {Promise}
         */
        save: function () {
            return new Promise((resolve, reject) => {
                QUIAjax.post('package_quiqqer_feed_ajax_setFeed', resolve, {
                    'package': lg,
                    feedId   : this.getAttribute('feedId'),
                    params   : JSON.encode(this.getFeedData()),
                    onError  : reject
                });
            });
        },

        /**
         * Toggles the visibility of the page size input field
         */
        $toogglePageSizeVisibility: function () {
            var pageSizeContainer = this.$Elm.getElement('.qui-feed-feetwindow-pagesize');

            if (this.$SplitCheckbox.checked) {
                pageSizeContainer.style.display = 'block';
            } else {
                pageSizeContainer.style.display = 'none';
            }
        },

        /**
         * Toggles the visibility of the input to select sites on which the feed should get displayed
         */
        $togglePublishSitesVisibility: function () {
            if (this.$PublishCheckbox.checked) {
                this.$PublishSiteSelectContainer.style.display = 'block';
            } else {
                this.$PublishSiteSelectContainer.style.display = 'none';
            }
        },

        /**
         * Hides/Shows the visiblity of thec checkbox to split the feed into pages, depending o the current selected feeds support for pagination
         */
        $detectSplitOption: function () {
            if (this.$FeedTypeSelect.value !== 'googleSitemap') {
                this.$SplitCheckbox.checked                                         = false;
                this.$Elm.getElement('.qui-feed-feetwindow-pagesize').style.display = 'none';
                this.$Elm.getElement('.qui-feed-feedwindow-split').style.display    = 'none';
            } else {
                this.$Elm.getElement('.qui-feed-feedwindow-split').style.display = 'block';
            }
        },

        /**
         * If the user changes the project
         */
        $onProjectChange: function () {
            if (!this.$ProjectSelectControl) {
                return;
            }

            const siteSelectControlElements = this.$Elm.getElements(
                '[data-qui="controls/projects/project/site/Select"]'
            );

            this.$ProjectSelectControl.getProjects().each(function (Project) {
                siteSelectControlElements.forEach((ControlElm) => {
                    const Control = QUI.Controls.getById(ControlElm.get('data-quiid'));

                    Control.setProject(
                        Project.getName(),
                        Project.getLang()
                    );
                });
            });
        },

        /**
         * Event: on feed type change
         */
        $onFeedTypeChange: function () {
            const FeedType = this.$FeedTypes[this.$FeedTypeSelect.value];

            if (FeedType.description) {
                this.$FeedTypeDescription.set('html', FeedType.description);
                this.$FeedTypeDescription.removeClass('qui-control-feed__hidden');
            } else {
                this.$FeedTypeDescription.addClass('qui-control-feed__hidden');
            }

            this.$FeedTypeSettingsContainer.set('html', FeedType.settingsHtml);

            // @todo set data of currently selected Feed to HTML (form)

            this.Loader.show();

            QUI.parse(this.$FeedTypeSettingsContainer).then(() => {
                this.Loader.hide();
            });
        },

        /**
         * Get all feed types installed on this system
         *
         * @return {Promise}
         */
        $getFeedTypes: function () {
            return new Promise((resolve, reject) => {
                QUIAjax.get('package_quiqqer_feed_ajax_backend_getTypes', resolve, {
                    'package': lg,
                    onError  : reject
                });
            });
        }
    });
});