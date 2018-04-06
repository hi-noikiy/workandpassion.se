<?php


class DethemeReduxFramework_typography extends DethemeReduxFramework{

    /**
     * Field Constructor.
     *
     * Required - must call the parent constructor, then assign field and value to vars, and obviously call the render field function
     *
     * @since ReduxFramework 1.0.0
     */
    function __construct( $field = array(), $value ='', $parent ) {
    
        $this->args=$parent->args;
        $this->parent = $parent;
        $this->field = $field;
        $this->value = $value;

    
    }

    /**
     * Field Render Function.
     *
     * Takes the vars and outputs the HTML for the field in the settings
     *
     * @since ReduxFramework 1.0.0
     */
    function render(){   

        global $wp_filesystem;

        // Initialize the Wordpress filesystem, no more using file_put_contents function
        if (empty($wp_filesystem)) 
            return false;

        // No errors please
        $defaults = array(
            'font-family' => true,
            'font-size' => true,
            'subsets' => true,
            'font-weight' => true,
            'font-style' => true,
            'font-backup' => false,
            'color' => true,
            'preview' => true,
            'line-height' => true,
            'word-spacing' => false,
            'letter-spacing' => false,
            'google' => true,
            'update_weekly' => false // Enable to force updates of Google Fonts to be weekly
        );
        $this->field = wp_parse_args( $this->field, $defaults );

        $defaults = array(
            'font-family'=>'',
            'font-options'=>'',
            'font-backup'=>'',
            'line-height'=>'',
            'word-spacing' => '',
            'letter-spacing' => '',
            'subsets'=>'',
            'google'=>false,
            'font-script'=>'',
            'font-weight'=>'',
            'font-style'=>'',
            'color'=>'',
            'font-size'=>'',
        );

        $this->value = wp_parse_args( $this->value, $defaults );

        if (empty($this->field['units']) && !empty($this->field['default']['units'])) {
            $this->field['units'] = $this->field['default']['units'];
        }          
        if ( empty( $this->field['units'] ) || !in_array( $this->field['units'], array( 'px', 'em', 'rem', '%' ) ) ) {
            $this->field['units'] = 'px';
        }
        $unit = $this->field['units'];


    if ($this->field['font-family'] == true):
        
          echo '<div id="'.$this->field['id'].'" class="redux-typography-container" data-id="'.$this->field['id'].'" data-units="'.$unit.'">';

              /**
              Font Family
               **/
            
              if ( filter_var($this->value['google'], FILTER_VALIDATE_BOOLEAN) ) {
                $fontFamily = explode(', ', $this->value['font-family'],2);
                if (empty($fontFamily[0]) && !empty($fontFamily[1])) {
                  $fontFamily[0] = $fontFamily[1];
                  $fontFamily[1] = "";
                }       
              }

              if (!isset($fontFamily)) {
                $fontFamily = array();
                $fontFamily[0] = $this->value['font-family'];
                $fontFamily[1] = "";
              }

              echo '<input type="hidden" class="redux-typography-font-family '.$this->field['class'].'" name="'.$this->args['opt_name'].'['.$this->field['id'].'][font-family]" value="'.$this->value['font-family'].'" data-id="'.$this->field['id'].'"  />';
              echo '<input type="hidden" class="redux-typography-font-options '.$this->field['class'].'" name="'.$this->args['opt_name'].'['.$this->field['id'].'][font-options]" value="'.$this->value['font-options'].'" data-id="'.$this->field['id'].'"  />';
              echo '<div class="select_wrapper typography-family" style="width: 220px; margin-right: 5px;">';
              echo '<select data-placeholder="'.__('Font family','redux-framework').'" class="redux-typography redux-typography-family '.$this->field['class'].'" id="'.$this->field['id'].'-family" data-id="'.$this->field['id'].'" data-value="'.$fontFamily[0].'">';
              echo '<option data-google="false" data-details="" value=""></option>';

              if ( isset($this->field['update_weekly']) && $this->field['update_weekly'] == true && $this->field['google'] == true && !empty( $this->args['google_api_key'] ) ) {

                  if( file_exists( DethemeReduxFramework::$_dir.'inc/fields/typography/googlefonts.html' )) {
                    // Keep the fonts updated weekly
                    $weekback = strtotime( date('jS F Y', time() + (60 * 60 * 24 * -7) ) );
                    $last_updated = filemtime( DethemeReduxFramework::$_dir.'inc/fields/typography/googlefonts.html' );
                    if ( $last_updated < $weekback ) {
                      unlink( DethemeReduxFramework::$_dir.'inc/fields/typography/googlefonts.html' );
                      unlink( DethemeReduxFramework::$_dir.'inc/fields/typography/googlefonts.json' );
                    }                 
                  }
              }
              if (empty($this->field['fonts'])) {
                  $this->field['fonts'] = array(
                      "Arial, Helvetica, sans-serif" => "Arial, Helvetica, sans-serif",
                      "'Arial Black', Gadget, sans-serif" => "'Arial Black', Gadget, sans-serif",
                      "'Bookman Old Style', serif" => "'Bookman Old Style', serif",
                      "'Comic Sans MS', cursive" => "'Comic Sans MS', cursive",
                      "Courier, monospace" => "Courier, monospace",
                      "Garamond, serif" => "Garamond, serif",
                      "Georgia, serif" => "Georgia, serif",
                      "Impact, Charcoal, sans-serif" => "Impact, Charcoal, sans-serif",
                      "'Lucida Console', Monaco, monospace" => "'Lucida Console', Monaco, monospace",
                      "'Lucida Sans Unicode', 'Lucida Grande', sans-serif" => "'Lucida Sans Unicode', 'Lucida Grande', sans-serif",
                      "'MS Sans Serif', Geneva, sans-serif" => "'MS Sans Serif', Geneva, sans-serif",
                      "'MS Serif', 'New York', sans-serif" =>"'MS Serif', 'New York', sans-serif",
                      "'Palatino Linotype', 'Book Antiqua', Palatino, serif" => "'Palatino Linotype', 'Book Antiqua', Palatino, serif",
                      "Tahoma, Geneva, sans-serif" =>"Tahoma, Geneva, sans-serif",
                      "'Times New Roman', Times, serif" => "'Times New Roman', Times, serif",
                      "'Trebuchet MS', Helvetica, sans-serif" => "'Trebuchet MS', Helvetica, sans-serif",
                      "Verdana, Geneva, sans-serif" => "Verdana, Geneva, sans-serif",
                  );
              }

              // Standard sizes for normal fonts
              $font_sizes = urlencode( json_encode( array( '400'=>'Normal 400', '700'=>'Bold 700', '400italic'=>'Normal 400 Italic', '700italic'=>'Bold 700 Italic' ) ) );
              if ( $this->field['google'] == true && !empty( $this->args['google_api_key'] ) ) {
                echo '<optgroup label="'.__('Standard Fonts', 'redux-framework').'">';  
              }
              foreach ($this->field['fonts'] as $i=>$family) {
                  echo '<option data-google="false" data-details="'.$font_sizes.'" value="'. $i .'"' . selected($this->value['font-family'], $i, false) . '>'. $family .'</option>';
              }
              if ($this->field['google'] == true && !empty( $this->args['google_api_key'] ) ) {
                  echo '</optgroup>';

                  if( !file_exists( DethemeReduxFramework::$_dir.'inc/fields/typography/googlefonts.html' ) ) {
                      $this->getGoogleFonts();
                  }

                  if ( !isset( $this->googleFontHTML ) && !empty( $this->googleFontHTML ) ) {
                    echo $this->googleFontHTML;
                  } elseif( file_exists( DethemeReduxFramework::$_dir.'inc/fields/typography/googlefonts.html' )) {

                    $googleHTML = $wp_filesystem->get_contents( DethemeReduxFramework::$_dir.'inc/fields/typography/googlefonts.html' );

                    if(!$googleHTML){

                    $fp = fopen(DethemeReduxFramework::$_dir.'inc/fields/typography/googlefonts.html', 'r');
                    if ( ! $fp )
                      return false;

                    $contents = '';

                    while ( ! feof($fp) )
                      $contents .= fread($fp, 8192);

                    fclose($fp);

                    $googleHTML=$contents;

                    }


                    $this->googleFontHTML = $googleHTML;
                    echo $googleHTML;
                  }
              }

              echo '</select></div>';

              if ($this->field['google'] == true) { 
                // Set a flag so we know to set a header style or not
                  echo '<input type="hidden" class="redux-typography-google'.$this->field['class'].'" id="'.$this->field['id'].'-google" name="'.$this->args['opt_name'].'['.$this->field['id'].'][google]" type="text" value="'. $this->field['google'] .'" data-id="'.$this->field['id'].'" />';            
              }

            endif;



            /**
            Font Style/Weight
             **/
            if ($this->field['font-style'] == true || $this->field['font-weight'] == true):
                echo '<div class="select_wrapper typography-style" original-title="'.__('Font style','redux-framework').'">';
              $style = $this->value['font-weight'].$this->value['font-style'];
                echo '<input type="hidden" class="typography-font-weight" name="'.$this->args['opt_name'].'['.$this->field['id'].'][font-weight]" val="'.$this->value['font-weight'].'" data-id="'.$this->field['id'].'"  /> ';
                echo '<input type="hidden" class="typography-font-style" name="'.$this->args['opt_name'].'['.$this->field['id'].'][font-style]" val="'.$this->value['font-style'].'" data-id="'.$this->field['id'].'"  /> ';
                echo '<select data-placeholder="'.__('Style','redux-framework').'" class="redux-typography redux-typography-style select'.$this->field['class'].'" original-title="'.__('Font style','redux-framework').'" id="'. $this->field['id'].'_style" data-id="'.$this->field['id'].'" data-value="'.$style.'">';
                if (empty($this->value['subset'])) {
                    echo '<option value=""></option>';
                }
                $nonGStyles = array('200'=>'Lighter','400'=>'Normal','700'=>'Bold','900'=>'Bolder');
                if (isset($gfonts[$this->value['font-family']])) {
                    foreach ($gfonts[$this->value['font-family']]['variants'] as $v) {
                        echo '<option value="'. $v['id'] .'" ' . selected($this->value['subset'], $v['id'], false) . '>'. $v['name'] .'</option>';
                    }
                } else {
                    foreach ($nonGStyles as $i=>$style){
                        if (!isset($this->value['subset']))
                            $this->value['subset'] = false;
                        echo '<option value="'. $i .'" ' . selected($this->value['subset'], $i, false) . '>'. $style .'</option>';
                    }
                }

                echo '</select></div>';

            endif;


            /**
            Font Script
             **/
            if ($this->field['subsets'] == true && $this->field['google'] == true):
                echo '<div class="select_wrapper typography-script tooltip" original-title="'.__('Font subsets','redux-framework').'">';
                echo '<select data-placeholder="'.__('Subsets','redux-framework').'" class="redux-typography redux-typography-subsets'.$this->field['class'].'" original-title="'.__('Font script','redux-framework').'"  id="'.$this->field['id'].'-subsets" name="'.$this->args['opt_name'].'['.$this->field['id'].'][subsets]" data-value="'.$this->value['subsets'].'" data-id="'.$this->field['id'].'" >';
                if (empty($this->value['subsets'])) {
                    echo '<option value=""></option>';
                }
                if (isset($gfonts[$this->value['font-family']])) {
                    foreach ($gfonts[$this->value['font-family']]['subsets'] as $v) {
                        echo '<option value="'. $v['id'] .'" ' . selected($this->value['subset'], $v['id'], false) . '>'. $v['name'] .'</option>';
                    }
                }
                echo '</select></div>';

            endif;


            /**
            Font Size
             **/
            if ($this->field['font-size'] == true):
                echo '<div class="input-append"><input type="text" class="span2 redux-typography-size mini'.$this->field['class'].'" placeholder="'.__('Size','redux-framework').'" id="'.$this->field['id'].'-size" name="'.$this->args['opt_name'].'['.$this->field['id'].'][font-size]" value="'.str_replace($unit, '', $this->value['font-size']).'" data-value="'.str_replace($unit, '', $this->value['font-size']).'"><span class="add-on">'.$unit.'</span></div>';
              echo '<input type="hidden" class="typography-font-size" name="'.$this->args['opt_name'].'['.$this->field['id'].'][font-size]" value="'.$this->value['font-size'].'" data-id="'.$this->field['id'].'"  />';
            endif;


            /**
            Line Height
             **/
            if ($this->field['line-height'] == true):
                echo '<div class="input-append"><input type="text" class="span2 redux-typography redux-typography-height mini'.$this->field['class'].'" placeholder="'.__('Height','redux-framework').'" id="'.$this->field['id'].'-height" value="'.str_replace($unit, '', $this->value['line-height']).'" data-value="'.str_replace($unit, '', $this->value['line-height']).'"><span class="add-on">'.$unit.'</span></div>';
                echo '<input type="hidden" class="typography-line-height" name="'.$this->args['opt_name'].'['.$this->field['id'].'][line-height]" value="'.$this->value['line-height'].'" data-id="'.$this->field['id'].'"  />';
            endif;

            /**
            Word Spacing
             **/
            if ($this->field['word-spacing'] == true):
                echo '<div class="input-append"><input type="text" class="span2 redux-typography redux-typography-word mini'.$this->field['class'].'" placeholder="'.__('Word Spacing','redux-framework').'" id="'.$this->field['id'].'-word" value="'.str_replace($unit, '', $this->value['word-spacing']).'" data-value="'.str_replace($unit, '', $this->value['word-spacing']).'"><span class="add-on">'.$unit.'</span></div>';
                echo '<input type="hidden" class="typography-word-spacing" name="'.$this->args['opt_name'].'['.$this->field['id'].'][word-spacing]" value="'.$this->value['word-spacing'].'" data-id="'.$this->field['id'].'"  />';
            endif;

            /**
            Letter Spacing
             **/
            if ($this->field['letter-spacing'] == true):
                echo '<div class="input-append"><input type="text" class="span2 redux-typography redux-typography-letter mini'.$this->field['class'].'" placeholder="'.__('Letter Spacing','redux-framework').'" id="'.$this->field['id'].'-letter" value="'.str_replace($unit, '', $this->value['letter-spacing']).'" data-value="'.str_replace($unit, '', $this->value['letter-spacing']).'"><span class="add-on">'.$unit.'</span></div>';
              echo '<input type="hidden" class="typography-letter-spacing" name="'.$this->args['opt_name'].'['.$this->field['id'].'][letter-spacing]" value="'.$this->value['letter-spacing'].'" data-id="'.$this->field['id'].'"  />';
            endif;

            
            /**
            Backup Font
             **/
            if ($this->field['font-family'] == true && $this->field['google'] == true) { 
              // Set a flag so we know to set a header style or not
                echo '<input type="hidden" class="redux-typography-google'.$this->field['class'].'" id="'.$this->field['id'].'-google" name="'.$this->args['opt_name'].'['.$this->field['id'].'][google]" type="text" value="'. $this->field['google'] .'" data-id="'.$this->field['id'].'" data-id="'.$this->field['id'].'"  />';            
            
                if ($this->field['font-backup'] == true) {
                  echo '<div class="select_wrapper typography-family-backup" style="width: 220px; margin-right: 5px;">';
                  echo '<select data-placeholder="'.__('Backup Font Family','redux-framework').'" name="'.$this->args['opt_name'].'['.$this->field['id'].'][font-backup]" class="redux-typography redux-typography-family-backup '.$this->field['class'].'" id="'.$this->field['id'].'-family-backup" data-id="'.$this->field['id'].'" data-value="'.$this->value['font-backup'].'">';
                  echo '<option data-google="false" data-details="" value=""></option>';
                  foreach ($this->field['fonts'] as $i=>$family) {
                      echo '<option data-google="true" data-details="'.$font_sizes.'" value="'. $i .'"' . selected($this->value['font-backup'], $i, false) . '>'. $family .'</option>';
                  }
                  echo '</select></div>';               
                }
            }

            /**
            Font Color
             **/
            if ($this->field['color'] == true):
                $default = "";
                if (empty($this->field['default']['color']) && !empty($this->field['color'])) {
                    $default = $this->value['color'];
                } else if (!empty($this->field['default']['color'])) {
                    $default = $this->field['default']['color'];
                }
                echo '<div id="' . $this->field['id'] . '_color_picker" class="colorSelector typography-color"><div style="background-color: '.$this->value['color'].'"></div></div>';
                echo '<input data-default-color="'.$default.'" class="redux-color redux-typography-color'.$this->field['class'].'" original-title="'.__('Font color','redux-framework').'" id="'.$this->field['id'].'-color" name="'.$this->args['opt_name'].'['.$this->field['id'].'][color]" type="text" value="'. $this->value['color'] .'" data-id="'.$this->field['id'].'" />';
            endif;

            /**
            Font Preview
             **/
            if (!isset( $this->field['preview'] ) || $this->field['preview'] != false):
                if(isset($value['preview']['text'])){
                    $g_text = $value['preview']['text'];
                } else {
                    $g_text = '0123456789 ABCDEFGHIJKLMNOPQRSTUVWXYZ abcdefghijklmnopqrstuvwxyz';
                }
                if(isset($value['preview']['font-size'])) {
                    $g_size = 'style="font-size: '. $value['preview']['font-size'] .';"';
                } else {
                    $g_size = '';
                }

                echo '<p class="clear '.$this->field['id'].'_previewer typography-preview" '. $g_size .'>'. $g_text .'</p>';
            echo "</div>";
        endif;

    }//function

    /**
     * Enqueue Function.
     *
     * If this field requires any scripts, or css define this function and register/enqueue the scripts/css
     *
     * @since ReduxFramework 1.0.0
     */
    function enqueue(){

        wp_enqueue_script(
            'redux-field-color-js', 
            DethemeReduxFramework::$_url . 'inc/fields/color/field_color.js', 
            array( 'jquery', 'wp-color-picker' ),
            time(),
            true
        );

        wp_enqueue_style(
            'redux-field-color-css', 
            DethemeReduxFramework::$_url . 'inc/fields/color/field_color.css', 
            time(),
            true
        );

        wp_enqueue_script(
            'redux-field-typography-js',
            DethemeReduxFramework::$_url.'inc/fields/typography/field_typography.js',
            array('jquery', 'wp-color-picker', 'redux-field-color-js', 'select2-js'),
            time(),
            true
        );

        wp_enqueue_style(
            'redux-field-typography-css',
            DethemeReduxFramework::$_url.'inc/fields/typography/field_typography.css',
            time(),
            true
        );


    }//function

    /**
     * makeGoogleWebfontLink Function.
     *
     * Creates the google fonts link.
     *
     * @since ReduxFramework 3.0.0
     */
    function makeGoogleWebfontLink($fonts) {
      $link = "";
      $subsets = array();
      foreach($fonts as $family=>$font) {
        if (!empty($link)) {
          $link .= "|"; // Append a new font to the string
        }
        $link .= $family;

        if ( !empty( $font['font-style'] ) ) {
            $link .= ':';
            if ( !empty($font['all-styles']) ) {
                $link .= implode(',', $font['all-styles']);
            } else if ( !empty($font['font-style'] ) ) {
                $link .= implode(',', $font['font-style']);
            }
        }
        if ( !empty( $font['subset'] ) ) {
          foreach($font['subset'] as $subset) {
            if ( !in_array( $subset, $subsets) ) {
              array_push($subsets, $subset);
            }  
          }
          
        }
      }
      if (!empty($subsets)) {
        $link .= "&amp;subset=".implode(',', $subsets);
      }

      return '//fonts.googleapis.com/css?family='.$link;

    }

    function output() {
      global $wp_styles;

      if ( !empty( $this->parent->fieldTypographySet ) ) {
        return; // We only run this function once!
      }



      $this->parent->fieldTypographySet = true;

      $outCSS = "";
      $fonts = array();
      foreach( $this->parent->sections as $section ) {

        if( isset( $section['fields'] ) ) {
          foreach( $section['fields'] as $field ) {
            if( isset( $field['type'] ) && $field['type'] == "typography" ) {

              $font = $this->parent->options[$field['id']];
              //echo $font['font-family'];
              if ( !empty( $font['font-family'] ) && !empty( $font['font-backup'] ) ) {
                $font['font-family'] = str_replace( ', '.$font['font-backup'], '', $font['font-family'] );  
              }
              if ( !empty( $field['output'] ) ) : // Don't create dynamic CSS if output array is not set
                $keys = implode(",", $field['output']);
                $newOutCSS = '';
                if (!empty($font)) {
                    foreach( $font as $key=>$value) {
                        if ($key == 'font-options') {
                            continue;
                        }
                      if (empty($value) && in_array($key, array('font-weight', 'font-style'))) {
                        $value = "normal";
                      }
                      if ( $key == "google" || $key == "subsets" || $key == "font-backup" || empty( $value ) ) {
                          continue;
                      }
                      $newOutCSS .= $key.':'.$value.';';
                    }                    
                }

                
                if ( !empty( $newOutCSS) ) {
                  $outCSS .= $keys."{".$newOutCSS.'}';
                }                 
              endif;
              

              // Google only stuff!
              if ( !empty( $this->parent->args['google_api_key'] ) && !empty($font['font-family']) && !empty($this->parent->options[$field['id']]['google']) && filter_var($this->parent->options[$field['id']]['google'], FILTER_VALIDATE_BOOLEAN) ) {

                if ( !empty( $font['font-backup'] ) && !empty( $font['font-family'] ) ) {
                  $font['font-family'] = str_replace( ', '.$font['font-backup'], '', $font['font-family'] );
                }
                $family = $font['font-family'];
                $font['font-family'] = str_replace( ' ', '+', $font['font-family'] );
                if ( empty( $fonts[$font['font-family']] ) ) {
                  $fonts[$font['font-family']] = array();  
                }
                if (isset($this->field['all_styles'])) {
                    if ( !isset( $font['font-options'] ) ) {
                        $this->getGoogleArray();
                        if ( isset( $this->parent->googleArray ) && !empty( $this->parent->googleArray ) && isset( $this->parent->googleArray[$family] ) ) {
                            $font['font-options'] = $this->parent->googleArray[$family];
                        }
                    } else {
                        $font['font-options'] = json_decode($font['font-options'], true);
                    }
                }
                
                if ( isset( $font['font-options'] ) && !empty( $font['font-options'] ) && isset( $this->field['all_styles'] ) && filter_var( $this->field['all_styles'], FILTER_VALIDATE_BOOLEAN ) ) {
                    if ( isset( $font['font-options'] ) && !empty( $font['font-options']['variants'] ) ) {
                      if ( !isset( $fonts[$font['font-family']]['all-styles'] ) || empty( $fonts[$font['font-family']]['all-styles'] ) ) {
                          $fonts[$font['font-family']]['all-styles'] = array();
                          foreach($font['font-options']['variants'] as $variant) {
                            $fonts[$font['font-family']]['all-styles'][] = $variant['id'];
                          }                        
                      }
                    }                    
                } 
                if ( !empty( $font['font-weight'] ) ) {
                  if ( empty( $fonts[$font['font-family']]['font-weight'] ) || !in_array( $font['font-weight'], $fonts[$font['font-family']]['font-weight'] ) ) {
                    $style = $font['font-weight'];
                  }
                  if ( !empty( $font['font-style'] ) ) {
                      $style .= $font['font-style'];
                  }                        
                  if ( empty( $fonts[$font['font-family']]['font-style'] ) || !in_array( $style, $fonts[$font['font-family']]['font-style'] ) ) {
                    $fonts[$font['font-family']]['font-style'][] = $style;
                  }                      
                }
                if ( !empty( $font['subsets'] ) ) {
                  if ( empty( $fonts[$font['font-family']]['subset'] ) || !in_array( $font['subsets'], $fonts[$font['font-family']]['subset'] ) ) {
                    $fonts[$font['font-family']]['subset'][] = $font['subsets'];
                  }                      
                }   
              }                   
            }
          }
        }
      } // Typography not set

      $version = '';
      if (!empty($this->parent->options['REDUX_last_saved'])) {
        $version = $this->parent->options['REDUX_last_saved'];
      }

      if ( !empty( $fonts ) && (bool)$this->parent->args['output']) {
        wp_register_style( 'redux-google-fonts', $this->makeGoogleWebfontLink( $fonts ), '', $version );
        wp_enqueue_style( 'redux-google-fonts' ); 
      }
      if ( !empty($outCSS ) ) {
        $this->parent->outputCSS .= $outCSS;  
      }
    }


    /**
    *
    *   Construct the google array from the stored JSON/HTML
    * 
    */
    function getGoogleArray() {

        global $wp_filesystem;

        // Initialize the Wordpress filesystem, no more using file_put_contents function
        if (empty($wp_filesystem)) 
            return false;


        if ( isset( $this->parent->googleArray ) && !empty( $this->parent->googleArray ) ) {
            return;
        }          

        if( !file_exists( DethemeReduxFramework::$_dir.'inc/fields/typography/googlefonts.json' ) ) {
          $result = wp_remote_get( 'https://www.googleapis.com/webfonts/v1/webfonts?key='.$this->args['google_api_key']);
          if ($result['response']['code'] == 200) {
              $result = json_decode($result['body']);
              foreach ($result->items as $font) {
                  $this->parent->googleArray[$font->family] = array(
                      'variants' => $this->getVariants($font->variants),
                      'subsets' => $this->getSubsets($font->subsets)
                  );
              }

              if ( !empty( $this->parent->googleArray ) ) {
                  $wp_filesystem->put_contents(
                      DethemeReduxFramework::$_dir.'inc/fields/typography/googlefonts.json',
                      json_encode($this->parent->googleArray),
                      FS_CHMOD_FILE // predefined mode settings for WP files
                  );
              }

          }//if
        }//if
        
      if ( !isset( $this->parent->googleArray ) || empty( $this->parent->googleArray ) ) {
          $this->parent->googleArray = json_decode($wp_filesystem->get_contents(DethemeReduxFramework::$_dir.'inc/fields/typography/googlefonts.json' ), true );
      }

    }

    /**
     * getGoogleFonts Function.
     *
     * Used to retrieve Google Web Fonts from their API
     *
     * @since ReduxFramework 0.2.0
     */
    function getGoogleFonts() {
        
        $this->getGoogleArray();
        
        if ( !isset( $this->parent->googleArray ) || empty( $this->parent->googleArray ) ) {
            return;
        }

        $gfonts = '<optgroup label="'.__('Google Webfonts', 'redux-framework').'">';
        foreach ($this->parent->googleArray as $i => $face) {
            $gfonts .= '<option data-details="'.urlencode(json_encode($face)).'" data-google="true" value="'.$i.'">'. $i .'</option>';
        }
        $gfonts .= '</optgroup>';
        //endif;
        if (empty($this->parent->googleArray)) {
            $gfonts = "";
        }

        $wp_filesystem->put_contents(
            DethemeReduxFramework::$_dir.'inc/fields/typography/googlefonts.html',
            $gfonts,
            FS_CHMOD_FILE // predefined mode settings for WP files
        );
    }//function

    /**
     * getGoogleFonts Function.
     *
     * Clean up the Google Webfonts subsets to be human readable
     *
     * @since ReduxFramework 0.2.0
     */
    function getSubsets($var) {
        $result = array();
        foreach ($var as $v) {
            if (strpos($v,"-ext")) {
                $name = ucfirst(str_replace("-ext"," Extended",$v));
            } else {
                $name = ucfirst($v);
            }
            array_push($result, array('id'=>$v, 'name'=>$name));
        }
        return array_filter($result);
    }//function

    /**
     * getGoogleFonts Function.
     *
     * Clean up the Google Webfonts variants to be human readable
     *
     * @since ReduxFramework 0.2.0
     */
    function getVariants($var) {
        $result = array();
        $italic = array();
        foreach ($var as $v) {
            $name = "";
            if ($v[0] == 1) {
                $name = 'Ultra-Light 100';
            } else if ($v[0] == 2) {
                $name = 'Light 200';
            } else if ($v[0] == 3) {
                $name = 'Book 300';
            } else if ($v[0] == 4 || $v[0] == "r" || $v[0] == "i") {
                $name = 'Normal 400';
            } else if ($v[0] == 5) {
                $name = 'Medium 500';
            } else if ($v[0] == 6) {
                $name = 'Semi-Bold 600';
            } else if ($v[0] == 7) {
                $name = 'Bold 700';
            } else if ($v[0] == 8) {
                $name = 'Extra-Bold 800';
            } else if ($v[0] == 9) {
                $name = 'Ultra-Bold 900';
            }
            if ($v == "regular") {
                $v = "400";
            }
            if (strpos($v,"italic") || $v == "italic") {
                $name .= " Italic";
                $name = trim($name);
                $italic[] = array('id'=>$v, 'name'=>$name);
            } else {
                $result[] = array('id'=>$v, 'name'=>$name);
            }
        }

        array_push($result, array_pop($italic));

        return array_filter($result);
    }//function


}//class