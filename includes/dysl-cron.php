<?php

/*
    cron deactivation
*/
register_deactivation_hook( __FILE__, 'dysl_cron_deactivate' );
function dysl_cron_deactivate() {
    wp_clear_scheduled_hook( 'dysl_cron_hook' );
}
/*
    cron activation
*/
add_action( 'dysl_cron_hook', 'dysl_fetch_options_data' );
if ( ! wp_next_scheduled( 'dysl_cron_hook' ) ) {
    wp_schedule_event( time(), 'daily', 'dysl_cron_hook' ); // minute
}

/*
    Add one minute option to cron schedule
*/
add_filter( 'cron_schedules', 'dysl_cron_add_minute' );
function dysl_cron_add_minute( $schedules ) {
    $schedules['minute'] = array(
        'interval' => 60,
        'display' => __( 'Once a minute' )
    );
    return $schedules;
}