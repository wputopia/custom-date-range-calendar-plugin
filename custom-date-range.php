<?php
/*
Plugin Name: Custom Date Range Calendar
Description: Displays a calendar widget with post date ranges
Version: 1.0
Author: Victor from WPutopia.com
*/

// Add Meta Box for Date Range
function add_date_range_meta_box() {
    add_meta_box(
        'date_range_meta_box',
        'Post Date Range',
        'display_date_range_meta_box',
        'post',
        'side',
        'high'
    );
}
add_action('add_meta_boxes', 'add_date_range_meta_box');

// Display Meta Box Content
function display_date_range_meta_box($post) {
    wp_nonce_field('date_range_meta_box', 'date_range_meta_box_nonce');
    
    $start_date = get_post_meta($post->ID, '_post_start_date', true);
    $end_date = get_post_meta($post->ID, '_post_end_date', true);
    
    if (empty($start_date) && empty($end_date)) {
        $post_date = get_the_date('Y-m-d', $post->ID);
        $start_date = $post_date;
        $end_date = $post_date;
    }
    ?>
    <p>
        <label for="post_start_date">Start Date:</label><br>
        <input type="date" id="post_start_date" name="post_start_date" value="<?php echo esc_attr($start_date); ?>">
    </p>
    <p>
        <label for="post_end_date">End Date:</label><br>
        <input type="date" id="post_end_date" name="post_end_date" value="<?php echo esc_attr($end_date); ?>">
    </p>
    <p class="description">Leave empty to use post publication date</p>
    <?php
}

// Save Meta Box Data
function save_date_range_meta_box($post_id) {
    if (!isset($_POST['date_range_meta_box_nonce']) ||
        !wp_verify_nonce($_POST['date_range_meta_box_nonce'], 'date_range_meta_box')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    $post_date = get_the_date('Y-m-d', $post_id);
    
    $start_date = !empty($_POST['post_start_date']) ? sanitize_text_field($_POST['post_start_date']) : $post_date;
    $end_date = !empty($_POST['post_end_date']) ? sanitize_text_field($_POST['post_end_date']) : $post_date;

    update_post_meta($post_id, '_post_start_date', $start_date);
    update_post_meta($post_id, '_post_end_date', $end_date);
}
add_action('save_post', 'save_date_range_meta_box');

// Calendar Widget Class
class Date_Range_Calendar_Widget extends WP_Widget {
    public function __construct() {
        parent::__construct(
            'date_range_calendar',
            'Date Range Calendar',
            array('description' => 'Shows a calendar with linked dates for posts')
        );
    }

    public function widget($args, $instance) {
        echo $args['before_widget'];
        echo $args['before_title'] . 'Post Calendar' . $args['after_title'];
        echo '<div id="ajax-calendar-container">';
        $this->display_calendar();
        echo '</div>';
        echo $args['after_widget'];
    }

    public function display_calendar() {
        $current_month = isset($_POST['month']) ? intval($_POST['month']) : current_time('n');
        $current_year = isset($_POST['year']) ? intval($_POST['year']) : current_time('Y');
        
        // Add notice if viewing archive
        $archive_notice = '';
        if (isset($_GET['date'])) {
            $date = sanitize_text_field($_GET['date']);
            $archive_notice = '<div class="calendar-archive-notice">' . 
                'Viewing posts from: ' . date('F j, Y', strtotime($date)) .
                '</div>';
        }
        
        $calendar = '<div class="post-calendar">';
        $calendar .= $archive_notice;
        $calendar .= $this->generate_calendar_selectors($current_month, $current_year);
        $calendar .= $this->generate_calendar_nav($current_month, $current_year);
        $calendar .= $this->generate_calendar_grid($current_month, $current_year);
        $calendar .= '</div>';
        
        echo $calendar;
    }

    private function generate_calendar_selectors($current_month, $current_year) {
        // Generate month selector
        $months = array(
            1 => 'January', 2 => 'February', 3 => 'March',
            4 => 'April', 5 => 'May', 6 => 'June',
            7 => 'July', 8 => 'August', 9 => 'September',
            10 => 'October', 11 => 'November', 12 => 'December'
        );
        
        $month_selector = '<select id="calendar-month" class="calendar-selector">';
        foreach ($months as $num => $name) {
            $selected = $current_month == $num ? 'selected' : '';
            $month_selector .= sprintf(
                '<option value="%d" %s>%s</option>',
                $num,
                $selected,
                $name
            );
        }
        $month_selector .= '</select>';
        
        // Generate year selector (last 5 years to next 5 years)
        $current_year_num = intval(current_time('Y'));
        $year_selector = '<select id="calendar-year" class="calendar-selector">';
        for ($year = $current_year_num - 5; $year <= $current_year_num + 5; $year++) {
            $selected = $current_year == $year ? 'selected' : '';
            $year_selector .= sprintf(
                '<option value="%d" %s>%s</option>',
                $year,
                $selected,
                $year
            );
        }
        $year_selector .= '</select>';
        
        return '<div class="calendar-selectors">' . $month_selector . $year_selector . '</div>';
    }

    private function generate_calendar_nav($month, $year) {
        $prev_month = $month - 1;
        $next_month = $month + 1;
        $prev_year = $next_year = $year;
        
        if ($prev_month == 0) {
            $prev_month = 12;
            $prev_year--;
        }
        if ($next_month == 13) {
            $next_month = 1;
            $next_year++;
        }

        $nav = '<div class="calendar-nav">';
        $nav .= sprintf(
            '<a href="#" class="calendar-nav-link" data-month="%d" data-year="%d">&laquo; Prev</a>',
            $prev_month,
            $prev_year
        );
        $nav .= '<span>' . date('F Y', mktime(0, 0, 0, $month, 1, $year)) . '</span>';
        $nav .= sprintf(
            '<a href="#" class="calendar-nav-link" data-month="%d" data-year="%d">Next &raquo;</a>',
            $next_month,
            $next_year
        );
        $nav .= '</div>';
        
        return $nav;
    }

    private function generate_calendar_grid($month, $year) {
        $first_day = mktime(0, 0, 0, $month, 1, $year);
        $days_in_month = date('t', $first_day);
        $day_of_week = date('w', $first_day);
        
        $posts_in_range = $this->get_posts_for_month($month, $year);
        
        $calendar = '<table class="calendar-table">';
        $calendar .= '<tr><th>Sun</th><th>Mon</th><th>Tue</th><th>Wed</th><th>Thu</th><th>Fri</th><th>Sat</th></tr>';
        $calendar .= '<tr>';
        
        for ($i = 0; $i < $day_of_week; $i++) {
            $calendar .= '<td></td>';
        }
        
        for ($day = 1; $day <= $days_in_month; $day++) {
            if (($day + $day_of_week - 1) % 7 == 0 && $day != 1) {
                $calendar .= '</tr><tr>';
            }
            
            $current_date = sprintf('%04d-%02d-%02d', $year, $month, $day);
            $has_posts = isset($posts_in_range[$current_date]);
            
            if ($has_posts) {
                $archive_url = add_query_arg('date', $current_date, home_url('/'));
                $calendar .= '<td class="has-posts"><a href="' . esc_url($archive_url) . '">' . $day . '</a></td>';
            } else {
                $calendar .= '<td>' . $day . '</td>';
            }
        }
        
        while (($day + $day_of_week - 1) % 7 != 0) {
            $calendar .= '<td></td>';
            $day++;
        }
        
        $calendar .= '</tr></table>';
        
        return $calendar;
    }

    private function get_posts_for_month($month, $year) {
        global $wpdb;
        
        $start_date = sprintf('%04d-%02d-01', $year, $month);
        $end_date = sprintf('%04d-%02d-%02d', $year, $month, date('t', strtotime($start_date)));
        
        $query = $wpdb->prepare(
            "SELECT DISTINCT p.ID, 
                COALESCE(pm1.meta_value, DATE(p.post_date)) as start_date,
                COALESCE(pm2.meta_value, DATE(p.post_date)) as end_date
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} pm1 ON p.ID = pm1.post_id AND pm1.meta_key = '_post_start_date'
            LEFT JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_post_end_date'
            WHERE p.post_type = 'post'
            AND p.post_status = 'publish'
            AND (
                (COALESCE(pm1.meta_value, DATE(p.post_date)) <= %s AND COALESCE(pm2.meta_value, DATE(p.post_date)) >= %s)
                OR (COALESCE(pm1.meta_value, DATE(p.post_date)) BETWEEN %s AND %s)
                OR (DATE(p.post_date) BETWEEN %s AND %s)
            )",
            $end_date, $start_date, $start_date, $end_date, $start_date, $end_date
        );
        
        $results = $wpdb->get_results($query);
        
        $posts_by_date = array();
        foreach ($results as $post) {
            $start = max($start_date, $post->start_date);
            $end = min($end_date, $post->end_date);
            
            $current = strtotime($start);
            while ($current <= strtotime($end)) {
                $date = date('Y-m-d', $current);
                if (!isset($posts_by_date[$date])) {
                    $posts_by_date[$date] = array();
                }
                $posts_by_date[$date][] = $post->ID;
                $current = strtotime('+1 day', $current);
            }
        }
        
        return $posts_by_date;
    }
}

// Register Calendar Widget
function register_date_range_calendar_widget() {
    register_widget('Date_Range_Calendar_Widget');
}
add_action('widgets_init', 'register_date_range_calendar_widget');

// AJAX Handler
function ajax_get_calendar() {
    check_ajax_referer('calendar_nonce', 'nonce');
    
    $widget = new Date_Range_Calendar_Widget();
    $widget->display_calendar();
    wp_die();
}
add_action('wp_ajax_get_calendar', 'ajax_get_calendar');
add_action('wp_ajax_nopriv_get_calendar', 'ajax_get_calendar');

// Enqueue Scripts
function enqueue_calendar_scripts() {
    wp_enqueue_script(
        'calendar-ajax',
        plugins_url('js/calendar.js', __FILE__),
        array('jquery'),
        '1.0',
        true
    );

    wp_localize_script('calendar-ajax', 'calendarAjax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('calendar_nonce')
    ));
}
add_action('wp_enqueue_scripts', 'enqueue_calendar_scripts');

// Modify Query for Date Parameter
function modify_posts_for_date($query) {
    if (!is_admin() && $query->is_main_query() && isset($_GET['date'])) {
        $clicked_date = sanitize_text_field($_GET['date']);
        
        // Find all periods that contain this date
        global $wpdb;
        $periods = $wpdb->get_results($wpdb->prepare(
            "SELECT p.ID, p.post_title,
                COALESCE(pm1.meta_value, DATE(p.post_date)) as start_date,
                COALESCE(pm2.meta_value, DATE(p.post_date)) as end_date
            FROM {$wpdb->posts} p
            LEFT JOIN {$wpdb->postmeta} pm1 ON p.ID = pm1.post_id AND pm1.meta_key = '_post_start_date'
            LEFT JOIN {$wpdb->postmeta} pm2 ON p.ID = pm2.post_id AND pm2.meta_key = '_post_end_date'
            WHERE p.post_type = 'post'
            AND p.post_status = 'publish'
            AND (
                (COALESCE(pm1.meta_value, DATE(p.post_date)) <= %s AND COALESCE(pm2.meta_value, DATE(p.post_date)) >= %s)
            )",
            $clicked_date,
            $clicked_date
        ));

        if (!empty($periods)) {
            // Get all post IDs that contain this date
            $post_ids = wp_list_pluck($periods, 'ID');
            
            // Get the date range for display
            $date_ranges = array();
            foreach ($periods as $period) {
                $start = date('F j, Y', strtotime($period->start_date));
                $end = date('F j, Y', strtotime($period->end_date));
                if ($start !== $end) {
                    $date_ranges[] = "$start to $end";
                } else {
                    $date_ranges[] = $start;
                }
            }

            // Add notice about the date range
            add_action('wp_footer', function() use ($date_ranges) {
                ?>
                <script>
                jQuery(document).ready(function($) {
                    // Add notice at the top of the content area
                    var notice = '<div class="calendar-period-notice">' +
                        '<strong>Showing posts from these periods:</strong><br>' +
                        '<?php echo implode("<br>", array_unique($date_ranges)); ?>' +
                        '</div>';
                    $('.content-area').prepend(notice);

                    // Add styles if they don't exist
                    if (!$('#calendar-period-notice-styles').length) {
                        $('head').append(`
                            <style id="calendar-period-notice-styles">
                                .calendar-period-notice {
                                    background: #f0f8ff;
                                    padding: 15px;
                                    margin-bottom: 20px;
                                    border-left: 4px solid #0073aa;
                                    border-radius: 4px;
                                }
                            </style>
                        `);
                    }
                });
                </script>
                <?php
            });

            // Update the query to show all posts from the period(s)
            $query->set('post__in', $post_ids);
            $query->set('orderby', 'date');
            $query->set('order', 'DESC');
            
            // Remove the date query since we're using post IDs
            $query->set('date_query', array());
            $query->set('meta_query', array());
        }
        
        // Set calendar display to match clicked date
        add_action('wp_footer', function() use ($clicked_date) {
            ?>
            <script>
            jQuery(document).ready(function($) {
                var archiveDate = new Date('<?php echo $clicked_date; ?>');
                var month = archiveDate.getMonth() + 1;
                var year = archiveDate.getFullYear();
                if (typeof updateCalendar === 'function') {
                    updateCalendar(month, year, false);
                }
            });
            </script>
            <?php
        });
    }
}
add_action('pre_get_posts', 'modify_posts_for_date');


function add_calendar_styles() {
    ?>
    <style>
	   .post-calendar {
            max-width: 100%;
            margin: 0 auto;
        }
        .calendar-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
            padding: 10px;
            background: #f5f5f5;
            border-radius: 4px;
        }
        .calendar-nav a {
            text-decoration: none;
            padding: 5px 10px;
            background: #fff;
            border: 1px solid #ddd;
            border-radius: 3px;
            cursor: pointer;
        }
        .calendar-nav a:hover {
            background: #f0f0f0;
        }
        .calendar-table {
            width: 100%;
            border-collapse: collapse;
            background: #fff;
        }
        .calendar-table th,
        .calendar-table td {
            padding: 8px;
            text-align: center;
            border: 1px solid #ddd;
        }
        .calendar-table th {
            background-color: #f5f5f5;
            font-weight: bold;
        }
        .calendar-table td.has-posts {
            background-color: #e6f3ff;
        }
        .calendar-table td.has-posts a {
            color: #0073aa;
            text-decoration: none;
            display: block;
            border-radius: 3px;
        }
        .calendar-table td.has-posts a:hover {
            background-color: #0073aa;
            color: #fff;
        }
        #ajax-calendar-container {
            position: relative;
            min-height: 200px;
        }
        #ajax-calendar-container.loading::after {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.7);
            z-index: 1;
        }
        #ajax-calendar-container.loading::before {
            content: "Loading...";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #fff;
            padding: 10px 20px;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            z-index: 2;
        }
           .calendar-selectors {
            display: flex;
            gap: 10px;
            margin-bottom: 10px;
        }
        
        .calendar-selector {
            padding: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
            background: #fff;
            min-width: 100px;
        }
        
        .calendar-archive-notice {
            background: #f0f8ff;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
            border-left: 4px solid #0073aa;
        }
        
        /* Make the calendar navigation more compact with selectors */
        .calendar-nav {
            margin-top: 10px;
        }
        
        /* Optimize loading state */
        #ajax-calendar-container.loading {
            opacity: 0.6;
            transition: opacity 0.2s;
        }
        
        #ajax-calendar-container.loading::before {
            content: "Loading...";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            background: #fff;
            padding: 8px 16px;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            z-index: 2;
        }
        
        /* Add some hover effects for better UX */
        .calendar-selector:hover {
            border-color: #0073aa;
        }
        
        .calendar-table td.has-posts a {
            transition: all 0.2s ease;
        }
        
        /* Add responsive styles */
        @media (max-width: 480px) {
            .calendar-selectors {
                flex-direction: column;
            }
            
            .calendar-selector {
                width: 100%;
            }
        }

        .calendar-period-notice {
            background: #f0f8ff;
            padding: 15px;
            margin-bottom: 20px;
            border-left: 4px solid #0073aa;
            border-radius: 4px;
        }
    </style>
    <?php
}
add_action('wp_head', 'add_calendar_styles');