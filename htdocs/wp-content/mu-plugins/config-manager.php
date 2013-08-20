<?php

add_action('tgmpa_register', function() {
    tgmpa(array(
        array(
            'name'                  => 'Config Manager', // The plugin name
            'slug'                  => 'config-manager', // The plugin slug (typically the folder name)
            'required'              => true, // If false, the plugin is only 'recommended' instead of required
            'force_activation'      => true, // If true, plugin is activated upon theme activation and cannot be deactivated until theme switch
        ),
    ));
});
