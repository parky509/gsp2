jQuery(document).ready(function($) {
    $('.gsp2-action-btn').on('click', function() {
        var action = $(this).data('action');
        alert('Action: ' + action + ' - Coming soon!');
    });
});
