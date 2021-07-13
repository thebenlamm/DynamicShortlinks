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

// Register API endpoint fields
add_action( 'admin_init', 'dysl_register_settings_func' );
function dysl_register_settings_func() {
    register_setting( 'dysl_setup_config', DYSL_ENDPOINT_OPTION_NAME, array('sanitize_callback' => 'sanitize_text_field') );

    add_settings_section( 'dysl_setup_config_section', 'Dynamic Shortlinks Configuration', 'dysl_setup_config_section_func', 'dysl_setup_config_page' );
    add_settings_field( 'api_endpoint_field', 'API Endpoint', 'dysl_api_endpoint_field_func', 'dysl_setup_config_page', 'dysl_setup_config_section' );
}

function dysl_setup_config_section_func() {
    echo '<p>Please enter the full api endpoint below.</p>';
}

function dysl_api_endpoint_field_func() {
    $option = get_option( DYSL_ENDPOINT_OPTION_NAME );
    $value = $option ? esc_attr($option) : '';
    echo "<input id='dysl_api_endpoint_field' name='" . DYSL_ENDPOINT_OPTION_NAME . "' type='text' value='" . $value . "' style='width: 80%'/>";
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
            if(get_option(DYSL_ENDPOINT_OPTION_NAME)) submit_button('Refresh');
        ?>
    </form>
    <hr>
    <form action="options.php" method="post">
        <?php 
        settings_fields( 'dysl_setup_config' );
        do_settings_sections( 'dysl_setup_config_page' ); ?>
        <input name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e( 'Save' ); ?>" />
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

// Refresh options data
function dysl_fetch_options_data_func(){
    $endpoint = get_option(DYSL_ENDPOINT_OPTION_NAME);
    if($endpoint){
        $response = wp_remote_get($endpoint);
        $body     = wp_remote_retrieve_body( $response );
        $options = dysl_response_body_parser_func($body);
        $option_added = add_option( DYSL_SHORTLINKS_OPTION_NAME, $options );
        if(!$option_added){
            update_option(DYSL_SHORTLINKS_OPTION_NAME, $options);
        }
    }
}

// Code to parse response body from options refresh
function dysl_response_body_parser_func($body){
    $decoded = json_decode($body, true);

    function sum($arr, $item){
        $name = str_replace(' ', '_', $item["fields"]["Name"]);
        $price = $item["fields"]["Price"];
        $arr[$name] = $price;
        return $arr;
    }

    return array_reduce($decoded["records"], "sum");
}