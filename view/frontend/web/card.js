/**
 * HiPay Fullservice Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Apache 2.0 Licence
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * @author Kassim Belghait <kassim@sirateck.com>
 * @copyright Copyright (c) 2016 - HiPay
 * @license http://www.apache.org/licenses/LICENSE-2.0 Apache 2.0 Licence
 * @link https://github.com/hipay/hipay-fullservice-sdk-magento2
 *
 */
/*jshint browser:true, jquery:true*/
/*global confirm:true*/
define([
    "jquery",
    'Magento_Ui/js/modal/confirm',
    "jquery/ui",
    "mage/translate"
], function($, confirm){
    "use strict";
    
    $.widget('hipay.card', {
        /**
         * Options common to all instances of this widget.
         * @type {Object}
         */
        options: {
            deleteConfirmMessage: $.mage.__('Are you sure you want to delete this card?')
        },

        /**
         * Bind event handlers fordeleting cards.
         * @private
         */
        _create: function() {
            var options         = this.options,
                deleteCard   = options.deleteCard;
            
            if( deleteCard ){
                $(document).on('click', deleteCard, this._deleteCard.bind(this));
            }
        },

        /**
         * Delete the card whose id is specified in a data attribute after confirmation from the user.
         * @private
         * @param {Event}
         * @return {Boolean}
         */
        _deleteCard: function(e) {
            var self = this;

            confirm({
                content: this.options.deleteConfirmMessage,
                actions: {
                    confirm: function() {
                    	if (typeof $(e.target).parent().data('card') !== 'undefined') {
                            window.location = self.options.deleteUrlPrefix + $(e.target).parent().data('card');
                        }
                        else {
                            window.location = self.options.deleteUrlPrefix + $(e.target).data('card');
                        }
   
                    }
                }
            });

            return false;
        }
    });
    
    return $.hipay.card;
});