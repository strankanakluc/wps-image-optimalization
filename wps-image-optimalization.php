<?php
/**
 * Plugin Name: WPS Image Optimalization
 * Plugin URI: 
 * Description: Optimizes images when uploading. Define the maximum image size and choose which file types should be converted to webp and what the compression or quality of the optimized image should be.
 * Author: WPS
 * Author URI: https://wps.sk
 * Version: 1.1.0
 * License: GPL-2.0+
 * @category Plugin
 * @package  WPS_Image_Optimalization
 * @link     https://wps.sk
 * @php      7.4
 */

// Add menu item to Settings
add_action('admin_menu', 'Wps_Image_Optimalization_menu');

/**
 * Add menu item to Settings
 *
 * @return void
 */
function Wps_Image_Optimalization_menu()
{
    add_options_page(
        'Wps Image Optimalization Settings',
        'Wps Image Optimalization',
        'manage_options',
        'wps-image-optimalization',
        'Wps_Image_Optimalization_Settings_page'
    );
}

// Enqueue admin styles
add_action('admin_enqueue_scripts', 'Wps_Image_Optimalization_Enqueue_Admin_styles');

/**
 * Enqueue admin styles
 *
 * @param string $hookSuffix The suffix for the hook
 *
 * @return void
 */
function Wps_Image_Optimalization_Enqueue_Admin_styles($hookSuffix)
{
    if ($hookSuffix == 'settings_page_wps-image-optimalization') {
        wp_enqueue_style('wps-image-optimalization-admin', plugin_dir_url(__FILE__) . 'wps-image-optimalization-admin.css');
    }
}

// Register settings
add_action('admin_init', 'Wps_Image_Optimalization_Settings_init');

/**
 * Register settings
 *
 * @return void
 */
function Wps_Image_Optimalization_Settings_init()
{
    register_setting('wps_image_optimalization_settings', 'wps_image_optimalization_settings', 'wps_image_optimalization_sanitize_settings');

    add_settings_section(
        'wps_image_optimalization_main_settings',
        __('Settings', 'wps-image-optimalization'),
        'Wps_Image_Optimalization_Section_callback',
        'wps_image_optimalization_settings'
    );

    add_settings_field(
        'retain_original',
        __('Also keep the original image', 'wps-image-optimalization'),
        'Wps_Image_Optimalization_Retain_Original_render',
        'wps_image_optimalization_settings',
        'wps_image_optimalization_main_settings'
    );

    add_settings_field(
        'quality',
        __('Image Quality', 'wps-image-optimalization'),
        'Wps_Image_Optimalization_Quality_render',
        'wps_image_optimalization_settings',
        'wps_image_optimalization_main_settings'
    );

    add_settings_field(
        'method',
        __('Compression Method', 'wps-image-optimalization'),
        'Wps_Image_Optimalization_Method_render',
        'wps_image_optimalization_settings',
        'wps_image_optimalization_main_settings'
    );

    add_settings_field(
        'allowed_types',
        __('Allowed Image Types', 'wps-image-optimalization'),
        'Wps_Image_Optimalization_Allowed_Types_render',
        'wps_image_optimalization_settings',
        'wps_image_optimalization_main_settings'
    );

    add_settings_field(
        'set_alt_text',
        __('Copy file name to alt text', 'wps-image-optimalization'),
        'Wps_Image_Optimalization_Set_Alt_Text_render',
        'wps_image_optimalization_settings',
        'wps_image_optimalization_main_settings'
    );

    add_settings_field(
        'max_width',
        __('Maximum Image Width', 'wps-image-optimalization'),
        'Wps_Image_Optimalization_Max_Width_render',
        'wps_image_optimalization_settings',
        'wps_image_optimalization_main_settings'
    );
}

/**
 * Sanitize settings
 *
 * @param array $input The input to sanitize
 *
 * @return array The sanitized input
 */
function wps_image_optimalization_sanitize_settings($input)
{
    $sanitized = array();

    if (isset($input['retain_original'])) {
        $sanitized['retain_original'] = intval($input['retain_original']);
    }

    if (isset($input['quality'])) {
        $sanitized['quality'] = intval($input['quality']);
    }

    if (isset($input['method'])) {
        $sanitized['method'] = intval($input['method']);
    }

    if (isset($input['allowed_types']) && is_array($input['allowed_types'])) {
        $sanitized['allowed_types'] = array_map('sanitize_text_field', $input['allowed_types']);
    }

    if (isset($input['set_alt_text'])) {
        $sanitized['set_alt_text'] = intval($input['set_alt_text']);
    }

    if (isset($input['max_width'])) {
        $sanitized['max_width'] = intval($input['max_width']);
    }

    return $sanitized;
}

/**
 * Section callback
 *
 * @return void
 */
function Wps_Image_Optimalization_Section_callback()
{
    echo '<p>' . __('Optimizes images when uploading. Define the maximum image size and choose which file types should be converted to webp and what the compression or quality of the optimized image should be.', 'wps-image-optimalization') . '</p>';
}

/**
 * Render retain original setting
 *
 * @return void
 */
function Wps_Image_Optimalization_Retain_Original_render()
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

/**
 * Render quality setting
 *
 * @return void
 */
function Wps_Image_Optimalization_Quality_render()
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

/**
 * Render method setting
 *
 * @return void
 */
function Wps_Image_Optimalization_Method_render()
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

/**
 * Render allowed types setting
 *
 * @return void
 */
function Wps_Image_Optimalization_Allowed_Types_render()
{
    $options = get_option('wps_image_optimalization_settings');
    $allowedTypes = isset($options['allowed_types']) ? $options['allowed_types'] : ['image/jpeg', 'image/png', 'image/gif'];
    $allTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image_tiff', 'image/svg+xml'];
    ?>
    <p><?php _e('Images to be optimized:', 'wps-image-optimalization'); ?></p>
    <?php
    foreach ($allTypes as $type) {
        ?>
        <label for="allowed_types">
            <input type='checkbox' name='wps_image_optimalization_settings[allowed_types][]' <?php checked(in_array($type, $allowedTypes)); ?> value='<?php echo esc_attr($type); ?>'>
            <?php echo esc_html($type); ?>
        </label><br>
        <?php
    }
    ?>
    <p class="description"><?php _e('The default images are JPEG, PNG, and GIF. Select other types if necessary. SVG files usually take up little space and do not need to be optimized.', 'wps-image-optimalization'); ?></p>
    <?php
}

/**
 * Render set alt text setting
 *
 * @return void
 */
function Wps_Image_Optimalization_Set_Alt_Text_render()
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

/**
 * Render max width setting
 *
 * @return void
 */
function Wps_Image_Optimalization_Max_Width_render()
{
    $options = get_option('wps_image_optimalization_settings');
    $maxWidth = isset($options['max_width']) ? intval($options['max_width']) : 1200;
    ?>
    <label for="max_width">
        <input type='number' name='wps_image_optimalization_settings[max_width]' value='<?php echo esc_attr($maxWidth); ?>' min='0' step='1'>
        <?php _e('Set the maximum width for uploaded images (in pixels).', 'wps-image-optimalization'); ?>
    </label>
    <p class="description"><?php _e('Images wider than this value will be resized before further optimization. The default value is 1200 pixels.', 'wps-image-optimalization'); ?></p>
    <?php
}

/**
 * Settings page
 *
 * @return void
 */
function Wps_Image_Optimalization_Settings_page()
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

/**
 * Disable WordPress default image sizes and back-sizing
 *
 * @param array $sizes The sizes of the images
 *
 * @return array
 */
function Disable_Default_Image_sizes($sizes)
{
    unset($sizes['thumbnail']);      // Remove Thumbnail size
    unset($sizes['medium']);         // Remove Medium size
    unset($sizes['medium_large']);   // Remove Medium Large size
    unset($sizes['large']);          // Remove Large size
    // Note: 'full' represents the original upload size and cannot be removed here.
    return $sizes;
}
add_filter('intermediate_image_sizes_advanced', 'Disable_Default_Image_sizes');

/**
 * Disable additional image sizes
 *
 * @return void
 */
function Disable_Additional_Image_sizes()
{
    remove_image_size('1536x1536');  // Remove 2x medium-large size
    remove_image_size('2048x2048');  // Remove 2x large size
}
add_action('init', 'Disable_Additional_Image_sizes');

add_filter('big_image_size_threshold', '__return_false'); // Disable big image scaling

if (!isset($content_width)) {
    $content_width = 1920; // Set max content width to prevent large image generation
}

// Hook into the image upload process to convert images to WebP
add_filter('wp_handle_upload', 'Wps_Image_Optimalization_Handle_upload');

/**
 * Convert images to WebP upon upload
 *
 * @param array $upload The uploaded file
 *
 * @return array
 */
function Wps_Image_Optimalization_Handle_upload($upload)
{
    $options = get_option('wps_image_optimalization_settings');
    $retainOriginal = isset($options['retain_original']) ? $options['retain_original'] : false;
    $quality = isset($options['quality']) ? intval($options['quality']) : 80;
    $method = isset($options['method']) ? intval($options['method']) : 6;
    $maxWidth = isset($options['max_width']) ? intval($options['max_width']) : 1200;

    // Define allowed image types
    $allowedTypes = isset($options['allowed_types']) && !empty($options['allowed_types']) ? $options['allowed_types'] : ['image/jpeg', 'image/png', 'image/gif'];

    if (!in_array($upload['type'], $allowedTypes, true)) {
        return $upload;
    }

    $filePath = $upload['file'];
    $fileInfo = pathinfo($filePath);

    // Only convert the original full-size image
    if (strpos($filePath, '-scaled') !== false || preg_match('/-\d+x\d+\./', $filePath)) {
        return $upload;
    }

    // Resize image if it exceeds the maximum width
    $imageEditor = wp_get_image_editor($filePath);
    if (!is_wp_error($imageEditor)) {
        $imageSize = $imageEditor->get_size();
        if ($imageSize['width'] > $maxWidth) {
            $imageEditor->resize($maxWidth, null);
            $imageEditor->save($filePath);
        }
    }

    $newFilePath = $fileInfo['dirname'] . '/' . wp_unique_filename($fileInfo['dirname'], $fileInfo['filename'] . '.webp');

    // Check if ImageMagick is available
    if (extension_loaded('imagick')) {
        $image = new Imagick($filePath);

        // Set WebP compression quality and method
        $image->setImageFormat('webp');
        $image->setOption('webp:method', $method);
        $image->setImageCompressionQuality($quality);

        $image->stripImage();
        $image->writeImage($newFilePath);
        $image->clear();
        $image->destroy();
    } elseif (extension_loaded('gd')) {
        // Check if GD is available
        if (!is_wp_error($imageEditor)) {
            $imageEditor->save($newFilePath, 'image/webp', array('quality' => $quality));
        }
    } else {
        error_log("No suitable image library (ImageMagick or GD) found for WebP optimalization.");
        return $upload;
    }

    if (file_exists($newFilePath)) {
        $upload['file'] = $newFilePath;
        $upload['url'] = str_replace(basename($upload['url']), basename($newFilePath), $upload['url']);
        $upload['type'] = 'image/webp';

        // If retaining original, register it with the media library
        if ($retainOriginal) {
            $attachment = array(
                'guid' => $upload['url'],
                'post_mime_type' => $upload['type'],
                'post_title' => preg_replace('/\.[^.]+$/', '', basename($filePath)),
                'post_content' => '',
                'post_status' => 'inherit',
            );
            wp_insert_attachment($attachment, $filePath);
        } elseif (file_exists($filePath)) {
            @unlink($filePath); // Delete the original image if not retained
        }
    } else {
        error_log("Image optimalization failed for: " . $filePath);
    }

    return $upload;
}

// Hook into the image upload process to set alt text
add_action('add_attachment', 'Wps_Image_Optimalization_Set_Image_Alt_Text_On_upload');

/**
 * Set image alt text based on filename
 *
 * @param int $postId The post ID
 *
 * @return void
 */
function Wps_Image_Optimalization_Set_Image_Alt_Text_On_upload($postId)
{
    // Get the plugin settings
    $options = get_option('wps_image_optimalization_settings');
    $setAltText = isset($options['set_alt_text']) ? $options['set_alt_text'] : false;

    // Check if the setting to automatically set alt text is enabled
    if (!$setAltText) {
        return;
    }

    // Get the attachment post
    $attachment = get_post($postId);

    // Ensure it's an image
    if (!wp_attachment_is_image($postId)) {
        return;
    }

    // Get the attachment's title
    $title = $attachment->post_title;

    // Replace hyphens with spaces
    $title = str_replace('-', ' ', $title);

    // Convert to sentence case
    $altText = ucfirst(strtolower($title));

    // Update the attachment post meta with the new alt text
    update_post_meta($postId, '_wp_attachment_image_alt', $altText);
}
?>
