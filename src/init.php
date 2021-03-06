<?php

/**
 * Blocks Initializer
 *
 * Enqueue CSS/JS of all the blocks.
 *
 * @since   1.0.0
 * @package CGB
 */

use Illuminate\Support\Arr;
use Tera\Instagram;

// Exit if accessed directly.
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Enqueue Gutenberg block assets for both frontend + backend.
 *
 * Assets enqueued:
 * 1. blocks.style.build.css - Frontend + Backend.
 * 2. blocks.build.js - Backend.
 * 3. blocks.editor.build.css - Backend.
 *
 * @uses  {wp-blocks} for block type registration & related functions.
 * @uses  {wp-element} for WP Element abstraction — structure of blocks.
 * @uses  {wp-i18n} to internationalize the block's text.
 * @uses  {wp-editor} for WP editor styles.
 * @since 1.0.0
 */

new Instagram();

if (!function_exists('tera_instagram_block_cgb_block_assets')) {
    function tera_nstagram_block_cgb_block_assets()
    {
        wp_register_script('instagram-embed', '//instagram.com/embed.js');

        // Register block styles for both frontend + backend.
        wp_register_style(
            'tera_instagram_block-cgb-style-css', // Handle.
            plugins_url('dist/blocks.style.build.css', dirname(__FILE__)), // Block style CSS.
            is_admin() ? ['wp-editor'] : null, // Dependency to include the CSS after it.
            filemtime(plugin_dir_path(__DIR__) . 'dist/blocks.style.build.css') // Version: File modification time.
        );

        // Register block editor script for backend.
        wp_register_script(
            'tera_instagram_block-cgb-block-js',
            // Handle.
            plugins_url('/dist/blocks.build.js', dirname(__FILE__)),
            // Block.build.js: We register the block here. Built with Webpack.
            ['wp-blocks', 'wp-i18n', 'wp-element', 'wp-editor'],
            // Dependencies, defined above.
            null,
            // filemtime( plugin_dir_path( __DIR__ ) . 'dist/blocks.build.js' ), // Version: filemtime — Gets file modification time.
            true // Enqueue the script in the footer.
        );

        // Register block editor styles for backend.
        wp_register_style(
            'tera_instagram_block-cgb-block-editor-css', // Handle.
            plugins_url('dist/blocks.editor.build.css', dirname(__FILE__)), // Block editor CSS.
            ['wp-edit-blocks'], // Dependency to include the CSS after it.
            filemtime(plugin_dir_path(__DIR__) . 'dist/blocks.editor.build.css') // Version: File modification time.
        );

        // WP Localized globals. Use dynamic PHP stuff in JavaScript via `cgbGlobal` object.
        wp_localize_script(
            'tera_instagram_block-cgb-block-js',
            'cgbGlobal', // Array containing dynamic data for a JS Global.
            [
                'pluginDirPath' => plugin_dir_path(__DIR__),
                'pluginDirUrl'  => plugin_dir_url(__DIR__),
                // Add more data here that you want to access from `cgbGlobal` object.
            ]
        );

        /**
         * Register Gutenberg block on server-side.
         *
         * Register the block on server-side to ensure that the block
         * scripts and styles for both frontend and backend are
         * enqueued when the editor loads.
         *
         * @link  https://wordpress.org/gutenberg/handbook/blocks/writing-your-first-block-type#enqueuing-block-scripts
         * @since 1.16.0
         */


        register_block_type(
            'cgb/block-tera-instagram-block',
            [
                // Enqueue blocks.style.build.css on both frontend & backend.
                'style'           => ['tera_instagram_block-cgb-style-css'],
                // Enqueue blocks.build.js in the editor only.
                'editor_script'   => ['tera_instagram_block-cgb-block-js'],
                // Enqueue blocks.editor.build.css in the editor only.
                'editor_style'    => ['tera_instagram_block-cgb-block-editor-css'],
                'render_callback' => 'render_tera_scrape_instagram',
            ]
        );
    }

    if (!function_exists('render_tera_scrape_instagram')) {
        function render_tera_scrape_instagram($attributes)
        {
            wp_enqueue_script('instagram-embed');
            ob_start();
            ?>
            <?php
            if ($attributes['posts']) : ?>
                <div
                        style="--column-count: <?php
                        echo Arr::get($attributes, 'columnCount', 3); ?>"
                        class="tera-instagram-posts is-initialized">
                    <?php
                    foreach ($attributes['posts'] as $post) : ?>
                        <div class="tera-instagram-post">
                            <!--                                        data-instgrm-captioned-->
                            <blockquote class="instagram-media"
                                        data-instgrm-permalink="<?php
                                        echo $post['link']; ?>"
                                        data-instgrm-version="12">
                                <div>
                                    <a href="<?php
                                    echo $post['link']; ?>"
                                       target="_blank"></a>
                                </div>
                            </blockquote>
                        </div>
                        <?php
                    endforeach; ?>
                </div>
                <?php
            endif; ?>
            <?php
            return ob_get_clean();
        }
    }


    // Hook: Block assets.
    add_action('init', 'tera_nstagram_block_cgb_block_assets', 10);
}

