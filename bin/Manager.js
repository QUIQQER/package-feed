
/**
 * Feed manager
 *
 * @module packages/quiqqer/feed/bin/Manager
 * @author www.pcsg.de (Henning Leutz)
 */

define('package/quiqqer/feed/bin/Manager', [

    'qui/QUI',
    'qui/controls/desktop/Panel',
    'qui/Locale',
    'controls/grid/Grid',
    'Ajax'

], function(QUI, QUIPanel, QUILocale, Grid, Ajax)
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
                    onClick : function() {
                        console.log( 'asd' );
                    }
                }
            });

            // Grid
            var Content   = this.getContent(),
                Container = new Element('div').inject( Content );

            this.$Grid = new Grid( Container, {
                columnModel : [{
                    header    : Locale.get( 'quiqqer/system', 'id' ),
                    dataIndex : 'id',
                    dataType  : 'string',
                    width     : 80
                }, {
                    header    : Locale.get( 'quiqqer/system', 'project' ),
                    dataIndex : 'project',
                    dataType  : 'string',
                    width     : 200
                }, {
                    header    : Locale.get( 'quiqqer/system', 'lang' ),
                    dataIndex : 'lang',
                    dataType  : 'string',
                    width     : 80
                }],
                pagination        : true,
                multipleSelection : true
            });

            this.$Grid.addEvents({

                onRefresh : function() {
                    self.refresh();
                },

                onDblClick : function(event)
                {

                    //self.$Grid.getDataByRow( event.row ).id

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

            Ajax.get('package_quiqqer_feed_ajax_getList', function(result)
            {
                console.log( result );
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
         *
         * @param {Integer} feedId
         */
        openFeedWindow : function(feedId)
        {

        }
    });
});