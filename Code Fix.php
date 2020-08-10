	function et_plugin_setup_builder() {
		define( 'ET_BUILDER_PLUGIN_ACTIVE', true );

		define( 'ET_BUILDER_VERSION', ET_BUILDER_PLUGIN_VERSION );

		define( 'ET_BUILDER_DIR', ET_BUILDER_PLUGIN_DIR . 'includes/builder/' );
		define( 'ET_BUILDER_URI', trailingslashit( plugins_url( '', __FILE__ ) ) . 'includes/builder' );
		define( 'ET_BUILDER_LAYOUT_POST_TYPE', 'et_pb_layout' );

		// Start of code changed by Ralph
		//fix divi builder language due to wordpress core logic issue.
        // Are we using WPML ?
        if(defined('ICL_LANGUAGE_CODE') && is_admin()) {
            $builderlang = "";
            if (current_user_can( 'edit_posts' )){

                $etbuilder_userid = get_current_user_id();
                $etbuilder_useadminlang = get_user_meta( $etbuilder_userid, "icl_admin_language_for_edit", true );
                if (get_user_meta( $etbuilder_userid, "icl_admin_language_for_edit", true )) {
                    // Builder language will be current language being edited
                } else {
                    // determine which language the builder should use
                    $useadminlang = get_user_meta( $etbuilder_userid, "locale", true );
                    if ($useadminlang >"") {
                        $userlangopt = explode("_", $useadminlang);
                        $builderlang = $userlangopt[0];
                    } else {
                        // make it the current language in WPML - using english for now.
                        $builderlang = "en";
                    }
                }
                $et_editor_lpath = ET_BUILDER_DIR . 'languages' . ($builderlang >"" ? ("/" . $builderlang) : "");
                if (!file_exists($et_editor_lpath )) {
                    // user has editing rights so verify that custom language directory and required files exist and fix if missing.
                    global $wpdb;
                    $origlang = $builderlang;
                    $etbuilder_userid = get_current_user_id();
                    if ($origlang > "") {
                    } else {
                        // make it the current language in WPML - using english for now.
                        $origlang = "en";
                    }
                    $builderlangdir = "/" . $origlang;
                    $buildermainlangpath = ET_BUILDER_DIR . 'languages';
                    $builderlangpath = ET_BUILDER_DIR . 'languages' . $builderlangdir;
                    if (!file_exists($builderlangpath)) {
                        // We must create dir and copy required files
                        global $wpdb;
                        //Create our directory to store seudo language files
                        mkdir($builderlangpath);            
                    }
                    $admin_locale = $wpdb->get_var("SELECT default_locale FROM " . $wpdb->prefix . "icl_languages WHERE code = '" . $origlang . "'");
                    if (!is_null($admin_locale)) {
                        // Get All Active Languages for this website, If Multisite and different languages on various sites - will fix first time run!
                        $languages = $wpdb->get_col("SELECT default_locale FROM " . $wpdb->prefix . "icl_languages WHERE active = 1");
                        foreach ( $languages as $localetoprocess ){
                            $divibuilder_langfile = $builderlangpath . "/" . $localetoprocess . ".mo";
                            if (!file_exists($divibuilder_langfile)) {
                                $fromfile = $buildermainlangpath . "/" . $admin_locale . ".mo";
                                copy( $fromfile, $divibuilder_langfile );
                            }                        
                            $gutenbergeditor_langfile = $builderlangpath . "/" . "et_builder-" . $localetoprocess . "-et-builder-gutenberg.json";
                            if (!file_exists($gutenbergeditor_langfile)) {
                                $fromfile = $buildermainlangpath . "/" . "et_builder-" . $admin_locale . "-et-builder-gutenberg.json";
                                copy( $fromfile, $gutenbergeditor_langfile );
                            }
                        }
                    }                    
                }
            }
            if ($builderlang >"") {
                $builderlang = "/" . $builderlang;
            }
            load_theme_textdomain( 'et_builder', ET_BUILDER_DIR . 'languages' . $builderlang );
        } else {
	    // end of code changes
		load_theme_textdomain( 'et_builder', ET_BUILDER_DIR . 'languages' );
		// Start code change by Ralph
        }
		// End code change by Ralph
		load_plugin_textdomain( 'et_builder_plugin', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );

		require ET_BUILDER_PLUGIN_DIR . 'functions.php';
		require ET_BUILDER_PLUGIN_DIR . 'theme-compat.php';
		require ET_BUILDER_DIR . 'framework.php';

		et_pb_register_posttypes();

		add_action( 'admin_menu', array( $this, 'add_divi_menu' ));

		// Check if the plugin was just activated and call for the et_builder_prepare_bfb().
		if ( 'activated' === get_option( 'et_pb_builder_plugin_status', '' ) ) {
			et_builder_prepare_bfb();
			// Delete cached definitions / helpers
			et_fb_delete_builder_assets();
			delete_option( 'et_pb_builder_plugin_status' );
		}
	}
