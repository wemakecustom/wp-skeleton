<?php

/**
 * Plugin Name: MU plugins subdirectory loader
 * Plugin URI: https://gist.github.com/lavoiesl/6302907
 * Description: Enables the loading of plugins sitting in mu-plugins (as folders)
 * Version: 0.1
 * Author: github@lavoie.sl
 * Author URI: http://blog.lavoie.sl/
 *
 * Will clear cache when visiting the plugin page in /wp-admin/.
 * Will also clear cache if a previously detected mu-plugin was deleted.
 * Sadly, get_mu_plugins does not have any hooks.
 *
 * @file wp-content/mu-plugins/subdirectory_loader.php
 */

function sub_mu_plugins_files()
{
    // Cache plugins
    $plugins = get_site_transient('sub_mu_plugins');

    if ($plugins !== false) {
        // Validate plugins still exist
        // If not, invalidate cache
        foreach ($plugins as $plugin_file) {
            if (!is_readable(WPMU_PLUGIN_DIR . '/' . $plugin_file)) {
                $plugins = false;
                break;
            }
        }
    }

    if ($plugins === false) {
        echo 'loading';
        if (!function_exists('get_plugins')) {
            // get_plugins is not included by default
            require ABSPATH . 'wp-admin/includes/plugin.php';
        }

        // Invalid cache
        $plugins = array();
        foreach (get_plugins('/../mu-plugins') as $plugin_file => $data) {
            if (dirname($plugin_file) != '.') { // skip files directly at root
                $plugins[] = $plugin_file;
            }
        }

        set_site_transient('sub_mu_plugins', $plugins);
    }

    return $plugins;
}

add_action('muplugins_loaded', function(){
    if (isset($_SERVER['REQUEST_URI']) && strpos($_SERVER['REQUEST_URI'], '/wp-admin/plugins.php') !== false) {
        // delete cache when viewing plugins page in /wp-admin/
        delete_site_transient('sub_mu_plugins');
    }

    foreach (sub_mu_plugins_files() as $plugin_file) {
        require WPMU_PLUGIN_DIR . '/' . $plugin_file;
    }
});

/**
 * Add rows for each subplugin under this plugin when listing mu-plugins in wp-admin
 */
add_action('admin_init', function() {

    add_action('after_plugin_row_subdirectory_loader.php', function() {
        foreach (sub_mu_plugins_files() as $plugin_file) {
            // Strip down version of WP_Plugins_List_Table

            $data = get_plugin_data(WPMU_PLUGIN_DIR . '/' . $plugin_file);
            $name = empty($data['Plugin Name']) ? $plugin_file : $data['Plugin Name'];
            $desc = empty($data['Description']) ? '&nbsp;' : $data['Description'];
            $id = sanitize_title($name);

            echo <<<HTML
            <tr id="$id" class="active">
                <th scope="row" class="check-column"></th>
                <td class="plugin-title"><strong style="padding-left: 10px;">+&nbsp;&nbsp;$name</strong></td>
                <td class="column-description desc">
                    <div class="plugin-description"><p>$desc</p></div>
                </td>
            </tr>
HTML;
        }
    });
});
