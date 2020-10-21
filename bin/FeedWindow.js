/**
 * Feed window
 *
 * @module package/quiqqer/feed/bin/FeedWindow
 * @author www.pcsg.de (Henning Leutz)
 *
 * @require qui/QUI
 * @require qui/controls/windows/Confirm
 * @require package/quiqqer/feed/bin/Feed
 * @require Locale
 */
define('package/quiqqer/feed/bin/FeedWindow', [

    'qui/QUI',
    'qui/controls/windows/Confirm',
    'package/quiqqer/feed/bin/Feed',
    'Locale'

], function (QUI, QUIConfirm, Feed, Locale) {
    "use strict";

    return new Class({

        Extends: QUIConfirm,
        Type   : 'package/quiqqer/feed/bin/FeedWindow',

        Binds: [
            '$onOpen',
            '$onSubmit'
        ],

        initialize: function (options) {
            this.$Feed = null;

            // defaults
            this.setAttributes({
                maxHeight: 700,
                maxWidth : 600,
                feedId   : false,
                icon     : 'fa fa-rss',
                autoclose: false,
                title    : Locale.get('quiqqer/feed', 'window.title.feed.edit'),
                texticon : false
            });

            this.parent(options);

            this.addEvents({
                onOpen  : this.$onOpen,
                onSubmit: this.$onSubmit
            });
        },

        /**
         * event : on open
         *
         * @param {Object} Win - qui/controls/windows/Popup
         */
        $onOpen: function (Win) {
            if (!this.getAttribute('feedId')) {
                this.setAttribute('title', Locale.get('quiqqer/feed', 'window.title.feed.create'));
            } else {
                this.setAttribute('title', Locale.get('quiqqer/feed', 'window.title.feed.edit'));
            }

            this.refresh();


            this.$Feed = new Feed({
                feedId: this.getAttribute('feedId')
            }).inject(Win.getContent());
        },

        /**
         * event : on submit
         */
        $onSubmit: function (Win) {
            Win.Loader.show();

            this.$Feed.save(function () {
                Win.close();
            });
        }
    });
});
