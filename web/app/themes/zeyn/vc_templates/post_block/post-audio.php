<?php
defined('ABSPATH') or die();

global $post,$detheme_config;

$content=get_post($post->id);

        $regexshortcodes=
        '\\['                              // Opening bracket
        . '(\\[?)'                           // 1: Optional second opening bracket for escaping shortcodes: [[tag]]
        . "(audio)"                     // 2: Shortcode name
        . '(?![\\w-])'                       // Not followed by word character or hyphen
        . '('                                // 3: Unroll the loop: Inside the opening shortcode tag
        .     '[^\\]\\/]*'                   // Not a closing bracket or forward slash
        .     '(?:'
        .         '\\/(?!\\])'               // A forward slash not followed by a closing bracket
        .         '[^\\]\\/]*'               // Not a closing bracket or forward slash
        .     ')*?'
        . ')'
        . '(?:'
        .     '(\\/)'                        // 4: Self closing tag ...
        .     '\\]'                          // ... and closing bracket
        . '|'
        .     '\\]'                          // Closing bracket
        .     '(?:'
        .         '('                        // 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags
        .             '[^\\[]*+'             // Not an opening bracket
        .             '(?:'
        .                 '\\[(?!\\/\\2\\])' // An opening bracket not followed by the closing shortcode tag
        .                 '[^\\[]*+'         // Not an opening bracket
        .             ')*+'
        .         ')'
        .         '\\[\\/\\2\\]'             // Closing shortcode tag
        .     ')?'
        . ')'
        . '(\\]?)';                          // 6: Optional second closing brocket for escaping shortcodes: [[tag]]



preg_match_all( '/'. $regexshortcodes .'/s', $content->post_content, $matches );

?>
<?php   if (count($matches[0])) { ?>											
		<div class="post-image">
				<?php if ($post->thumbnail!=="") { ?>
					<?php print $post->thumbnail;?>
				<?php } else { ?>
					<div class="postaudio tertier_color_bg">
						<i class="icon-note-beamed"></i>
					</div>
				<?php } ?>
             		<?php
         				echo do_shortcode($matches[0][0]);
               		?>
		</div>
<?php } ?>							

<div class="post-info">
	<span class="author"><?php print $post->author;?></span>
    <?php if ( comments_open($post->id)) : ?><span> / <?php print $post->comment_count;?> Comments</span><?php endif; //if ( comments_open()) ?>
	<h4><a href="<?php echo $post->link ?>" class="vc_read_more" title="<?php echo esc_attr(sprintf(__( 'Detail to %s', 'detheme' ), $post->title_attribute)); ?>"<?php echo " target=\"".$post->link_target."\""; ?>><?php print $post->title;?></a></h4>
	<div class="post-content">
		<?php print $post->excerpt;?>
	</div>
</div>
<div class="postmetabottom">
	<div class="col-xs-7">
		<?php if (!empty($post->post_tags)) : ?>
		<i class="icon-tags-2"></i><?php echo $post->post_tags; ?>
		<?php endif; //if ($tags!='') : ?>
	</div>
	<div class="col-xs-5">
		<i class="icon-clock-circled"></i><?php print $post->post_date;?>
	</div>
</div>
