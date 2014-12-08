
/**
 * Feed manager
 *
 * @module packages/quiqqer/feed/bin/Manager
 * @author www.pcsg.de (Henning Leutz)
 *
 * @require qui/QUI
 * @require qui/controls/desktop/Panel
 * @require controls/grid/Grid
 * @require package/quiqqer/feed/bin/FeedWindow
 * @require Locale
 * @require Ajax
 * @require Projects
 */

define('package/quiqqer/feed/bin/Manager', [

    'qui/QUI',
    'qui/controls/desktop/Panel',
    'qui/controls/windows/Confirm',
    'controls/grid/Grid',
    'package/quiqqer/feed/bin/FeedWindow',
    'Locale',
    'Ajax',
    'Projects'

], function(QUI, QUIPanel, QUIConfirm, Grid, FeedWindow, QUILocale, Ajax, Projects)
{
    "use strict";

    var lg = 'quiqqer/feed';

    return new Class({

        Extends : QUIPanel,
        Type    : 'package/quiqqer/feed/bin/Manager',

        Binds : [
            '$onCreate',
            '$onResize'
        ],

        options : {
            title : QUILocale.get( lg, 'feed.manager.title' )
        },

        initialize : function(options)
        {
            this.parent( options );

            this.$Grid = null;

            this.addEvents({
                onCreate : this.$onCreate,
                onResize : this.$onResize
            });
        },

        /**
         * Create the panel
         */
        $onCreate : function()
        {
            var self = this;

            this.addButton({
                name : 'add',
                text : 'Neuen Feed anlegen',
                textimage : 'icon-plus',
                events :
                {
                    onClick : function() {
                        self.openFeedWindow();
                    }
                }
            });

            this.addButton({
                type : 'seperator'
            });

            this.addButton({
                name : 'delete',
                text : 'Markierten Feed löschen',
                textimage : 'icon-trash',
                disabled  : true,
                events :
                {
                    onClick : function()
                    {
                        var ids = self.$Grid.getSelectedData().map(function(entry) {
                            return entry.id;
                        });

                        self.openFeedDeleteWindow( ids );
                    }
                }
            });

            // Grid
            var Content   = this.getContent(),
                Container = new Element('div').inject( Content );

            this.$Grid = new Grid( Container, {
                columnModel : [{
                    header    : QUILocale.get( 'quiqqer/system', 'id' ),
                    dataIndex : 'id',
                    dataType  : 'string',
                    width     : 40
                }, {
                    header    : QUILocale.get( lg, 'quiqqer.feed.feedtype' ),
                    dataIndex : 'feedtype',
                    dataType  : 'string',
                    width     : 80
                }, {
                    header    : QUILocale.get( 'quiqqer/system', 'project' ),
                    dataIndex : 'project',
                    dataType  : 'string',
                    width     : 120
                }, {
                    header    : QUILocale.get( 'quiqqer/system', 'language' ),
                    dataIndex : 'lang',
                    dataType  : 'string',
                    width     : 80
                }, {
                    header    : QUILocale.get( lg, 'quiqqer.feed.feedlimit' ),
                    dataIndex : 'feedlimit',
                    dataType  : 'string',
                    width     : 80
                }],
                pagination            : true,
                multipleSelection     : true,
                accordion             : true,
                openAccordionOnClick  : false,
                accordionLiveRenderer : function(data)
                {
                    var GridObj = data.grid,
                        Parent  = data.parent,
                        row     = data.row,
                        rowData = GridObj.getDataByRow( row );

                    Parent.set( 'html', '' );

                    var Project = Projects.get( rowData.project, rowData.lang);

                    Project.getHost(function(host)
                    {
                        var url = host +'/feed='+ rowData.id +'.xml';

                        new Element('div', {
                            html   : 'Feed URL: '+ url,
                            styles : {
                                padding: 10
                            }
                        }).inject( Parent );
                    });
                }
            });

            this.$Grid.addEvents({

                onRefresh : function() {
                    self.refresh();
                },

                onDblClick : function(event)
                {
                    self.openFeedWindow(
                        self.$Grid.getDataByRow( event.row ).id
                    );
                },

                onClick : function() {
                    self.getButtons( 'delete' ).enable();
                }
            });

            this.refresh();
        },

        /**
         * Load the feeds
         */
        refresh : function()
        {
            if ( !this.$Grid ) {
                return;
            }

            var self = this;

            Ajax.get('package_quiqqer_feed_ajax_getList', function(result)
            {
                self.$Grid.setData( result );
            }, {
                'package'  : 'quiqqer/feed',
                gridParams : JSON.encode( this.$Grid.getPaginationData() )
            });
        },

        /**
         * panel resize
         */
        $onResize : function()
        {
            if ( !this.$Grid ) {
                return;
            }

            var Body = this.getContent();

            if ( !Body ) {
                return;
            }

            var size = Body.getSize();

            this.$Grid.setHeight( size.y - 40 );
            this.$Grid.setWidth( size.x - 40 );
        },

        /**
         * Open the feed window
         *
         * @param {Number} [feedId] - (optional) ID of the Feed, if no ID a new Feed would be added
         */
        openFeedWindow : function(feedId)
        {
            var self = this;

            new FeedWindow({
                feedId : feedId || false,
                events :
                {
                    onClose : function() {
                        self.refresh();
                    }
                }
            }).open();
        },

        /**
         * Open the feed deletion
         *
         * @param {Array} feedIds - ID of the Feed
         */
        openFeedDeleteWindow : function(feedIds)
        {
            if ( typeOf( feedIds ) !== 'array' ) {
                return;
            }

            var self = this;

            new QUIConfirm({
                title : 'Feeds löschen',
                icon  : 'icon-trash',
                text  : 'Möchten Sie folgende Feeds wirklich löschen?',
                information : feedIds.join(', '),
                autoclose : false,
                events :
                {
                    onSubmit : function(Win)
                    {
                        Win.Loader.show();

                        Ajax.post('package_quiqqer_feed_ajax_delete', function()
                        {
                            self.refresh();
                            Win.close();

                        }, {
                            'package' : 'quiqqer/feed',
                            feedIds   : JSON.encode( feedIds )
                        });
                    }
                }
            }).open();
        }
    });
});