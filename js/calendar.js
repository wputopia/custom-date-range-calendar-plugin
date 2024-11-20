jQuery(document).ready(function($) {
    function updateCalendar(month, year, updateUrl = true) {
        var container = $('#ajax-calendar-container');
        container.addClass('loading');
        
        $.ajax({
            url: calendarAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'get_calendar',
                month: month,
                year: year,
                nonce: calendarAjax.nonce
            },
            success: function(response) {
                container.html(response);
                
                // Update the month and year selectors
                $('#calendar-month').val(month);
                $('#calendar-year').val(year);
                
                // Update URL if needed
                if (updateUrl && window.history && window.history.pushState) {
                    var newUrl = new URL(window.location);
                    newUrl.searchParams.set('cal_month', month);
                    newUrl.searchParams.set('cal_year', year);
                    window.history.pushState({}, '', newUrl);
                }
            },
            error: function(xhr, status, error) {
                console.error('Calendar update failed:', error);
            },
            complete: function() {
                container.removeClass('loading');
            }
        });
    }
    
    // Handle month/year selector changes
    $(document).on('change', '#calendar-month, #calendar-year', function(e) {
        var month = $('#calendar-month').val();
        var year = $('#calendar-year').val();
        updateCalendar(month, year);
    });
    
    // Handle navigation buttons
    $(document).on('click', '.calendar-nav-link', function(e) {
        e.preventDefault();
        var month = $(this).data('month');
        var year = $(this).data('year');
        updateCalendar(month, year);
    });

    // On page load, check URL parameters
    var urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('cal_month') && urlParams.has('cal_year')) {
        updateCalendar(urlParams.get('cal_month'), urlParams.get('cal_year'), false);
    }
});