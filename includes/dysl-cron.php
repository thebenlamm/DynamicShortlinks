<?php
define('CRON_HOOK', 'dysl_cron_hook');

/*
    cron deactivation
*/
register_deactivation_hook( __FILE__, 'dysl_cron_deactivate_func' );
function dysl_cron_deactivate_func() {
    wp_clear_scheduled_hook( CRON_HOOK );
}
/*
    cron activation
*/
add_action( CRON_HOOK, 'dysl_fetch_options_data_func' );
if ( ! wp_next_scheduled( CRON_HOOK ) ) {
    wp_schedule_event( time(), 'daily', CRON_HOOK );
}

/*
    Add one minute option to cron schedule
*/
/*  * /
add_filter( 'cron_schedules', 'dysl_cron_add_minute' );
function dysl_cron_add_minute( $schedules ) {
    $schedules['minute'] = array(
        'interval' => 60,
        'display' => __( 'Once a minute' )
    );
    return $schedules;
}
/*  */