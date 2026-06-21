<?php
// Block direct access and any run outside WordPress' uninstall routine.
defined( 'ABSPATH' ) || exit;
defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

// LangDesk only persists one thing: the per-user language assignment. Remove it
// for every user. (No options, transients or custom tables are created.)
delete_metadata( 'user', 0, 'langdesk_allowed_langs', '', true );
