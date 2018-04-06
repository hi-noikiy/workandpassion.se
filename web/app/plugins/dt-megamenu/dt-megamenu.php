<?php
defined( 'ABSPATH' ) OR exit;
/*
Plugin Name:  Detheme Megamenu Plugin
Plugin URI:   http://detheme.com
Description:  Adds a mega menu functionality to your website
Version:      1.0.8
Author:       detheme.com
Author URI: http://www.detheme.com/
Author Email:
License:
License URI:

Copyright 2014 Detheme
 */

require_once ABSPATH . 'wp-admin/includes/nav-menu.php';
require_once 'class-dt-megamenu-walker.php';

// Load at the default priority of 10
add_action( 'plugins_loaded', array( 'dt_Megamenu', 'init' ) );

class dt_Megamenu {
    /**
     * Instance of the class
     * @static
     * @access protected
     * @var object
     */

    protected static $instance;

    /**
     * ID of the custom metabox
     * @var string
     */
    public $metabox_id = 'megamenu_sectionid';

    public static function init() {
        is_null( self::$instance ) AND self::$instance = new self;
        return self::$instance;
    }


    /**
     * Constructor.
     * @return \dt_Megamenu
     */
    public function __construct() {

        //Adds new menu to menus in admin section
        add_action( 'admin_menu', array( $this, 'add_metabox' ) );

        //Saves megamenu specific fields
        add_action('wp_update_nav_menu_item', array( $this, 'save_megamenu_fields' ), 10, 3  );

        //Reads megamenu fields from post and set it up to menu item
        add_filter( 'wp_setup_nav_menu_item', array( $this, 'read_megamenu_fields' ) );

        //Adds specific classes to megamenu items in frontend side
        add_filter( 'wp_nav_menu_objects', array( $this, 'add_classes_to_megamenu_items_frontend' ) );

        //Adds specific attributes to megamenu items in frontend side
        //add_filter( 'wp_nav_menu_items', array( $this, 'add_attributes_to_megamenu_items_frontend' ) );

        //Replace original nav_edit_walker class
        add_filter( 'wp_edit_nav_menu_walker', array( $this, 'replace_nav_edit_walker' ) );

        // Allows HTML tags and shortcodes in megamenu-content menu item
        add_filter( 'walker_nav_menu_start_el', array( $this, 'nav_menu_with_description' ), 10, 4 );

        //Switches off striping HTML and short code tags for megamenu-content item
        add_filter('wp_setup_nav_menu_item', array( $this, 'strip_tags_but_content_item' ) );

    }

//
//Adds metabox to admin menus area
//
    function add_metabox() {

        add_meta_box(
            $this->metabox_id,
            'Mega Menu Elements',
            array( $this, 'add_megamenu_metabox' ),
            'nav-menus', 'side', 'high'
        );

    }

    function add_megamenu_metabox( $post ) {

        // Add an nonce field so we can check for it later.
        wp_nonce_field( 'add_megamenu_metabox', 'add_megamenu_metabox_nonce' );

        global $_nav_menu_placeholder, $nav_menu_selected_id;

        $_nav_menu_placeholder = 0 > $_nav_menu_placeholder ? $_nav_menu_placeholder - 1 : -1;

    ?>
        <div class="customlink-add-item" id="customlink-add-item">
            <input type="submit" <?php wp_nav_menu_disabled_check( $nav_menu_selected_id ); ?> class="button-secondary submit-add-to-menu" value="<?php esc_attr_e('Add Column'); ?>" name="add-column" id="submit-megamenu-column" onclick="jQuery('#custom-menu-item-name-megamenu').val('Mega Menu Column') "/><br><br>
            <input type="submit" <?php wp_nav_menu_disabled_check( $nav_menu_selected_id ); ?> class="button-secondary submit-add-to-menu" value="<?php esc_attr_e('Add Heading Item'); ?>" name="add-column" id="submit-megamenu-heading-item" onclick="jQuery('#custom-menu-item-name-megamenu').val('Mega Menu Heading') "/><br><br>
            <input type="submit" <?php wp_nav_menu_disabled_check( $nav_menu_selected_id ); ?> class="button-secondary submit-add-to-menu" value="<?php esc_attr_e('Add Content Item'); ?>" name="add-column" id="submit-megamenu-content-item" onclick="jQuery('#custom-menu-item-name-megamenu').val('Mega Menu Content') "/><br><br>


            <label for="myplugin_new_field">After you click on the button, menu item will appear on the bottom of current menu.<br>
                Rearrange it to the right position<br>
                <br>
            </label>
        </div>
    <?php
        wp_register_style( 'dt-megamenu-backend', plugins_url('dt-megamenu-backend.css', __FILE__) );
        wp_enqueue_style('dt-megamenu-backend');

        wp_register_script(
            'dt-megamenu-js',
            plugins_url( 'dt-megamenu.js', __FILE__ ),
            array( 'jquery' ),
            filemtime( plugin_dir_path( __FILE__ ).'dt-megamenu.js' ),
            true
        );
        wp_enqueue_script( 'dt-megamenu-js' );



    }

//
// Saves new fields to postmeta for navigation
//
    function save_megamenu_fields($menu_id, $menu_item_db_id, $args ) {
        if ( isset( $_REQUEST['menu-item-dt-megamenu-type'][$menu_item_db_id] ) ) {
            $custom_value = $_REQUEST['menu-item-dt-megamenu-type'][$menu_item_db_id];
            update_post_meta( $menu_item_db_id, '_menu_item_dt_megamenu_type', $custom_value );
        }

        if ( isset( $_REQUEST['menu-item-dt-megamenu'][$menu_item_db_id] ) ) {
            $custom_value = $_REQUEST['menu-item-dt-megamenu'][$menu_item_db_id];
            update_post_meta( $menu_item_db_id, '_menu_item_dt_megamenu', $custom_value );
        } else {
            delete_post_meta( $menu_item_db_id, '_menu_item_dt_megamenu');
        }

        if ( isset( $_REQUEST['menu-item-dt-megamenu-logo'][$menu_item_db_id] ) ) {
            $custom_value = $_REQUEST['menu-item-dt-megamenu-logo'][$menu_item_db_id];
            update_post_meta( $menu_item_db_id, '_menu_item_dt_megamenu_logo', $custom_value );
        } else {
            delete_post_meta( $menu_item_db_id, '_menu_item_dt_megamenu_logo');
        }

        if ( isset( $_REQUEST['menu-item-dt-megamenu-description'][$menu_item_db_id] ) ) {
            $custom_value = $_REQUEST['menu-item-dt-megamenu-description'][$menu_item_db_id];
            if (strrpos($custom_value, "full") === false) {
                $custom_value = preg_replace('/\D/', '', $custom_value);
            }
            else $custom_value = 'full';
            update_post_meta( $menu_item_db_id, '_menu_item_dt_megamenu_description', $custom_value );
        }

        if ( isset( $_REQUEST['menu-item-dt-megamenu-background-url'][$menu_item_db_id] ) ) {
            $custom_value = $_REQUEST['menu-item-dt-megamenu-background-url'][$menu_item_db_id];
            update_post_meta( $menu_item_db_id, '_menu_item_dt_megamenu_background_url', $custom_value );
        }

        if ( isset( $_REQUEST['menu-item-dt-megamenu-background-horizontal-position'][$menu_item_db_id] ) ) {
            $custom_value = $_REQUEST['menu-item-dt-megamenu-background-horizontal-position'][$menu_item_db_id];
            update_post_meta( $menu_item_db_id, '_menu_item_dt_megamenu_background_horizontal_position', $custom_value );
        }

        if ( isset( $_REQUEST['menu-item-dt-megamenu-background-vertical-position'][$menu_item_db_id] ) ) {
            $custom_value = $_REQUEST['menu-item-dt-megamenu-background-vertical-position'][$menu_item_db_id];
            update_post_meta( $menu_item_db_id, '_menu_item_dt_megamenu_background_vertical_position', $custom_value );
        }

        if ( isset( $_REQUEST['menu-item-dt-megamenu-background-repeat'][$menu_item_db_id] ) ) {
            $custom_value = $_REQUEST['menu-item-dt-megamenu-background-repeat'][$menu_item_db_id];
            update_post_meta( $menu_item_db_id, '_menu_item_dt_megamenu_background_repeat', $custom_value );
        }

        if ( isset( $_REQUEST['menu-item-dt-megamenu-width-options'][$menu_item_db_id] ) ) {
            $custom_value = $_REQUEST['menu-item-dt-megamenu-width-options'][$menu_item_db_id];
            update_post_meta( $menu_item_db_id, '_menu_item_dt_megamenu_width_options', $custom_value );
        }

        if ( isset( $_REQUEST['menu-item-dt-megamenu-width'][$menu_item_db_id] ) ) {
            $custom_value = $_REQUEST['menu-item-dt-megamenu-width'][$menu_item_db_id];
            update_post_meta( $menu_item_db_id, '_menu_item_dt_megamenu_width', $custom_value );
        }

        if ( isset( $_REQUEST['menu-item-dt-megamenu-content-pages'][$menu_item_db_id] ) ) {
            $custom_value = $_REQUEST['menu-item-dt-megamenu-content-pages'][$menu_item_db_id];
            update_post_meta( $menu_item_db_id, '_menu_item_dt_megamenu_content_pages', $custom_value );
        }

    }

//
// Reads values of new fields to $item object that will be passed to Walker_Nav_Menu_Edit_Custom
//
    function read_megamenu_fields($menu_item) {

        $megamenuWidthOptions=get_post_meta( $menu_item->ID, '_menu_item_dt_megamenu_width_options', true );

        

        $megamenuActive=get_post_meta( $menu_item->ID, '_menu_item_dt_megamenu', true );
        $menu_item->megamenuActive = ($megamenuActive=='active')?"active":$megamenuActive;

        $megamenuLogo=get_post_meta( $menu_item->ID, '_menu_item_dt_megamenu_logo', true );
        $menu_item->megamenuLogo = ($megamenuLogo=='active')?"active":$megamenuLogo;


        $menu_item->megamenuDescription = get_post_meta( $menu_item->ID, '_menu_item_dt_megamenu_description', true );
        $menu_item->megamenuType = get_post_meta( $menu_item->ID, '_menu_item_dt_megamenu_type', true );
        $menu_item->megamenuBackgroundURL = get_post_meta( $menu_item->ID, '_menu_item_dt_megamenu_background_url', true );
        $menu_item->megamenuBackgroundHorizontalPosition = get_post_meta( $menu_item->ID, '_menu_item_dt_megamenu_background_horizontal_position', true );
        $menu_item->megamenuBackgroundVerticalPosition = get_post_meta( $menu_item->ID, '_menu_item_dt_megamenu_background_vertical_position', true );
        $menu_item->megamenuBackgroundRepeat = get_post_meta( $menu_item->ID, '_menu_item_dt_megamenu_background_repeat', true );
        $menu_item->megamenuWidthOptions = ($megamenuWidthOptions)?$megamenuWidthOptions:"full-dt-megamenu";
        $menu_item->megamenuWidth = get_post_meta( $menu_item->ID, '_menu_item_dt_megamenu_width', true );

        $menu_item->megamenuContentPages=get_post_meta( $menu_item->ID, '_menu_item_dt_megamenu_content_pages', true );
        return $menu_item;
    }

//
//adds frontend megamenu items classes
//
    function add_classes_to_megamenu_items_frontend($menu_items) {



        foreach ($menu_items as $menu_item) {
            array_push($menu_item->classes, $menu_item->megamenuType);

            if ($menu_item->megamenuActive == "active") {
                //array_push($menu_item->classes, 'megamenu-parent');
                array_push($menu_item->classes, 'dt-megamenu');
                //array_push($menu_item->classes, 'data-width="'.$menu_item->megamenuDescription.'"');
            }

            if ($menu_item->megamenuWidthOptions == "dt-megamenu-width-set sticky-left") {
                array_push($menu_item->classes, 'dt-megamenu-width-set');
                array_push($menu_item->classes, 'sticky-left');

//                add_action('wp_print_scripts', 'dtheme_print_megamenu_inline_style' );
            }


        }
        return $menu_items;
    }
/*
    function dtheme_print_megamenu_inline_style() {
        print "<style type=\"text/css\">\n";
        print ".test_megamenu { width: 100px; }";
        print "</style>\n";
    }
*/
//
//adds frontend megamenu items attributes
//
    function add_attributes_to_megamenu_items_frontend($menu) {
        wp_register_script(
            'jquery_hoverIntent_minified-js',
            plugins_url( 'jquery.hoverIntent.minified.js', __FILE__ ),
            array( 'jquery' ),
            filemtime( plugin_dir_path( __FILE__ ).'jquery.hoverIntent.minified.js' ),
            true
        );
        wp_enqueue_script( 'jquery_hoverIntent_minified-js' );

        wp_register_script(
            'dt-megamenu-frontend-js',
            plugins_url( 'dt-megamenu-frontend.js', __FILE__ ),
            array( 'jquery' ),
            filemtime( plugin_dir_path( __FILE__ ).'dt-megamenu-frontend.js' ),
            true
        );
        wp_enqueue_script( 'dt-megamenu-frontend-js' );

        wp_register_style( 'dt-megamenu-frontend', plugins_url('dt-megamenu-frontend.css', __FILE__) );
        wp_enqueue_style('dt-megamenu-frontend');

        // Icon font
        wp_register_style( 'dt-megamenu-iconfont', plugins_url('/iconfont/style.css', __FILE__) );
        wp_enqueue_style('dt-megamenu-iconfont');

        $matches = explode('"', $menu);
        foreach ($matches as $match) {
            $data_width_value = strstr($match, 'data-width=');
            if ($data_width_value != false) {
                $just_value = explode('&quot;', $data_width_value);
                $new_match = str_replace("data-width=&quot;".$just_value[1]."&quot;", "", $match);
                $new_match = $new_match.'" data-width="'.$just_value[1];
                $menu = str_replace($match, $new_match, $menu);
            }
        }
        return $menu;
    }

//
//Replace original nav_edit_walker class
//
    function replace_nav_edit_walker() {
        return 'dt_Megamenu_Walker';
    }

//
// Allows HTML tags and shortcodes in megamenu-content menu item
//
    function nav_menu_with_description ( $item_output, $item, $depth, $args ) {
        if ( isset( $item->megamenuType ) )
            if ('megamenu-content' == $item->megamenuType) {
                if (isset($item->megamenuContentPages)&&!empty($item->megamenuContentPages)) {
                    $item_output = $this->detheme_load_megamenu_vc_content($item->megamenuContentPages);
                } else {
                    $item_output = do_shortcode( $item->post_content );    
                }
                //$item_output = do_shortcode( $item->post_content );
            }
        return $item_output;
    }

  function detheme_load_megamenu_vc_content($page_id){
    if(!$page_id)
      return;

    $result = "";

    if(function_exists('vc_set_as_theme')){

      $assetPath=plugins_url( 'js_composer/assets/css','js_composer');

      $front_css_file = version_compare(WPB_VC_VERSION,"4.2.3",'>=')?$assetPath.'/js_composer.css':$assetPath.'/js_composer_front.css';

      $upload_dir = wp_upload_dir();

      if(function_exists('vc_settings')){

        if ( vc_settings()->get( 'use_custom' ) == '1' && is_file( $upload_dir['basedir'] . '/js_composer/js_composer_front_custom.css' ) ) {
          $front_css_file = $upload_dir['baseurl'] . '/js_composer/js_composer_front_custom.css';
        }
      }
      else{
        if ( WPBakeryVisualComposerSettings::get('use_custom') == '1' && is_file( $upload_dir['basedir'] . '/js_composer/js_composer_front_custom.css' ) ) {
          $front_css_file = $upload_dir['baseurl'] . '/js_composer/js_composer_front_custom.css';
        }

      }

      wp_register_style( 'js_composer_front', $front_css_file, false, WPB_VC_VERSION, 'all' );
      
      if ( is_file( $upload_dir['basedir'] . '/js_composer/custom.css' ) ) {
        wp_register_style( 'js_composer_custom_css', $upload_dir['baseurl'] . '/js_composer/custom.css', array(), WPB_VC_VERSION, 'screen' );
      }

      wp_enqueue_style('js_composer_front');
      wp_enqueue_style('js_composer_custom_css');

    }

  $post = $this->_get_wpml_post($page_id);
  $old_sidebar=get_query_var('sidebar');
  set_query_var('sidebar','nosidebar');
  if (isset($post->post_content)) $result = do_shortcode($post->post_content);
  set_query_var('sidebar',$old_sidebar);

  return $result;
}

//
//Switches off striping HTML and short code tags for megamenu-content item
//
    function strip_tags_but_content_item ( $menuitem ) {
        if ( 'megamenu-content' == $menuitem->megamenuType ) {
            $allowed_tags = wp_kses_allowed_html( 'post' );
            $menuitem->post_content = wp_kses( $menuitem->post_content, $allowed_tags );
        }
        return $menuitem;
    }


    function _get_wpml_post($post_id){

      if(!defined('ICL_LANGUAGE_CODE'))
            return get_post($post_id);

        global $wpdb;

       $postid = $wpdb->get_var(
          $wpdb->prepare("SELECT element_id FROM {$wpdb->prefix}icl_translations WHERE trid=(SELECT trid FROM {$wpdb->prefix}icl_translations WHERE element_id='%d' LIMIT 1) AND element_id!='%d' AND language_code='%s'", $post_id,$post_id,ICL_LANGUAGE_CODE)
       );

      if($postid)
          return get_post($postid);
      
      return get_post($post_id);
    }

}





?>