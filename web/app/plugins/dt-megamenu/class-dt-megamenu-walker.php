<?php

/**
 * Copied from Walker_Nav_Menu_Edit class in core
 *
 * Create HTML list of nav menu input items.
 *
 * @package WordPress
 * @since 3.0.0
 * @uses Walker_Nav_Menu
 */
class dt_Megamenu_Walker extends Walker_Nav_Menu_Edit {

/**
* @see Walker::start_el()
* @since 3.0.0
*
* @param string $output Passed by reference. Used to append additional content.
* @param object $item Menu item data object.
* @param int $depth Depth of menu item. Used for padding.
* @param object $args
*/
    function start_el( &$output, $object, $depth = 0, $args = array(), $current_object_id = 0 ) {
        global $_wp_nav_menu_max_depth;
        $_wp_nav_menu_max_depth = $depth > $_wp_nav_menu_max_depth ? $depth : $_wp_nav_menu_max_depth;
        $item = $object;
        $indent = ( $depth ) ? str_repeat( "\t", $depth ) : '';

        ob_start();
        //        $item_id = esc_attr( $item->ID );
        $removed_args = array(
            'action',
            'customlink-tab',
            'edit-menu-item',
            'menu-item',
            'page-tab',
            '_wpnonce',
        );

        $original_title = '';
        if ( 'taxonomy' == $item->type ) {
            $original_title = get_term_field( 'name', $item->object_id, $item->object, 'raw' );
            if ( is_wp_error( $original_title ) ) $original_title = false;
        } elseif ( 'post_type' == $item->type ) {
            $original_object = get_post( $item->object_id );
            $original_title = $original_object->post_title;
        }

        $item_id = esc_attr( $item->ID );
        $classes = array(
            'menu-item menu-item-depth-' . $depth,
            'menu-item-' . esc_attr( $item->object ),
            'menu-item-edit-' . ( ( isset( $_GET['edit-menu-item'] ) && $item_id == $_GET['edit-menu-item'] ) ? 'active' : 'inactive'),
        );

        $title = $item->title;

        if ( ! empty( $item->_invalid ) ) {
            $classes[] = 'menu-item-invalid';
            /* translators: %s: title of menu item which is invalid */
            $title = sprintf( __( '%s (Invalid)' ), $item->title );
        } elseif ( isset( $item->post_status ) && 'draft' == $item->post_status ) {
            $classes[] = 'pending';
            /* translators: %s: title of menu item in draft status */
            $title = sprintf( __('%s (Pending)'), $item->title );
        }

        $title = ( ! isset( $item->label ) || '' == $item->label ) ? $title : $item->label;
        $submenu_text = '';
        if ( 0 == $depth )
            $submenu_text = 'style="display: none;"';

        $megamenu_title = ($item->megamenuActive == "active") ? '<span class="is-submenu">mega menu</span>' : '';

        switch ($item->url) {
            case '#MegaMenuColumn':
                $item->type_label = 'Column';
                $this->doColumnTemplate($output, $item, $depth, $args, $item_id, $classes, $title, $megamenu_title, $submenu_text, $removed_args, $original_title);
                break;
            case '#MegaMenuHeading':
                $item->type_label = 'Heading';
                $this->doHeadingTemplate($output, $item, $depth, $args, $item_id, $classes, $title, $megamenu_title, $submenu_text, $removed_args, $original_title);
                break;
            case '#MegaMenuContent':
                $item->type_label = 'Content';
                $this->doContentTemplate($output, $item, $depth, $args, $item_id, $classes, $title, $megamenu_title, $submenu_text, $removed_args, $original_title);
                break;
            default:
                $this->doDefaultTemplate($output, $item, $depth, $args, $item_id, $classes, $title, $megamenu_title, $submenu_text, $removed_args, $original_title);
                break;
        }
        $output .= ob_get_clean();
    }

    function doColumnTemplate(&$output, $item, $depth, $args, &$item_id, &$classes, &$title, &$megamenu_title, &$submenu_text, &$removed_args, &$original_title) {

    ?>
    <li id="menu-item-<?php echo $item_id; ?>" class="<?php echo implode(' ', $classes ); ?> megamenu-column">
        <dl class="menu-item-bar">
            <dt class="menu-item-handle">
                <span class="item-title"><span class="menu-item-title"><?php echo esc_html( $title ).' '.$megamenu_title; ?></span> <span class="is-submenu" <?php echo $submenu_text; ?>><?php _e( 'sub item' ); ?></span></span>
                        <span class="item-controls">
                            <span class="item-type"><?php echo esc_html( $item->type_label ); ?></span>
                            <span class="item-order hide-if-js">
                                <a href="<?php
                                echo wp_nonce_url(
                                    add_query_arg(
                                        array(
                                            'action' => 'move-up-menu-item',
                                            'menu-item' => $item_id,
                                        ),
                                        remove_query_arg($removed_args, admin_url( 'nav-menus.php' ) )
                                    ),
                                    'move-menu_item'
                                );
                                ?>" class="item-move-up"><abbr title="<?php esc_attr_e('Move up'); ?>">&#8593;</abbr></a>
                                |
                                <a href="<?php
                                echo wp_nonce_url(
                                    add_query_arg(
                                        array(
                                            'action' => 'move-down-menu-item',
                                            'menu-item' => $item_id,
                                        ),
                                        remove_query_arg($removed_args, admin_url( 'nav-menus.php' ) )
                                    ),
                                    'move-menu_item'
                                );
                                ?>" class="item-move-down"><abbr title="<?php esc_attr_e('Move down'); ?>">&#8595;</abbr></a>
                            </span>
                            <a class="item-edit" id="edit-<?php echo $item_id; ?>" title="<?php esc_attr_e('Edit Menu Item'); ?>" href="<?php
                            echo ( isset( $_GET['edit-menu-item'] ) && $item_id == $_GET['edit-menu-item'] ) ? admin_url( 'nav-menus.php' ) : add_query_arg( 'edit-menu-item', $item_id, remove_query_arg( $removed_args, admin_url( 'nav-menus.php#menu-item-settings-' . $item_id ) ) );
                            ?>"><?php _e( 'Edit Menu Item' ); ?></a>
                        </span>
            </dt>
        </dl>

        <div class="menu-item-settings" id="menu-item-settings-<?php echo $item_id; ?>">
            <?php if( 'custom' == $item->type ) : ?>

                <input type="hidden" id="edit-menu-item-url-<?php echo $item_id; ?>" class="widefat code edit-menu-item-url" name="menu-item-url[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->url ); ?>" />

            <?php endif; ?>


            <input type="hidden" id="edit-menu-item-title-<?php echo $item_id; ?>" class="widefat edit-menu-item-title" name="menu-item-title[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->title ); ?>" />


            <input type="hidden" id="edit-menu-item-attr-title-<?php echo $item_id; ?>" class="widefat edit-menu-item-attr-title" name="menu-item-attr-title[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->post_excerpt ); ?>" />


            <p class="field-css-classes description description-thin">
                <label for="edit-menu-item-classes-<?php echo $item_id; ?>">
                    <?php _e( 'CSS Classes (optional)' ); ?><br />
                    <input type="text" id="edit-menu-item-classes-<?php echo $item_id; ?>" class="widefat code edit-menu-item-classes" name="menu-item-classes[<?php echo $item_id; ?>]" value="<?php echo esc_attr( implode(' ', $item->classes ) ); ?>" />
                </label>
            </p>
            <input type="hidden" id="edit-menu-item-xfn-<?php echo $item_id; ?>" class="widefat code edit-menu-item-xfn" name="menu-item-xfn[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->xfn ); ?>" />



            <p class="field-move hide-if-no-js description description-wide">
                <label>
                    <span><?php _e( 'Move' ); ?></span>
                    <a href="#" class="menus-move-up"><?php _e( 'Up one' ); ?></a>
                    <a href="#" class="menus-move-down"><?php _e( 'Down one' ); ?></a>
                    <a href="#" class="menus-move-left"></a>
                    <a href="#" class="menus-move-right"></a>
                    <a href="#" class="menus-move-top"><?php _e( 'To the top' ); ?></a>
                </label>
            </p>

            <div class="menu-item-actions description-wide submitbox">
                <?php if( 'custom' != $item->type && $original_title !== false ) : ?>
                    <p class="link-to-original">
                        <?php printf( __('Original: %s'), '<a href="' . esc_attr( $item->url ) . '">' . esc_html( $original_title ) . '</a>' ); ?>
                    </p>
                <?php endif; ?>
                <a class="item-delete submitdelete deletion" id="delete-<?php echo $item_id; ?>" href="<?php
                echo wp_nonce_url(
                    add_query_arg(
                        array(
                            'action' => 'delete-menu-item',
                            'menu-item' => $item_id,
                        ),
                        admin_url( 'nav-menus.php' )
                    ),
                    'delete-menu_item_' . $item_id
                ); ?>"><?php _e( 'Remove' ); ?></a> <span class="meta-sep hide-if-no-js"> | </span> <a class="item-cancel submitcancel hide-if-no-js" id="cancel-<?php echo $item_id; ?>" href="<?php echo esc_url( add_query_arg( array( 'edit-menu-item' => $item_id, 'cancel' => time() ), admin_url( 'nav-menus.php' ) ) );
                ?>#menu-item-settings-<?php echo $item_id; ?>"><?php _e('Cancel'); ?></a>
            </div>

            <?php
            //
            //        megamenu custom code here
            //

            $item_id = $item->ID;
            $key = 'menu-item-dt-megamenu';

            echo '<input type="hidden" id="edit-'.$key.'-type-'.$item_id.'" class="widefat edit-menu-item-description" value="megamenu-column" name="'.$key.'-type'. '['. $item_id .']">';

            //
            //        end of megamenu custom code
            //
            ?>

            <input class="menu-item-data-db-id" type="hidden" name="menu-item-db-id[<?php echo $item_id; ?>]" value="<?php echo $item_id; ?>" />
            <input class="menu-item-data-object-id" type="hidden" name="menu-item-object-id[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->object_id ); ?>" />
            <input class="menu-item-data-object" type="hidden" name="menu-item-object[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->object ); ?>" />
            <input class="menu-item-data-parent-id" type="hidden" name="menu-item-parent-id[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->menu_item_parent ); ?>" />
            <input class="menu-item-data-position" type="hidden" name="menu-item-position[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->menu_order ); ?>" />
            <input class="menu-item-data-type" type="hidden" name="menu-item-type[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->type ); ?>" />
        </div><!-- .menu-item-settings-->
        <ul class="menu-item-transport"></ul>
        <?php

        }

    function doHeadingTemplate(&$output, $item, $depth, $args, &$item_id, &$classes, &$title, &$megamenu_title, &$submenu_text, &$removed_args, &$original_title) {
        ?>
    <li id="menu-item-<?php echo $item_id; ?>" class="<?php echo implode(' ', $classes ); ?>">
        <dl class="menu-item-bar">
            <dt class="menu-item-handle">
                <span class="item-title"><span class="menu-item-title"><?php echo esc_html( $title ).' '.$megamenu_title; ?></span> <span class="is-submenu" <?php echo $submenu_text; ?>><?php _e( 'sub item' ); ?></span></span>
                        <span class="item-controls">
                            <span class="item-type"><?php echo esc_html( $item->type_label ); ?></span>
                            <span class="item-order hide-if-js">
                                <a href="<?php
                                echo wp_nonce_url(
                                    add_query_arg(
                                        array(
                                            'action' => 'move-up-menu-item',
                                            'menu-item' => $item_id,
                                        ),
                                        remove_query_arg($removed_args, admin_url( 'nav-menus.php' ) )
                                    ),
                                    'move-menu_item'
                                );
                                ?>" class="item-move-up"><abbr title="<?php esc_attr_e('Move up'); ?>">&#8593;</abbr></a>
                                |
                                <a href="<?php
                                echo wp_nonce_url(
                                    add_query_arg(
                                        array(
                                            'action' => 'move-down-menu-item',
                                            'menu-item' => $item_id,
                                        ),
                                        remove_query_arg($removed_args, admin_url( 'nav-menus.php' ) )
                                    ),
                                    'move-menu_item'
                                );
                                ?>" class="item-move-down"><abbr title="<?php esc_attr_e('Move down'); ?>">&#8595;</abbr></a>
                            </span>
                            <a class="item-edit" id="edit-<?php echo $item_id; ?>" title="<?php esc_attr_e('Edit Menu Item'); ?>" href="<?php
                            echo ( isset( $_GET['edit-menu-item'] ) && $item_id == $_GET['edit-menu-item'] ) ? admin_url( 'nav-menus.php' ) : add_query_arg( 'edit-menu-item', $item_id, remove_query_arg( $removed_args, admin_url( 'nav-menus.php#menu-item-settings-' . $item_id ) ) );
                            ?>"><?php _e( 'Edit Menu Item' ); ?></a>
                        </span>
            </dt>
        </dl>

        <div class="menu-item-settings" id="menu-item-settings-<?php echo $item_id; ?>">
            <?php if( 'custom' == $item->type ) : ?>

                <input type="hidden" id="edit-menu-item-url-<?php echo $item_id; ?>" class="widefat code edit-menu-item-url" name="menu-item-url[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->url ); ?>" />

            <?php endif; ?>


            <p class="description description-wide">
                <label for="edit-menu-item-title-<?php echo $item_id; ?>">
                    <?php _e( 'Heading Label' ); ?><br />
                    <input type="text" id="edit-menu-item-title-<?php echo $item_id; ?>" class="widefat edit-menu-item-title" name="menu-item-title[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->title ); ?>" />
                </label>
            </p>

            <input type="hidden" id="edit-menu-item-attr-title-<?php echo $item_id; ?>" class="widefat edit-menu-item-attr-title" name="menu-item-attr-title[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->post_excerpt ); ?>" />


            <p class="field-css-classes description description-thin">
                <label for="edit-menu-item-classes-<?php echo $item_id; ?>">
                    <?php _e( 'CSS Classes (optional)' ); ?><br />
                    <input type="text" id="edit-menu-item-classes-<?php echo $item_id; ?>" class="widefat code edit-menu-item-classes" name="menu-item-classes[<?php echo $item_id; ?>]" value="<?php echo esc_attr( implode(' ', $item->classes ) ); ?>" />
                </label>
            </p>
            <input type="hidden" id="edit-menu-item-xfn-<?php echo $item_id; ?>" class="widefat code edit-menu-item-xfn" name="menu-item-xfn[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->xfn ); ?>" />

            <p class="field-description description description-wide">
                <label for="edit-menu-item-description-<?php echo $item_id; ?>">
                    <?php _e( 'Description' ); ?><br />
                    <textarea id="edit-menu-item-description-<?php echo $item_id; ?>" class="widefat edit-menu-item-description" rows="3" cols="20" name="menu-item-description[<?php echo $item_id; ?>]"><?php echo esc_html( $item->description ); // textarea_escaped ?></textarea>
                    <span class="description"><?php _e('The description will be displayed in the menu if the current theme supports it.'); ?></span>
                </label>
            </p>

            <p class="field-move hide-if-no-js description description-wide">
                <label>
                    <span><?php _e( 'Move' ); ?></span>
                    <a href="#" class="menus-move-up"><?php _e( 'Up one' ); ?></a>
                    <a href="#" class="menus-move-down"><?php _e( 'Down one' ); ?></a>
                    <a href="#" class="menus-move-left"></a>
                    <a href="#" class="menus-move-right"></a>
                    <a href="#" class="menus-move-top"><?php _e( 'To the top' ); ?></a>
                </label>
            </p>

            <div class="menu-item-actions description-wide submitbox">
                <?php if( 'custom' != $item->type && $original_title !== false ) : ?>
                    <p class="link-to-original">
                        <?php printf( __('Original: %s'), '<a href="' . esc_attr( $item->url ) . '">' . esc_html( $original_title ) . '</a>' ); ?>
                    </p>
                <?php endif; ?>
                <a class="item-delete submitdelete deletion" id="delete-<?php echo $item_id; ?>" href="<?php
                echo wp_nonce_url(
                    add_query_arg(
                        array(
                            'action' => 'delete-menu-item',
                            'menu-item' => $item_id,
                        ),
                        admin_url( 'nav-menus.php' )
                    ),
                    'delete-menu_item_' . $item_id
                ); ?>"><?php _e( 'Remove' ); ?></a> <span class="meta-sep hide-if-no-js"> | </span> <a class="item-cancel submitcancel hide-if-no-js" id="cancel-<?php echo $item_id; ?>" href="<?php echo esc_url( add_query_arg( array( 'edit-menu-item' => $item_id, 'cancel' => time() ), admin_url( 'nav-menus.php' ) ) );
                ?>#menu-item-settings-<?php echo $item_id; ?>"><?php _e('Cancel'); ?></a>
            </div>

            <?php
            //
            //        megamenu custom code here
            //

            $item_id = $item->ID;
            $key = 'menu-item-dt-megamenu';

            echo '<input type="hidden" id="edit-'.$key.'-type-'.$item_id.'" class="widefat edit-menu-item-description" value="megamenu-heading" name="'.$key.'-type'. '['. $item_id .']">';

            //
            //        end of megamenu custom code
            //
            ?>

            <input class="menu-item-data-db-id" type="hidden" name="menu-item-db-id[<?php echo $item_id; ?>]" value="<?php echo $item_id; ?>" />
            <input class="menu-item-data-object-id" type="hidden" name="menu-item-object-id[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->object_id ); ?>" />
            <input class="menu-item-data-object" type="hidden" name="menu-item-object[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->object ); ?>" />
            <input class="menu-item-data-parent-id" type="hidden" name="menu-item-parent-id[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->menu_item_parent ); ?>" />
            <input class="menu-item-data-position" type="hidden" name="menu-item-position[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->menu_order ); ?>" />
            <input class="menu-item-data-type" type="hidden" name="menu-item-type[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->type ); ?>" />
        </div><!-- .menu-item-settings-->
        <ul class="menu-item-transport"></ul>
        <?php

        }

    function doContentTemplate(&$output, $item, $depth, $args, &$item_id, &$classes, &$title, &$megamenu_title, &$submenu_text, &$removed_args, &$original_title) {
        $megamenuContentPages = get_post_meta( $item_id, '_menu_item_dt_megamenu_content_pages', true );
?>
    <li id="menu-item-<?php echo $item_id; ?>" class="<?php echo implode(' ', $classes ); ?>">
        <dl class="menu-item-bar">
            <dt class="menu-item-handle">
                <span class="item-title"><span class="menu-item-title"><?php echo esc_html( $title ).' '.$megamenu_title; ?></span> <span class="is-submenu" <?php echo $submenu_text; ?>><?php _e( 'sub item' ); ?></span></span>
                        <span class="item-controls">
                            <span class="item-type"><?php echo esc_html( $item->type_label ); ?></span>
                            <span class="item-order hide-if-js">
                                <a href="<?php
                                echo wp_nonce_url(
                                    add_query_arg(
                                        array(
                                            'action' => 'move-up-menu-item',
                                            'menu-item' => $item_id,
                                        ),
                                        remove_query_arg($removed_args, admin_url( 'nav-menus.php' ) )
                                    ),
                                    'move-menu_item'
                                );
                                ?>" class="item-move-up"><abbr title="<?php esc_attr_e('Move up'); ?>">&#8593;</abbr></a>
                                |
                                <a href="<?php
                                echo wp_nonce_url(
                                    add_query_arg(
                                        array(
                                            'action' => 'move-down-menu-item',
                                            'menu-item' => $item_id,
                                        ),
                                        remove_query_arg($removed_args, admin_url( 'nav-menus.php' ) )
                                    ),
                                    'move-menu_item'
                                );
                                ?>" class="item-move-down"><abbr title="<?php esc_attr_e('Move down'); ?>">&#8595;</abbr></a>
                            </span>
                            <a class="item-edit" id="edit-<?php echo $item_id; ?>" title="<?php esc_attr_e('Edit Menu Item'); ?>" href="<?php
                            echo ( isset( $_GET['edit-menu-item'] ) && $item_id == $_GET['edit-menu-item'] ) ? admin_url( 'nav-menus.php' ) : add_query_arg( 'edit-menu-item', $item_id, remove_query_arg( $removed_args, admin_url( 'nav-menus.php#menu-item-settings-' . $item_id ) ) );
                            ?>"><?php _e( 'Edit Menu Item' ); ?></a>
                        </span>
            </dt>
        </dl>

        <div class="menu-item-settings" id="menu-item-settings-<?php echo $item_id; ?>">
            <?php if( 'custom' == $item->type ) : ?>

                <input type="hidden" id="edit-menu-item-url-<?php echo $item_id; ?>" class="widefat code edit-menu-item-url" name="menu-item-url[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->url ); ?>" />

            <?php endif; ?>

            <input type="hidden" id="edit-menu-item-title-<?php echo $item_id; ?>" class="widefat edit-menu-item-title" name="menu-item-title[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->title ); ?>" />

            <input type="hidden" id="edit-menu-item-attr-title-<?php echo $item_id; ?>" class="widefat edit-menu-item-attr-title" name="menu-item-attr-title[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->post_excerpt ); ?>" />


            <p class="field-css-classes description description-thin">
                <label for="edit-menu-item-classes-<?php echo $item_id; ?>">
                    <?php _e( 'CSS Classes (optional)' ); ?><br />
                    <input type="text" id="edit-menu-item-classes-<?php echo $item_id; ?>" class="widefat code edit-menu-item-classes" name="menu-item-classes[<?php echo $item_id; ?>]" value="<?php echo esc_attr( implode(' ', $item->classes ) ); ?>" />
                </label>
            </p>
            <input type="hidden" id="edit-menu-item-xfn-<?php echo $item_id; ?>" class="widefat code edit-menu-item-xfn" name="menu-item-xfn[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->xfn ); ?>" />

            <p class="field-content-pages description description-wide">
                <label for="edit-menu-item-dt-megamenu-content-pages-<?php echo $item_id ?>">
                    <?php _e('Content from pages' ); ?><br />

<?php               $optionpages = $this->get_wordpress_data('pages'); ?>
                <select id="edit-menu-item-dt-megamenu-content-pages-<?php echo $item_id; ?>" class="" name="menu-item-dt-megamenu-content-pages[<?php echo $item_id; ?>]">
                    <option value=""><?php _e( '-- Select a Page --' ); ?></option>
<?php               
                foreach($optionpages as $k => $v){
                    $selected = ($megamenuContentPages==$k) ? ' selected ':'';
                    echo '<option value="'.$k.'"'.$selected.'>'.$v.'</option>';
                }//foreach
?>
                </select>
                </label>
            </p>

            <p class="field-description description description-wide">
                <label for="edit-menu-item-description-<?php echo $item_id; ?>">
                    <?php _e( 'Content' ); ?><br />
                    <textarea id="edit-menu-item-description-<?php echo $item_id; ?>" class="widefat edit-menu-item-description" rows="3" cols="20" name="menu-item-description[<?php echo $item_id; ?>]"><?php echo esc_html( $item->post_content ); // textarea_escaped ?></textarea>
                    <span class="description"><?php _e('The description will be displayed in the menu if the current theme supports it.'); ?></span>
                </label>
            </p>

            <p class="field-move hide-if-no-js description description-wide">
                <label>
                    <span><?php _e( 'Move' ); ?></span>
                    <a href="#" class="menus-move-up"><?php _e( 'Up one' ); ?></a>
                    <a href="#" class="menus-move-down"><?php _e( 'Down one' ); ?></a>
                    <a href="#" class="menus-move-left"></a>
                    <a href="#" class="menus-move-right"></a>
                    <a href="#" class="menus-move-top"><?php _e( 'To the top' ); ?></a>
                </label>
            </p>

            <div class="menu-item-actions description-wide submitbox">
                <?php if( 'custom' != $item->type && $original_title !== false ) : ?>
                    <p class="link-to-original">
                        <?php printf( __('Original: %s'), '<a href="' . esc_attr( $item->url ) . '">' . esc_html( $original_title ) . '</a>' ); ?>
                    </p>
                <?php endif; ?>
                <a class="item-delete submitdelete deletion" id="delete-<?php echo $item_id; ?>" href="<?php
                echo wp_nonce_url(
                    add_query_arg(
                        array(
                            'action' => 'delete-menu-item',
                            'menu-item' => $item_id,
                        ),
                        admin_url( 'nav-menus.php' )
                    ),
                    'delete-menu_item_' . $item_id
                ); ?>"><?php _e( 'Remove' ); ?></a> <span class="meta-sep hide-if-no-js"> | </span> <a class="item-cancel submitcancel hide-if-no-js" id="cancel-<?php echo $item_id; ?>" href="<?php echo esc_url( add_query_arg( array( 'edit-menu-item' => $item_id, 'cancel' => time() ), admin_url( 'nav-menus.php' ) ) );
                ?>#menu-item-settings-<?php echo $item_id; ?>"><?php _e('Cancel'); ?></a>
            </div>

            <?php
            //
            //        megamenu custom code here
            //

            $item_id = $item->ID;
            $key = 'menu-item-dt-megamenu';

            echo '<input type="hidden" id="edit-'.$key.'-type-'.$item_id.'" class="widefat edit-menu-item-description" value="megamenu-content" name="'.$key.'-type'. '['. $item_id .']">';

            //
            //        end of megamenu custom code
            //
            ?>

            <input class="menu-item-data-db-id" type="hidden" name="menu-item-db-id[<?php echo $item_id; ?>]" value="<?php echo $item_id; ?>" />
            <input class="menu-item-data-object-id" type="hidden" name="menu-item-object-id[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->object_id ); ?>" />
            <input class="menu-item-data-object" type="hidden" name="menu-item-object[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->object ); ?>" />
            <input class="menu-item-data-parent-id" type="hidden" name="menu-item-parent-id[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->menu_item_parent ); ?>" />
            <input class="menu-item-data-position" type="hidden" name="menu-item-position[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->menu_order ); ?>" />
            <input class="menu-item-data-type" type="hidden" name="menu-item-type[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->type ); ?>" />
        </div><!-- .menu-item-settings-->
        <ul class="menu-item-transport"></ul>
        <?php
        }

    function doDefaultTemplate(&$output, $item, $depth, $args, &$item_id, &$classes, &$title, &$megamenu_title, &$submenu_text, &$removed_args, &$original_title) {
        $megamenuCheckboxValue = '';
        if ($depth == 0) {
            $item_id = $item->ID;
            $key = 'menu-item-dt-megamenu';

            $dt_megamenu=get_post_meta( $item_id, '_menu_item_dt_megamenu', true );
            $megamenuCheckboxValue = ($dt_megamenu=='active')?"active":"";
            
            $megamenuCheckboxLogo = (get_post_meta( $item_id, '_menu_item_dt_megamenu_logo', true )=='active')?"active":"";


            $megamenuDescriptionValue = get_post_meta( $item_id, '_menu_item_dt_megamenu_description', true );
            $megamenuBackgroundURL = get_post_meta( $item_id, '_menu_item_dt_megamenu_background_url', true );
            $megamenuBackgroundHorizontalPosition = get_post_meta( $item_id, '_menu_item_dt_megamenu_background_horizontal_position', true );
            $megamenuBackgroundVerticalPosition = get_post_meta( $item_id, '_menu_item_dt_megamenu_background_vertical_position', true );
            $megamenuBackgroundRepeat = get_post_meta( $item_id, '_menu_item_dt_megamenu_background_repeat', true );
            $megamenuWidthOptions = get_post_meta( $item_id, '_menu_item_dt_megamenu_width_options', true );
            $megamenuWidth = get_post_meta( $item_id, '_menu_item_dt_megamenu_width', true );

        }
        ?>
    <li id="menu-item-<?php echo $item_id; ?>" class="<?php echo implode(' ', $classes ); if($megamenuCheckboxValue != "") echo " dt-megamenu" //echo " megamenu-parent"  ?>">
        <dl class="menu-item-bar">
            <dt class="menu-item-handle">
                <span class="item-title"><span class="menu-item-title"><?php echo esc_html( $title ).' '.$megamenu_title; ?></span> <span class="is-submenu" <?php echo $submenu_text; ?>><?php _e( 'sub item' ); ?></span></span>
                        <span class="item-controls">
                            <span class="item-type"><?php echo esc_html( $item->type_label ); ?></span>
                            <span class="item-order hide-if-js">
                                <a href="<?php
                                echo wp_nonce_url(
                                    add_query_arg(
                                        array(
                                            'action' => 'move-up-menu-item',
                                            'menu-item' => $item_id,
                                        ),
                                        remove_query_arg($removed_args, admin_url( 'nav-menus.php' ) )
                                    ),
                                    'move-menu_item'
                                );
                                ?>" class="item-move-up"><abbr title="<?php esc_attr_e('Move up'); ?>">&#8593;</abbr></a>
                                |
                                <a href="<?php
                                echo wp_nonce_url(
                                    add_query_arg(
                                        array(
                                            'action' => 'move-down-menu-item',
                                            'menu-item' => $item_id,
                                        ),
                                        remove_query_arg($removed_args, admin_url( 'nav-menus.php' ) )
                                    ),
                                    'move-menu_item'
                                );
                                ?>" class="item-move-down"><abbr title="<?php esc_attr_e('Move down'); ?>">&#8595;</abbr></a>
                            </span>
                            <a class="item-edit" id="edit-<?php echo $item_id; ?>" title="<?php esc_attr_e('Edit Menu Item'); ?>" href="<?php
                            echo ( isset( $_GET['edit-menu-item'] ) && $item_id == $_GET['edit-menu-item'] ) ? admin_url( 'nav-menus.php' ) : add_query_arg( 'edit-menu-item', $item_id, remove_query_arg( $removed_args, admin_url( 'nav-menus.php#menu-item-settings-' . $item_id ) ) );
                            ?>"><?php _e( 'Edit Menu Item' ); ?></a>
                        </span>
            </dt>
        </dl>

        <div class="menu-item-settings" id="menu-item-settings-<?php echo $item_id; ?>">
            <?php if( 'custom' == $item->type ) : ?>
                <p class="field-url description description-wide">
                    <label for="edit-menu-item-url-<?php echo $item_id; ?>">
                        <?php _e( 'URL' ); ?><br />
                        <input type="text" id="edit-menu-item-url-<?php echo $item_id; ?>" class="widefat code edit-menu-item-url" name="menu-item-url[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->url ); ?>" />
                    </label>
                </p>
            <?php endif; ?>
            <p class="description description-thin">
                <label for="edit-menu-item-title-<?php echo $item_id; ?>">
                    <?php _e( 'Navigation Label' ); ?><br />
                    <input type="text" id="edit-menu-item-title-<?php echo $item_id; ?>" class="widefat edit-menu-item-title" name="menu-item-title[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->title ); ?>" />
                </label>
            </p>
            <p class="description description-thin">
                <label for="edit-menu-item-attr-title-<?php echo $item_id; ?>">
                    <?php _e( 'Title Attribute' ); ?><br />
                    <input type="text" id="edit-menu-item-attr-title-<?php echo $item_id; ?>" class="widefat edit-menu-item-attr-title" name="menu-item-attr-title[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->post_excerpt ); ?>" />
                </label>
            </p>
            <p class="field-link-target description">
                <label for="edit-menu-item-target-<?php echo $item_id; ?>">
                    <input type="checkbox" id="edit-menu-item-target-<?php echo $item_id; ?>" value="_blank" name="menu-item-target[<?php echo $item_id; ?>]"<?php checked( $item->target, '_blank' ); ?> />
                    <?php _e( 'Open link in a new window/tab' ); ?>
                </label>
            </p>
            <p class="field-css-classes description description-thin">
                <label for="edit-menu-item-classes-<?php echo $item_id; ?>">
                    <?php _e( 'CSS Classes (optional)' ); ?><br />
                    <input type="text" id="edit-menu-item-classes-<?php echo $item_id; ?>" class="widefat code edit-menu-item-classes" name="menu-item-classes[<?php echo $item_id; ?>]" value="<?php echo esc_attr( implode(' ', $item->classes ) ); ?>" />
                </label>
            </p>
            <p class="field-xfn description description-thin">
                <label for="edit-menu-item-xfn-<?php echo $item_id; ?>">
                    <?php _e( 'Link Relationship (XFN)' ); ?><br />
                    <input type="text" id="edit-menu-item-xfn-<?php echo $item_id; ?>" class="widefat code edit-menu-item-xfn" name="menu-item-xfn[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->xfn ); ?>" />
                </label>
            </p>
            <p class="field-description description description-wide">
                <label for="edit-menu-item-description-<?php echo $item_id; ?>">
                    <?php _e( 'Description' ); ?><br />
                    <textarea id="edit-menu-item-description-<?php echo $item_id; ?>" class="widefat edit-menu-item-description" rows="3" cols="20" name="menu-item-description[<?php echo $item_id; ?>]"><?php echo esc_html( $item->description ); // textarea_escaped ?></textarea>
                    <span class="description"><?php _e('The description will be displayed in the menu if the current theme supports it.'); ?></span>
                </label>
            </p>

            <?php
            //
            //        megamenu custom code here
            //
            if ($depth == 0) {
                if('custom'==$item->type) :
                    if ((wp_get_theme()->Name=='Cleanco')||(wp_get_theme()->Name=='Cleanco child theme')) :
                        if($megamenuCheckboxLogo == "active" ) $megamenuCheckboxLogo = "checked='checked'";
                        echo '<p class="description description-wide">
                                    <label for="edit-'.$key.'-logo-'.$item_id.'">
                                        <input type="checkbox" id="edit-'.$key.'-logo-'.$item_id.'" class="" value="active" name="'.$key . '-logo['. $item_id .']" '.$megamenuCheckboxLogo.' />&nbspLogo here
                                    </label>
                                </p>';
                    endif;
                endif; //if('custom'==$item->type)

                if($megamenuCheckboxValue == "active" ) $megamenuCheckboxValue = "checked='checked'";

                echo '<p class="description description-wide">
                                <label for="edit-megamenu-'.$item->ID.'">
                                    <input type="checkbox" id="edit-'.$key.'-'.$item_id.'" class="" value="active" name="'.$key . '['. $item_id .']" '.$megamenuCheckboxValue.' />&nbspActivate Mega Menu dropdown for this item
                                </label>
                            </p>
                            <p class="description description-wide" style="display: none;">
                                <label for="description-megamenu-'.$item->ID.'">Dropdown width (could be "full" or value in pixels)<br>
                                    <input type="text" id="edit-'.$key.'-description-'.$item_id.'" class="widefat edit-menu-item-description" value="'.$megamenuDescriptionValue.'" name="'.$key.'-description'. '['. $item_id .']">
                                </label>
                            </p>';
    
                echo '<p class="field-background-url description description-wide">
                    <label for="edit-'.$key.'-background-url-'.$item_id.'">'.
                         __( 'Background Image URL' ). '<br />
                        <input type="text" id="edit-'.$key.'-background-url-'.$item_id.'" class="widefat code edit-'.$key.'-background-url-'.$item_id.'" name="'.$key . '-background-url['.$item_id.']" value="'. $megamenuBackgroundURL.'" />
                    </label>
                </p>';

                echo '<p class="field-background-horizontal-position description description-wide">
                    <label for="edit-'.$key.'-background-horizontal-position-'.$item_id.'">'.
                         __( 'Background Horizontal Position (ex. left, right, center, 10px, -10px)' ). '<br />
                        <input type="text" id="edit-'.$key.'-background-horizontal-position-'.$item_id.'" class="widefat code edit-'.$key.'-background-horizontal-position-'.$item_id.'" name="'.$key . '-background-horizontal-position['.$item_id.']" value="'. $megamenuBackgroundHorizontalPosition.'" />
                    </label>
                </p>';

                echo '<p class="field-background-vertical-position description description-wide">
                    <label for="edit-'.$key.'-background-vertical-position-'.$item_id.'">'.
                         __( 'Background Vertical Position (ex. top, bottom, center, 10px, -10px)' ). '<br />
                        <input type="text" id="edit-'.$key.'-background-vertical-position-'.$item_id.'" class="widefat code edit-'.$key.'-background-vertical-position-'.$item_id.'" name="'.$key . '-background-vertical-position['.$item_id.']" value="'. $megamenuBackgroundVerticalPosition.'" />
                    </label>
                </p>';

                echo '<p class="field-background-repeat description description-wide">
                    <label for="edit-'.$key.'-background-repeat-'.$item_id.'">'.
                         __( 'Background Repeat' ). '<br />
                        <select id="edit-'.$key.'-background-repeat-'.$item_id.'" class="" value="active" name="'.$key . '-background-repeat['. $item_id .']">
                            <option value="">Select Background Repeat</option>
                            <option value="no-repeat" '. __(($megamenuBackgroundRepeat=="no-repeat") ? 'selected' : '') .'>No Repeat</option>
                            <option value="repeat" '. __(($megamenuBackgroundRepeat=="repeat") ? 'selected' : '') .'>Repeat</option>
                            <option value="repeat-x" '. __(($megamenuBackgroundRepeat=="repeat-x") ? 'selected' : '') .'>Repeat X</option>
                            <option value="repeat-y" '. __(($megamenuBackgroundRepeat=="repeat-y") ? 'selected' : '') .'>Repeat Y</option>
                        </select>
                    </label>
                </p>';

                echo '<p class="field-width-options description description-wide">
                    <label for="edit-'.$key.'-width-options-'.$item_id.'">'.
                         __( 'Megamenu Width Options' ). '<br />
                        <select id="edit-'.$key.'-width-options-'.$item_id.'" class="" name="'.$key . '-width-options['. $item_id .']">
                            <option value="">Megamenu Width Options</option>
                            <option value="full-dt-megamenu" '. __(($megamenuWidthOptions=="full-dt-megamenu" || $megamenuWidthOptions=="") ? 'selected' : '') .'>Full Width</option>
                            <option value="auto-dt-megamenu" '. __(($megamenuWidthOptions=="auto-dt-megamenu") ? 'selected' : '') .'>Auto Width</option>
                            <option value="dt-megamenu-width-set sticky-left" '. __(($megamenuWidthOptions=="dt-megamenu-width-set sticky-left") ? 'selected' : '') .'>Custom Width</option>
                        </select>
                    </label>
                </p>';

                echo '<p class="field-width description description-wide">
                    <label for="edit-'.$key.'-width-'.$item_id.'">'.
                         __( 'Set width if you choose "Custom Width" Options (ex. 80%, 70px)' ). '<br />
                        <input type="text" id="edit-'.$key.'-width-'.$item_id.'" class="widefat code edit-'.$key.'-width-'.$item_id.'" name="'.$key . '-width['.$item_id.']" value="'. $megamenuWidth.'" />
                    </label>
                </p>';
            };
            //
            //        end of megamenu custom code
            //
            ?>


            <p class="field-move hide-if-no-js description description-wide">
                <label>
                    <span><?php _e( 'Move' ); ?></span>
                    <a href="#" class="menus-move-up"><?php _e( 'Up one' ); ?></a>
                    <a href="#" class="menus-move-down"><?php _e( 'Down one' ); ?></a>
                    <a href="#" class="menus-move-left"></a>
                    <a href="#" class="menus-move-right"></a>
                    <a href="#" class="menus-move-top"><?php _e( 'To the top' ); ?></a>
                </label>
            </p>

            <div class="menu-item-actions description-wide submitbox">
                <?php if( 'custom' != $item->type && $original_title !== false ) : ?>
                    <p class="link-to-original">
                        <?php printf( __('Original: %s'), '<a href="' . esc_attr( $item->url ) . '">' . esc_html( $original_title ) . '</a>' ); ?>
                    </p>
                <?php endif; ?>
                <a class="item-delete submitdelete deletion" id="delete-<?php echo $item_id; ?>" href="<?php
                echo wp_nonce_url(
                    add_query_arg(
                        array(
                            'action' => 'delete-menu-item',
                            'menu-item' => $item_id,
                        ),
                        admin_url( 'nav-menus.php' )
                    ),
                    'delete-menu_item_' . $item_id
                ); ?>"><?php _e( 'Remove' ); ?></a> <span class="meta-sep hide-if-no-js"> | </span> <a class="item-cancel submitcancel hide-if-no-js" id="cancel-<?php echo $item_id; ?>" href="<?php echo esc_url( add_query_arg( array( 'edit-menu-item' => $item_id, 'cancel' => time() ), admin_url( 'nav-menus.php' ) ) );
                ?>#menu-item-settings-<?php echo $item_id; ?>"><?php _e('Cancel'); ?></a>
            </div>

            <input class="menu-item-data-db-id" type="hidden" name="menu-item-db-id[<?php echo $item_id; ?>]" value="<?php echo $item_id; ?>" />
            <input class="menu-item-data-object-id" type="hidden" name="menu-item-object-id[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->object_id ); ?>" />
            <input class="menu-item-data-object" type="hidden" name="menu-item-object[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->object ); ?>" />
            <input class="menu-item-data-parent-id" type="hidden" name="menu-item-parent-id[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->menu_item_parent ); ?>" />
            <input class="menu-item-data-position" type="hidden" name="menu-item-position[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->menu_order ); ?>" />
            <input class="menu-item-data-type" type="hidden" name="menu-item-type[<?php echo $item_id; ?>]" value="<?php echo esc_attr( $item->type ); ?>" />
        </div><!-- .menu-item-settings-->
        <ul class="menu-item-transport"></ul>
    <?php

    }

    function get_wordpress_data($type = false, $args = array()) {
        $wpdata = "";
        if ( !empty($type) ) {

            //$wpdata = apply_filters( 'redux/options/'.$this->args['opt_name'].'/wordpress_data/'.$type.'/', $wpdata ); // REMOVE LATER
            //$wpdata = apply_filters( 'redux/options/'.$this->args['opt_name'].'/data/'.$type, $wpdata ); 

            /**
                Use data from Wordpress to populate options array
            **/
            if (!empty($type) && empty($wpdata)) {
                if (empty($args)) {
                    $args = array();
                }
                $wpdata = array();
                $args = wp_parse_args($args, array());  
                if ($type == "categories" || $type == "category") {
                    $cats = get_categories($args); 
                    if (!empty($cats)) {        
                        foreach ( $cats as $cat ) {
                            $wpdata[$cat->term_id] = $cat->name;
                        }//foreach
                    } // If
                } else if ($type == "menus" || $type == "menu") {
                    $menus = wp_get_nav_menus($args);
                    if(!empty($menus)) {
                        foreach ($menus as $item) {
                            $wpdata[$item->term_id] = $item->name;
                        }//foreach
                    }//if
                } else if ($type == "pages" || $type == "page") {
                    $pages = get_pages($args); 
                    if (!empty($pages)) {
                        foreach ( $pages as $page ) {
                            $wpdata[$page->ID] = $page->post_title;
                        }//foreach
                    }//if
                } else if ($type == "terms" || $type == "term") {
                    $taxonomies = $args['taxonomies'];
                    unset($args['taxonomies']);
                    if (empty($args)) {
                        $args = array();
                    }
                    if (empty($args['args'])) {
                        $args['args'] = array();
                    }                        
                    $terms = get_terms($taxonomies, $args['args']); // this will get nothing
                    if (!empty($terms)) {       
                        foreach ( $terms as $term ) {
                            $wpdata[$term->term_id] = $term->name;
                        }//foreach
                    } // If
                } else if ($type == "posts" || $type == "post") {
                    $posts = get_posts($args); 
                    if (!empty($posts)) {
                        foreach ( $posts as $post ) {
                            $wpdata[$post->ID] = $post->post_title;
                        }//foreach
                    }//if
                } else if ($type == "post_type" || $type == "post_types") {
                    global $wp_post_types;
                    $defaults = array(
                        'public' => true,
                        'publicly_queryable' => true,
                        'exclude_from_search' => false,
                        '_builtin' => false,
                    );
                    $args = wp_parse_args( $args, $defaults );
                    $output = 'names';
                    $operator = 'and';
                    $post_types = get_post_types($args, $output, $operator);
                    $post_types['page'] = 'page';
                    $post_types['post'] = 'post';
                    ksort($post_types);

                    foreach ( $post_types as $name => $title ) {
                        if ( isset($wp_post_types[$name]->labels->menu_name) ) {
                            $wpdata[$name] = $wp_post_types[$name]->labels->menu_name;
                        } else {
                            $wpdata[$name] = ucfirst($name);
                        }
                    }
                } else if ($type == "tags" || $type == "tag") { // NOT WORKING!
                    $tags = get_tags($args); 
                    if (!empty($tags)) {
                        foreach ( $tags as $tag ) {
                            $wpdata[$tag->term_id] = $tag->name;
                        }//foreach
                    }//if
                } else if ($type == "menu_location" || $type == "menu_locations") {
                    global $_wp_registered_nav_menus;
                    foreach($_wp_registered_nav_menus as $k => $v) {
                        $wpdata[$k] = $v;
                    }
                }//if
                else if ($type == "elusive-icons" || $type == "elusive-icon" || $type == "elusive" || 
                         $type == "font-icon" || $type == "font-icons" || $type == "icons") {
                    $font_icons = apply_filters('redux-font-icons',array()); // REMOVE LATER
                    $font_icons = apply_filters('redux/font-icons',$font_icons);
                    foreach($font_icons as $k) {
                        $wpdata[$k] = $k;
                    }
                }else if ($type == "roles") {
                    /** @global WP_Roles $wp_roles */
                    global $wp_roles;
                    $wpdata = $wp_roles->get_names();
                }else if ($type == "sidebars" || $type == "sidebar") {
                    /** @global array $wp_registered_sidebars */
                    global $wp_registered_sidebars;
                    foreach ($wp_registered_sidebars as $key=>$value) {
                        $wpdata[$key] = $value['name'];
                    }
                }else if ($type == "capabilities") {
                    /** @global WP_Roles $wp_roles */
                    global $wp_roles;
                    foreach( $wp_roles->roles as $role ){
                        foreach( $role['capabilities'] as $key => $cap ){
                            $wpdata[$key] = ucwords(str_replace('_', ' ', $key));
                        }
                    }
                }else if ($type == "callback") {
                    $wpdata = call_user_func($args[0]);
                }//if           
            }//if
        }//if

        return $wpdata;
    }       

}
?>