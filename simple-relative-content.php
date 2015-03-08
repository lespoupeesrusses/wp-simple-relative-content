<?php
/*
Plugin Name: Simple Relative Content
Plugin URI: http://www.semiodesign.com/wordpress/simple-relative-content/
Description: Make all attachments urls stored in bdd relative to the WP_SITEURL url
Author: Pierre-Andr&eacute; Boissinot
Version: 1.0
*/

// Localization
load_plugin_textdomain('simple_relative_content', null, basename(dirname(__FILE__)) . '/languages/');


// We use the const WP_SITEURL everywhere, so we make sure it means something for WP
function src_define_siteurl_if_not_exists()
{
    if (!defined('WP_SITEURL')) {
        define('WP_SITEURL', get_option('siteurl'));
    }
}
add_action('init', 'src_define_siteurl_if_not_exists');


// To correct the database we use a recall of the page passing a parameter called 'src-correct'
function src_correct_db_if_wanted()
{
    global $displayDbCorrectionMessage;
    if (isset($_GET['src-correct'])) {
        global $wpdb;
        $oldSiteUrl = src_get_siturlvalue_directly_indb();
        // update the post table
        $query_posts = $wpdb->prepare('UPDATE ' . $wpdb->posts . ' SET guid = REPLACE (guid, %s, %s)', $oldSiteUrl, WP_SITEURL);
        $wpdb->query($query_posts);
        // update the options
        $query_options = $wpdb->prepare('UPDATE ' . $wpdb->options . ' SET option_value = REPLACE (option_value, %s, %s)', $oldSiteUrl, WP_SITEURL);
        $wpdb->query($query_options);
        
        $displayDbCorrectionMessage = true;
    }
}
add_action('init', 'src_correct_db_if_wanted');


// If a db correction has just been made display a message
function src_display_confirm_message()
{
    global $displayDbCorrectionMessage;
    if (isset($displayDbCorrectionMessage) && $displayDbCorrectionMessage == true) {
        ?>
        <div id="message" class="updated fade">
            <p>
                <strong><?php _e('Your database has been fixed', 'simple_relative_content'); ?></strong>
            </p>
        </div>
        <?php
    }
}
add_action('admin_notices', 'src_display_confirm_message');


// If there is a mismatch between the WP_SITEURL const and the entry in db displays a warning
function src_display_message_if_dburls_are_incorrect()
{
    if (WP_SITEURL != src_get_siturlvalue_directly_indb()) {
        if (current_user_can('manage_options')) {
            ?>
            <div id="message" class="error">
                <p>
                    <strong><?php _e('Some entries in your database seems to point on another location.', 'simple_relative_content'); ?></strong>
                    <br />
                    <strong>
                        <a href="?src-correct=true"><?php _e('Click here', 'simple_relative_content'); ?></a>
                        <?php _e('to correct it', 'simple_relative_content'); ?>
                    </strong>
                </p>
            </div>
            <?php
        }
        
    }
}
add_action('admin_notices', 'src_display_message_if_dburls_are_incorrect');


// When saving post, filter the content to remove the absolute references
function src_filter_post_content($data) 
{  
    $search = WP_SITEURL;
    if (substr($search, -1, 1) != '/') {
        $search .= '/';
    }
    
    $data["post_content"] = str_replace($search, '', $data["post_content"]);
    return $data;  
}  
add_filter('wp_insert_post_data','src_filter_post_content'); 


// Images are now written with a relative path in the db, so we change the WP editor to use a basepath in order to display the images correctly
function src_change_mce_path_options($init) 
{
     $basePath = WP_SITEURL;
    if (substr($basePath, -1, 1) != '/') {
        $basePath .= '/';
    }
    $init['relative_urls'] = true;
    $init['document_base_url'] = $basePath;
    return $init;
}
add_filter('tiny_mce_before_init', 'src_change_mce_path_options');


// Images are now written with a relative path in the db, so we add a "base" tag on the public header
function src_print_base() 
{
    $basePath = WP_SITEURL;
    if (substr($basePath, -1, 1) != '/') {
        $basePath .= '/';
    }
?>
    <base href="<?php echo $basePath; ?>"></base>
<?php
}
add_action("wp_head","src_print_base");


function src_get_siturlvalue_directly_indb()
{
    global $wpdb;
    $site_url = $wpdb->get_var($wpdb->prepare("SELECT option_value FROM $wpdb->options WHERE option_name='siteurl'"));
    return $site_url;
        
    
}











/*

function src_get_relative_attachment_path($path)
{
	$path = str_replace(WP_SITEURL, '', $path);
    return $path;
}

function src_wp_handle_upload($info)
{
    $info['url'] = src_get_relative_attachment_path($info['url']);
    return $info;
}
//add_filter('wp_handle_upload', 'src_wp_handle_upload');

function src_wp_get_attachment_url($url)
{
    return src_get_relative_attachment_path($url);
}
//add_filter('wp_get_attachment_url', 'src_wp_get_attachment_url');

function src_post_thumbnail_html($html) {
    $html = str_replace('wp-content/', WP_SITEURL . 'wp-content/', $html);
    return $html;
}
//add_filter('admin_post_thumbnail_html', 'src_post_thumbnail_html');

*/
