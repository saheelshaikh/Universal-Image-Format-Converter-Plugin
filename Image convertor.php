<?php
/**
 * Plugin Name: Universal Image Format Converter
 * Description: Convert uploaded images to any supported format with configurable options
 * Version: 2.0.0
 * Author: Your Name
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

class UniversalImageConverter {
    
    // Configuration - Easy to modify these values
    private $config = [
        'enabled' => true,
        'source_formats' => ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/bmp'],
        'target_format' => 'webp',  // Change this to: webp, jpeg, png, gif
        'quality' => 85,
        'delete_original' => true,
        'mime_types' => [
            'webp' => 'image/webp',
            'jpeg' => 'image/jpeg', 
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
            'gif' => 'image/gif'
        ]
    ];
    
    public function __construct() {
        add_action('admin_menu', [$this, 'add_admin_menu']);
        add_action('admin_init', [$this, 'register_settings']);
        
        if ($this->get_option('enabled', true)) {
            add_filter('wp_handle_upload', [$this, 'convert_image_format']);
        }
    }
    
    /**
     * Add admin menu for plugin settings
     */
    public function add_admin_menu() {
        add_options_page(
            'Universal Image Converter Settings',
            'Image Converter', 
            'manage_options',
            'universal-image-converter',
            [$this, 'admin_page']
        );
    }
    
    /**
     * Register plugin settings
     */
    public function register_settings() {
        register_setting('uic_settings_group', 'uic_enabled');
        register_setting('uic_settings_group', 'uic_target_format');
        register_setting('uic_settings_group', 'uic_quality');
        register_setting('uic_settings_group', 'uic_delete_original');
        register_setting('uic_settings_group', 'uic_source_formats');
    }
    
    /**
     * Admin settings page
     */
    public function admin_page() {
        ?>
        <div class="wrap">
            <h1>Universal Image Converter Settings</h1>
            <form method="post" action="options.php">
                <?php settings_fields('uic_settings_group'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">Enable Converter</th>
                        <td>
                            <input type="checkbox" name="uic_enabled" value="1" 
                                   <?php checked($this->get_option('enabled')); ?> />
                            <p class="description">Enable automatic image format conversion on upload</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Target Format</th>
                        <td>
                            <select name="uic_target_format">
                                <option value="webp" <?php selected($this->get_option('target_format'), 'webp'); ?>>WebP</option>
                                <option value="jpeg" <?php selected($this->get_option('target_format'), 'jpeg'); ?>>JPEG</option>
                                <option value="png" <?php selected($this->get_option('target_format'), 'png'); ?>>PNG</option>
                                <option value="gif" <?php selected($this->get_option('target_format'), 'gif'); ?>>GIF</option>
                            </select>
                            <p class="description">Choose the target format for conversion</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Quality</th>
                        <td>
                            <input type="number" name="uic_quality" min="1" max="100" 
                                   value="<?php echo $this->get_option('quality'); ?>" />
                            <p class="description">Image quality (1-100, higher = better quality)</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Delete Original</th>
                        <td>
                            <input type="checkbox" name="uic_delete_original" value="1" 
                                   <?php checked($this->get_option('delete_original')); ?> />
                            <p class="description">Delete original file after conversion</p>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">Source Formats</th>
                        <td>
                            <?php 
                            $source_formats = $this->get_option('source_formats');
                            $available_formats = ['image/jpeg', 'image/png', 'image/gif', 'image/bmp', 'image/webp'];
                            foreach ($available_formats as $format): 
                            ?>
                                <label>
                                    <input type="checkbox" name="uic_source_formats[]" value="<?php echo $format; ?>" 
                                           <?php checked(in_array($format, $source_formats)); ?> />
                                    <?php echo ucfirst(str_replace('image/', '', $format)); ?>
                                </label><br>
                            <?php endforeach; ?>
                            <p class="description">Select which image formats should be converted</p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button(); ?>
            </form>
            
            <h2>Server Support Check</h2>
            <table class="form-table">
                <tr>
                    <th>GD Library</th>
                    <td><?php echo extension_loaded('gd') ? '✅ Available' : '❌ Not Available'; ?></td>
                </tr>
                <tr>
                    <th>WebP Support</th>
                    <td><?php echo function_exists('imagewebp') ? '✅ Available' : '❌ Not Available'; ?></td>
                </tr>
                <tr>
                    <th>ImageMagick</th>
                    <td><?php echo extension_loaded('imagick') ? '✅ Available' : '❌ Not Available'; ?></td>
                </tr>
            </table>
        </div>
        <?php
    }
    
    /**
     * Get plugin option with fallback to config default
     */
    private function get_option($key, $use_wp_option = false) {
        if ($use_wp_option) {
            return get_option('uic_' . $key, $this->config[$key]);
        }
        
        $wp_option_map = [
            'enabled' => 'uic_enabled',
            'target_format' => 'uic_target_format', 
            'quality' => 'uic_quality',
            'delete_original' => 'uic_delete_original',
            'source_formats' => 'uic_source_formats'
        ];
        
        if (isset($wp_option_map[$key])) {
            return get_option($wp_option_map[$key], $this->config[$key]);
        }
        
        return $this->config[$key] ?? null;
    }
    
    /**
     * Main conversion function
     */
    public function convert_image_format($upload) {
        $file_path = $upload['file'];
        $file_type = strtolower($upload['type']);
        
        // Check if this file type should be converted
        $source_formats = $this->get_option('source_formats');
        if (!in_array($file_type, $source_formats)) {
            return $upload;
        }
        
        $target_format = $this->get_option('target_format');
        $quality = $this->get_option('quality');
        
        // Don't convert if already in target format
        if ($file_type === $this->config['mime_types'][$target_format]) {
            return $upload;
        }
        
        // Create new file path with target extension
        $path_info = pathinfo($file_path);
        $new_file_path = $path_info['dirname'] . '/' . $path_info['filename'] . '.' . $target_format;
        
        // Load source image
        $source_image = $this->load_image($file_path, $file_type);
        if ($source_image === false) {
            return $upload; // Return original if load failed
        }
        
        // Convert to target format
        $conversion_success = $this->save_image($source_image, $new_file_path, $target_format, $quality);
        
        // Clean up source image resource
        imagedestroy($source_image);
        
        if ($conversion_success) {
            // Delete original file if configured
            if ($this->get_option('delete_original')) {
                @unlink($file_path);
            }
            
            // Update upload array with new file info
            $upload['file'] = $new_file_path;
            $upload['url'] = str_replace(
                '.' . $path_info['extension'], 
                '.' . $target_format, 
                $upload['url']
            );
            $upload['type'] = $this->config['mime_types'][$target_format];
        }
        
        return $upload;
    }
    
    /**
     * Load image based on file type
     */
    private function load_image($file_path, $file_type) {
        switch ($file_type) {
            case 'image/jpeg':
            case 'image/jpg':
                return @imagecreatefromjpeg($file_path);
            case 'image/png':
                return @imagecreatefrompng($file_path);
            case 'image/gif':
                return @imagecreatefromgif($file_path);
            case 'image/bmp':
                return @imagecreatefrombmp($file_path);
            case 'image/webp':
                return @imagecreatefromwebp($file_path);
            default:
                return false;
        }
    }
    
    /**
     * Save image in target format
     */
    private function save_image($image, $file_path, $format, $quality) {
        // Enable alpha channel for PNG and WebP
        if (in_array($format, ['png', 'webp'])) {
            imagealphablending($image, false);
            imagesavealpha($image, true);
        }
        
        switch ($format) {
            case 'webp':
                return function_exists('imagewebp') ? imagewebp($image, $file_path, $quality) : false;
            case 'jpeg':
            case 'jpg':
                return imagejpeg($image, $file_path, $quality);
            case 'png':
                // PNG quality is 0-9, convert from 0-100 scale
                $png_quality = 9 - round(($quality / 100) * 9);
                return imagepng($image, $file_path, $png_quality);
            case 'gif':
                return imagegif($image, $file_path);
            default:
                return false;
        }
    }
}

// Initialize the plugin
new UniversalImageConverter();
