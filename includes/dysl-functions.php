<?php
// Add page to Settings menu
add_action( 'admin_menu', 'dysl_add_settings_page_func' );
function dysl_add_settings_page_func() {
    add_options_page(
        DYSL_SETTINGS_PAGE_TITLE,
        DYSL_SETTINGS_MENU_LINK_NAME,
        'manage_options', // Capability requirement to see the link
        DYSL_SETTINGS_PAGE_SLUG,
        'dysl_render_settings_page_func' // render function
        );
}

function dysl_render_settings_page_func() {
    if (isset($_POST['refresh_options']) && check_admin_referer('refresh_options_nonce')) {
        dysl_fetch_options_data_func();
    }
    ?>
    <h3>Configured shortcodes</h3>
    <form action="options-general.php?page=<?php echo DYSL_SETTINGS_PAGE_SLUG?>" method="post">
        <?php wp_nonce_field('refresh_options_nonce'); ?>
        <input type="hidden" value="true" name="refresh_options" />
        <table class="form-table" role="presentation">
            <tbody>
                <?php 
                    $options = get_option(DYSL_SHORTLINKS_OPTION_NAME);
                    if($options){
                        foreach ($options as $key => $value) {
                ?>
                    <tr>
                        <th scope="row"><?php echo DYSL_SHORTCODE_PREFIX . $key ?></th>
                        <td><input type="text" value="<?php echo $value ?>" disabled></td>
                    </tr>
                <?php 
                        }
                    } else { 
                        echo "No shortcodes configured"; 
                    } ?>
            </tbody>
        </table>
        <?php 
            submit_button('Refresh');
        ?>
    </form>
    <?php
}

// Add shortcodes
add_action( 'init', 'dysl_add_shortcodes_func' );
function dysl_add_shortcodes_func() {
    $options = get_option(DYSL_SHORTLINKS_OPTION_NAME);
    if($options){
        foreach ($options as $key => $value) {
            add_shortcode( DYSL_SHORTCODE_PREFIX . $key, 'dysl_get_shortcode_value_func' );
        }
    }
}

function dysl_get_shortcode_value_func($atts, $content, $shortcode_tag){
    $options = get_option(DYSL_SHORTLINKS_OPTION_NAME);
    if($options){
        return htmlspecialchars($options[str_replace(DYSL_SHORTCODE_PREFIX, '', $shortcode_tag)]);
    }
    return '';
}

function dysl_get_body(){
    $property = str_replace("www.", "", str_replace(".com", "", parse_url( get_site_url(), PHP_URL_HOST )));
    $endpoint = "https://2bgkw8jl54.execute-api.us-east-1.amazonaws.com/v1/dynamic-shortlink-middleman?property=$property";
    $response = wp_remote_get($endpoint, array('headers' => array('x-api-key' => 'a0bTB4gOty7UPD0FNfUnL6M18hMg6SwK2RrXKLZD')));
    $body     = wp_remote_retrieve_body( $response );
    
    if( array_key_exists("message", json_decode($body, true)) ) {
        error_log("DYSL: Response body: " . $body);
        return false;
    }

    return $body;
}

// Refresh options data
function dysl_fetch_options_data_func(){
    try {
        $body = dysl_get_body();

        // if body is false try once more and then give up
        if(!$body){
            $body = dysl_get_body();
            if(!$body){
                error_log("DYSL: Giving up");
                return;
            }
        }

        $new_options = dysl_response_body_parser_func($body);

        if (!$new_options) return;
        
        $old_options = get_option(DYSL_SHORTLINKS_OPTION_NAME);
        if($new_options == $old_options) return;

        if (class_exists('\LiteSpeed\Purge')) {
            \LiteSpeed\Purge::purge_all();
            error_log("DYSL: Purged LiteSpeed cache");
        }
        $option_added = add_option( DYSL_SHORTLINKS_OPTION_NAME, $new_options );
        if(!$option_added){
            update_option(DYSL_SHORTLINKS_OPTION_NAME, $new_options);
        }
    } catch (Exception $ex) {
        error_log("DYSL: Error fetching options data: " . $ex->getMessage());
    }
}

// Code to parse response body from options refresh
function dysl_response_body_parser_func($body){
    try {
        $decoded = json_decode($body, true);
    
        function func($arr, $item){
            $key = str_replace(' ', '_', $item[0]);
            $value = $item[1];
            $arr[$key] = $value;
            return $arr;
        }

        return array_reduce($decoded, "func");
    } catch (Exception $ex) {
        echo $ex->getMessage();
        return false;
    }
}