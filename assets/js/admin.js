/**
 * GlobalSwiftPay2 Investment Dashboard - Admin JavaScript
 * Handles admin panel interactions and AJAX requests
 */

(function($) {
    'use strict';

    const GSP2Admin = {
        init: function() {
            this.bindEvents();
        },

        bindEvents: function() {
            // Settings form submission
            $('#gsp2-settings-form').on('submit', this.handleSettingsSubmit);
            
            // Approve/Decline buttons
            $('.gsp2-approve-btn').on('click', this.handleApprove);
            $('.gsp2-decline-btn').on('click', this.handleDecline);
        },

        handleSettingsSubmit: function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            formData.append('action', 'gsp2_update_settings');
            formData.append('nonce', gsp2_admin_ajax.nonce);

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: formData,
                processData: false,
                contentType: false,
                success: function(response) {
                    if (response.success) {
                        alert('Settings updated successfully!');
                    } else {
                        alert('Error: ' + response.data.message);
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                }
            });
        },

        handleApprove: function() {
            const requestId = $(this).data('id');
            const requestType = $(this).data('type');
            
            if (!confirm('Are you sure you want to approve this request?')) {
                return;
            }

            GSP2Admin.processRequest(requestId, requestType, 'approve');
        },

        handleDecline: function() {
            const requestId = $(this).data('id');
            const requestType = $(this).data('type');
            
            const adminNote = prompt('Enter a reason for declining (optional):');
            
            GSP2Admin.processRequest(requestId, requestType, 'decline', adminNote);
        },

        processRequest: function(requestId, requestType, action, adminNote) {
            const ajaxAction = 'gsp2_' + action + '_' + requestType;

            $.ajax({
                url: ajaxurl,
                type: 'POST',
                data: {
                    action: ajaxAction,
                    request_id: requestId,
                    admin_note: adminNote || '',
                    nonce: gsp2_admin_ajax.nonce
                },
                success: function(response) {
                    if (response.success) {
                        alert(response.data.message);
                        location.reload();
                    } else {
                        alert('Error: ' + response.data.message);
                    }
                },
                error: function() {
                    alert('An error occurred. Please try again.');
                }
            });
        }
    };

    // Initialize on document ready
    $(document).ready(function() {
        GSP2Admin.init();
    });

})(jQuery);
