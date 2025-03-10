<?php
/**
 * Plugin Name: WPS Image Optimalization
 * Plugin URI: 
 * Description: Optimizes images when uploading. Define the maximum image size and choose which file types should be converted to webp and what the compression or quality of the optimized image should be.
 * Author: WPS
 * Author URI: https://wps.sk
 * Version: 1.1.0
 * License: GPL v3
 */

// Add menu item to Settings
add_action('admin_menu', 'wpsImageOptimalizationMenu');

function wpsImageOptimalizationMenu()
{
    add_options_page(
        'Wps Image Optimalization Settings',
        'Wps Image Optimalization',
        'manage_options',
        'wps-image-optimalization',
        'wpsImageOptimalizationSettingsPage'
    );
}

// Enqueue admin styles
add_action('admin_enqueue_scripts', 'wpsImageOptimalizationEnqueueAdminStyles');

function wpsImageOptimalizationEnqueueAdminStyles($hookSuffix)
{
    if ($hookSuffix == 'settings_page_wps-image-optimalization') {
        wp_enqueue_style('wps-image-optimalization-admin', plugin_dir_url(__FILE__) . 'wps-image-optimalization-admin.css');
    }
}

// Register settings
add_action('admin_init', 'wpsImageOptimalizationSettingsInit');

function wpsImageOptimalizationSettingsInit()
{
    register_setting('wps_image_optimalization_settings', 'wps_image_optimalization_settings');

    add_settings_section(
        'wps_image_optimalization_main_settings',
        __('Settings', 'wps-image-optimalization'),
        'wpsImageOptimalizationSectionCallback',
        'wps_image_optimalization_settings'
    );

    add_settings_field(
        'retain_original',
        __('Also keep the original image', 'wps-image-optimalization'),
        'wpsImageOptimalizationRetainOriginalRender',
        'wps_image_optimalization_settings',
        'wps_image_optimalization_main_settings'
    );

    add_settings_field(
        'quality',
        __('Image Quality', 'wps-image-optimalization'),
        'wpsImageOptimalizationQualityRender',
        'wps_image_optimalization_settings',
        'wps_image_optimalization_main_settings'
    );

    add_settings_field(
        'method',
        __('Compression Method', 'wps-image-optimalization'),
        'wpsImageOptimalizationMethodRender',
        'wps_image_optimalization_settings',
        'wps_image_optimalization_main_settings'
    );

    add_settings_field(
        'allowed_types',
        __('Allowed Image Types', 'wps-image-optimalization'),
        'wpsImageOptimalizationAllowedTypesRender',
        'wps_image_optimalization_settings',
        'wps_image_optimalization_main_settings'
    );

    add_settings_field(
        'set_alt_text',
        __('Copy file name to alt text', 'wps-image-optimalization'),
        'wpsImageOptimalizationSetAltTextRender',
        'wps_image_optimalization_settings',
        'wps_image_optimalization_main_settings'
    );

    add_settings_field(
        'max_width',
        __('Maximum Image Width', 'wps-image-optimalization'),
        'wpsImageOptimalizationMaxWidthRender',
        'wps_image_optimalization_settings',
        'wps_image_optimalization_main_settings'
    );
}

function wpsImageOptimalizationSectionCallback()
{
    echo '<p>' . __('Optimizes images when uploading. Define the maximum image size and choose which file types should be converted to webp and what the compression or quality of the optimized image should be.', 'wps-image-optimalization') . '</p>';
}

function wpsImageOptimalizationRetainOriginalRender()
{
    $options = get_option('wps_image_optimalization_settings');
    ?>
    <label for="retain_original">
        <input type='checkbox' name='wps_image_optimalization_settings[retain_original]' <?php checked(isset($options['retain_original'])); ?> value='1'>
        <?php _e('Check if you want to keep the original file without optimization.', 'wps-image-optimalization'); ?>
    </label>
    <p class="description"><?php _e('If unchecked, the original image will be deleted after successful conversion to WebP, saving disk space.', 'wps-image-optimalization'); ?></p>
    <?php
}

function wpsImageOptimalizationQualityRender()
{
    $options = get_option('wps_image_optimalization_settings');
    $quality = isset($options['quality']) ? intval($options['quality']) : 80;
    ?>
    <label for="quality">
        <input type='number' name='wps_image_optimalization_settings[quality]' value='<?php echo esc_attr($quality); ?>' min='0' max='100' step='1'>
        <?php _e('Image quality after optimization (0-100). Higher quality takes up more storage. (0 - lowest, 100 - highest quality)', 'wps-image-optimalization'); ?>
    </label>
    <p class="description"><?php _e('The default value is 80.', 'wps-image-optimalization'); ?></p>
    <?php
}

function wpsImageOptimalizationMethodRender()
{
    $options = get_option('wps_image_optimalization_settings');
    $method = isset($options['method']) ? intval($options['method']) : 6;
    ?>
    <label for="method">
        <input type='number' name='wps_image_optimalization_settings[method]' value='<?php echo esc_attr($method); ?>' min='0' max='6' step='1'>
        <?php _e('Image optimization (0-6).', 'wps-image-optimalization'); ?>
    </label>
    <p class="description"><?php _e('Higher value = greater image compression, which also means longer processing time during optimization.', 'wps-image-optimalization'); ?></p>
    <?php
}

function wpsImageOptimalizationAllowedTypesRender()
{
    $options = get_option('wps_image_optimalization_settings');
    $allowed_types = isset($options['allowed_types']) ? $options['allowed_types'] : ['image/jpeg', 'image/png', 'image/gif'];
    $all_types = ['image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image/tiff', 'image/svg+xml'];
    ?>
    <p><?php _e('Images to be optimized:', 'wps-image-optimalization'); ?></p>
    <?php
    foreach ($all_types as $type) {
        ?>
        <label for="allowed_types">
            <input type='checkbox' name='wps_image_optimalization_settings[allowed_types][]' <?php checked(in_array($type, $allowed_types)); ?> value='<?php echo esc_attr($type); ?>'>
            <?php echo esc_html($type); ?>
        </label><br>
        <?php
    }
    ?>
    <p class="description"><?php _e('The default images are JPEG, PNG, and GIF. Select other types if necessary. SVG files usually take up little space and do not need to be optimized.', 'wps-image-optimalization'); ?></p>
    <?php
}

function wpsImageOptimalizationSetAltTextRender()
{
    $options = get_option('wps_image_optimalization_settings');
    ?>
    <label for="set_alt_text">
        <input type='checkbox' name='wps_image_optimalization_settings[set_alt_text]' <?php checked(isset($options['set_alt_text'])); ?> value='1'>
        <?php _e('Check to automatically set image alt text based on the filename.', 'wps-image-optimalization'); ?>
    </label>
    <p class="description"><?php _e('If you have SEO-friendly image titles, you can enable this option. Otherwise, leave the feature disabled.', 'wps-image-optimalization'); ?></p>
    <?php
}

function wpsImageOptimalizationMaxWidthRender()
{
    $options = get_option('wps_image_optimalization_settings');
    $max_width = isset($options['max_width']) ? intval($options['max_width']) : 1200;
    ?>
    <label for="max_width">
        <input type='number' name='wps_image_optimalization_settings[max_width]' value='<?php echo esc_attr($max_width); ?>' min='0' step='1'>
        <?php _e('Set the maximum width for uploaded images (in pixels).', 'wps-image-optimalization'); ?>
    </label>
    <p class="description"><?php _e('Images wider than this value will be resized before further optimization. The default value is 1200 pixels.', 'wps-image-optimalization'); ?></p>
    <?php
}

function wpsImageOptimalizationSettingsPage()
{
    ?>
    <div class="wrap">
        <h1><?php _e('Wps Image Optimalization Settings', 'wps-image-optimalization'); ?></h1>
        <form action='options.php' method='post'>
            <?php
            settings_fields('wps_image_optimalization_settings');
            do_settings_sections('wps_image_optimalization_settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Disable WordPress default image sizes and back-sizing
function disableDefaultImageSizes($sizes)
{
    unset($sizes['thumbnail']);      // Remove Thumbnail size
    unset($sizes['medium']);         // Remove Medium size
    unset($sizes['medium_large']);   // Remove Medium Large size
    unset($sizes['large']);          // Remove Large size
    // Note: 'full' represents the original upload size and cannot be removed here.
    return $sizes;
}
add_filter('intermediate_image_sizes_advanced', 'disableDefaultImageSizes');

function disableAdditionalImageSizes()
{
    remove_image_size('1536x1536');  // Remove 2x medium-large size
    remove_image_size('2048x2048');  // Remove 2x large size
}
add_action('init', 'disableAdditionalImageSizes');

add_filter('big_image_size_threshold', '__return_false'); // Disable big image scaling

if (!isset($content_width)) {
    $content_width = 1920; // Set max content width to prevent large image generation
}

// Hook into the image upload process to convert images to WebP
add_filter('wp_handle_upload', 'wpsImageOptimalizationHandleUpload');

function wpsImageOptimalizationHandleUpload($upload)
{
    $options = get_option('wps_image_optimalization_settings');
    $retain_original = isset($options['retain_original']) ? $options['retain_original'] : false;
    $quality = isset($options['quality']) ? intval($options['quality']) : 80;
    $method = isset($options['method']) ? intval($options['method']) : 6;
    $max_width = isset($options['max_width']) ? intval($options['max_width']) : 1200;

    // Define allowed image types
    $allowed_types = isset($options['allowed_types']) && !empty($options['allowed_types']) ? $options['allowed_types'] : ['image/jpeg', 'image/png', 'image/gif'];

    if (in_array($upload['type'], $allowed_types, true)) {
        $file_path = $upload['file'];
        $file_info = pathinfo($file_path);

        // Only convert the original full-size image
        if (strpos($file_path, '-scaled') === false && !preg_match('/-\d+x\d+\./', $file_path)) {

            // Resize image if it exceeds the maximum width
            $image_editor = wp_get_image_editor($file_path);
            if (!is_wp_error($image_editor)) {
                $image_size = $image_editor->get_size();
                if ($image_size['width'] > $max_width) {
                    $image_editor->resize($max_width, null);
                    $image_editor->save($file_path);
                }
            }

            // Check if ImageMagick or GD is available
            if (extension_loaded('imagick')) {
                $image = new Imagick($file_path);

                // Set WebP compression quality and method
                $image->setImageFormat('webp');
                $image->setOption('webp:method', $method);
                $image->setImageCompressionQuality($quality);

                $image->stripImage();

                $new_file_path = $file_info['dirname'] . '/' . wp_unique_filename($file_info['dirname'], $file_info['filename'] . '.webp');

                $image->writeImage($new_file_path);
                $image->clear();
                $image->destroy();
            } elseif (extension_loaded('gd')) {
                $image_editor = wp_get_image_editor($file_path);
                if (!is_wp_error($image_editor)) {
                    $new_file_path = $file_info['dirname'] . '/' . wp_unique_filename($file_info['dirname'], $file_info['filename'] . '.webp');

                    $saved_image = $image_editor->save($new_file_path, 'image/webp', array('quality' => $quality));
                }
            } else {
                error_log("No suitable image library (ImageMagick or GD) found for WebP optimalization.");
                return $upload;
            }

            if (isset($new_file_path) && file_exists($new_file_path)) {
                $upload['file'] = $new_file_path;
                $upload['url'] = str_replace(basename($upload['url']), basename($new_file_path), $upload['url']);
                $upload['type'] = 'image/webp';

                // If retaining original, register it with the media library
                if ($retain_original) {
                    $attachment = array(
                        'guid' => $upload['url'],
                        'post_mime_type' => $upload['type'],
                        'post_title' => preg_replace('/\.[^.]+$/', '', basename($file_path)),
                        'post_content' => '',
                        'post_status' => 'inherit',
                    );
                    wp_insert_attachment($attachment, $file_path);
                } elseif (file_exists($file_path)) {
                    @unlink($file_path); // Delete the original image if not retained
                }
            } else {
                error_log("Image optimalization failed for: " . $file_path);
            }
        }
    }

    return $upload;
}

// Hook into the image upload process to set alt text
add_action('add_attachment', 'wpsImageOptimalizationSetImageAltTextOnUpload');

function wpsImageOptimalizationSetImageAltTextOnUpload($postId)
{
    // Get the plugin settings
    $options = get_option('wps_image_optimalization_settings');
    $set_alt_text = isset($options['set_alt_text']) ? $options['set_alt_text'] : false;

    // Check if the setting to automatically set alt text is enabled
    if ($set_alt_text) {
        // Get the attachment post
        $attachment = get_post($postId);

        // Ensure it's an image
        if (wp_attachment_is_image($postId)) {
            // Get the attachment's title
            $title = $attachment->post_title;

            // Replace hyphens with spaces
            $title = str_replace('-', ' ', $title);

            // Convert to sentence case
            $alt_text = ucfirst(strtolower($title));

            // Update the attachment post meta with the new alt text
            update_post_meta($postId, '_wp_attachment_image_alt', $alt_text);
        }
    }
}
?>
