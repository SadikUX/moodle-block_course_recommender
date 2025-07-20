/**
 * Course recommender AMD module.
 *
 * @module     block_course_recommender/recommender
 * @copyright  2025 Sadik Mert
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['jquery', 'core/ajax', 'core/notification', 'core/str'], function($, Ajax, Notification, Str) {

    /**
     * Initialize the module.
     */
    function init() {
        var form = $('#courserecommender-form');
        var resultsContainer = $('.courserecommender-results');

        if (!form.length || !resultsContainer.length) {
            return;
        }

        // Handle form submission
        form.on('submit', function(e) {
            e.preventDefault();
            updateResults(form, resultsContainer);
        });

        // Handle checkbox changes
        form.find('input[type="checkbox"]').on('change', function() {
            clearTimeout(window.changeTimeout);
            window.changeTimeout = setTimeout(function() {
                form.submit();
            }, 500);
        });
    }

    /**
     * Update results using AJAX.
     *
     * @param {jQuery} form The form element
     * @param {jQuery} resultsContainer The results container element
     */
    function updateResults(form, resultsContainer) {
        // Show loading indicator
        resultsContainer.html('<div class="text-center"><span class="spinner-border"></span></div>');

        // Get selected interests
        var interests = form.find('input[name="interests[]"]:checked').map(function() {
            return $(this).val();
        }).get();

        // Prepare request
        var request = {
            methodname: 'block_course_recommender_get_courses',
            args: {
                interests: interests,
                sesskey: M.cfg.sesskey
            }
        };

        // Make AJAX call
        Ajax.call([request])[0]
            .done(function(response) {
                resultsContainer.fadeOut(200, function() {
                    $(this).html(response.html).fadeIn(200);
                });
            })
            .fail(function(error) {
                Notification.exception(error);
                Str.get_string('error', 'block_course_recommender')
                    .then(function(errorStr) {
                        resultsContainer.html('<div class="alert alert-danger">' + errorStr + '</div>');
                        return;
                    });
            });
    }

    return {
        init: init
    };
});
