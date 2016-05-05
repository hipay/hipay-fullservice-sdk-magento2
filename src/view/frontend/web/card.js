/**
 * HiPay fullservice SDK
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 *
 * @copyright      Copyright (c) 2016 - HiPay
 * @license        http://opensource.org/licenses/mit-license.php MIT License
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
                            window.location = self.options.deleteUrlPrefix + $(e.target).data('card');  
                    }
                }
            });

            return false;
        }
    });
    
    return $.hipay.card;
});