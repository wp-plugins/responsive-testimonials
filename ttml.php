<?php
/*
Plugin Name: Responsive Testimonials
Plugin URI: http://wpdarko.com/responsive-testimonials/
Description: A responsive, clean and easy way to display testimonials. Create new testimonials, add authors and their jobs and copy-paste the shortcode into any post/page. Find help and information on our <a href="http://wpdarko.com/support/">support site</a>. This free version is NOT limited and does not contain any ad. Check out the <a href='http://wpdarko.com/responsive-testimonials/'>PRO version</a> for more great features.
Version: 1.1
Author: WP Darko
Author URI: http://wpdarko.com
License: GPL2
*/

/* Check for the PRO version */
function ttml_free_pro_check() {
    if (is_plugin_active('responsive-testimonials-pro/ttml_pro.php')) {
        
        function my_admin_notice(){
        echo '<div class="updated">
                <p><strong>PRO</strong> version is activated.</p>
              </div>';
        }
        add_action('admin_notices', 'my_admin_notice');
        
        deactivate_plugins(__FILE__);
    }
}

add_action( 'admin_init', 'ttml_free_pro_check' );

/* Enqueue styles & scripts */
add_action( 'wp_enqueue_scripts', 'add_ttml_scripts' );
function add_ttml_scripts() {
	wp_enqueue_style( 'ttml', plugins_url('css/ttml_custom_style.min.css', __FILE__));
}

/* Enqueue admin styles */
add_action( 'admin_enqueue_scripts', 'add_admin_ttml_style' );

function add_admin_ttml_style() {
	wp_enqueue_style( 'ttml', plugins_url('css/admin_de_style.min.css', __FILE__));
}

/* Create the Testimonial post type */
add_action( 'init', 'create_ttml_type' );

function create_ttml_type() {
  register_post_type( 'ttml',
    array(
      'labels' => array(
        'name' => 'Testimonials',
        'singular_name' => 'Testimonial'
      ),
      'public' => true,
      'has_archive'  => false,
      'hierarchical' => false,
      'capability_type'    => 'post',
      'supports'     => array( 'title' ),
      'menu_icon'    => 'dashicons-plus',
    )
  );
}

/* Hide View/Preview since it's a shortcode */
function ttml_admin_css() {
    global $post_type;
    $post_types = array( 
                        'ttml',
                  );
    if(in_array($post_type, $post_types))
    echo '<style type="text/css">#post-preview, #view-post-btn{display: none;}</style>';
}

function remove_view_link_ttml( $action ) {

    unset ($action['view']);
    return $action;
}

add_filter( 'post_row_actions', 'remove_view_link_ttml' );
add_action( 'admin_head-post-new.php', 'ttml_admin_css' );
add_action( 'admin_head-post.php', 'ttml_admin_css' );

// Adding the CMB2 Metabox class
if ( file_exists( dirname( __FILE__ ) . '/cmb2/init.php' ) ) {
    require_once dirname( __FILE__ ) . '/cmb2/init.php';
} elseif ( file_exists( dirname( __FILE__ ) . '/CMB2/init.php' ) ) {
    require_once dirname( __FILE__ ) . '/CMB2/init.php';
}

// Registering Testimonials metaboxes
function ttml_register_group_metabox() {
    
    $prefix = '_ttml_';
   
    // Tables group
    $main_group = new_cmb2_box( array(
        'id' => $prefix . 'testimonial_metabox',
        'title' => '<span class="dashicons dashicons-welcome-add-page"></span> Manage Testimonials <span style="color:#8a7463; font-weight:400; float:right; padding-right:14px;"><span class="dashicons dashicons-lock"></span> Free version</span>',
        'object_types' => array( 'ttml' ),
    ));
    
     $main_group->add_field( array(
         'name'    => '<span style="font-weight:400;">Getting started / Instructions</span>',
         'desc' => 'Edit your testimonials (see below), add more, reorder them and play around with the settings on the right. If you have trouble understanding how this works, click the "Help & Support tab on the right."',
         'id'      => $prefix . 'instructions',
         'type'    => 'title',
         'row_classes' => 'de_hundred de_instructions',
     ) );
    
        $ttml_group = $main_group->add_field( array(
            'id' => $prefix . 'head',
            'type' => 'group',
            'options' => array(
                'group_title' => 'Testimonial {#}',
                'add_button' => 'Add another testimonial',
                'remove_button' => 'Remove testimonial',
                'sortable' => true,
                'single' => false,
            ),
        ));
    
            $main_group->add_group_field( $ttml_group, array(
                'name' => 'Testimonial details',
                'id' => $prefix . 'testimonial_header',
                'type' => 'title',
                'row_classes' => 'de_hundred de_heading',
            ));

            $main_group->add_group_field( $ttml_group, array(
                'name' => '<span class="dashicons dashicons-edit"></span> Testimonial text',
				'id' => $prefix . 'text',
				'type' => 'textarea',
                'attributes'  => array(
                    'rows' => 5,
                ),
                'row_classes' => 'de_first de_seventyfive de_textarea de_input',
                'sanitization_cb' => false,
            ));
    
            $main_group->add_group_field( $ttml_group, array(
                'name' => 'Tips & Tricks',
                'desc' => '<span class="dashicons dashicons-yes"></span> Titles (H tags)<br/><span style="color:#bbb;">&lt;h1&gt;&lt;h2&gt;&lt;h3&gt;&lt;h4&gt;...</span></span><br/><br/><span class="dashicons dashicons-yes"></span> HTML allowed<br/><span style="color:#bbb;">&lt;img&gt;&lt;a&gt;&lt;br\&gt;&lt;p&gt;...</span></span>',
                'id'   => $prefix . 'text_tips',
                'type' => 'title',
                'row_classes' => 'de_twentyfive de_info',
            ));
    
            $main_group->add_group_field( $ttml_group, array(
                'name' => '<span style="color:#8a7463;"><span class="dashicons dashicons-lock"></span> PRO Heading quote</span>',
                'id' => $prefix . 'heading_quote',
                'desc' => 'An introduction/main point of this testimonial.',
                'type' => 'text',
                'row_classes' => 'de_first de_hundred de_text de_input',
                'sanitization_cb' => false,
            ));
    
            $main_group->add_group_field( $ttml_group, array(
                'name' => '<span class="dashicons dashicons-edit"></span> Author</span>',
                'id' => $prefix . 'author',
                'type' => 'text',
                'row_classes' => 'de_first de_fifty de_text de_input',
                'sanitization_cb' => false,
            ));
    
            $main_group->add_group_field( $ttml_group, array(
                'name' => '<span class="dashicons dashicons-edit"></span> Job/company</span>',
                'id' => $prefix . 'job',
                'type' => 'text',
                'row_classes' => 'de_fifty de_text de_input',
                'sanitization_cb' => false,
            ));
    
            $main_group->add_group_field( $ttml_group, array(
                'name' => 'Author\'s photo',
                'id' => $prefix . 'author_styling_header',
                'type' => 'title',
                'row_classes' => 'de_hundred de_heading',
            ));
    
            $main_group->add_group_field( $ttml_group, array(
                'name' => '<span class="dashicons dashicons-format-image"></span> Upload Photo',
                'id'   => $prefix . 'photo',
                'type' => 'file',
                'attributes'  => array(
                    'placeholder' => 'Recommended size: 250 x 250px (square shaped)',
                ),
                'options' => array(
		            'add_upload_file_text' => __( 'Upload', 'jt_cmb2' ),
	            ),
                'row_classes' => 'de_first de_hundred de_upload de_input',
            ));
    
            $main_group->add_group_field( $ttml_group, array(
                'name' => 'Styling',
                'id' => $prefix . 'author_styling2_header',
                'type' => 'title',
                'row_classes' => 'de_hundred de_heading',
            ));
    
            $main_group->add_group_field( $ttml_group, array(
                'name' => '<span style="color:#8a7463;"><span class="dashicons dashicons-lock"></span> PRO Testimonial\'s color</span>',
                'id' => $prefix . 'color_pro',
                'desc' => 'Pro version allows a color <strong>per</strong> testimonial.',
                'type' => 'colorpicker',
                'default' => '#ffffff',
                'row_classes' => 'de_first de_hundred de_color de_input',
            ));
    
    // Settings group
    $side_group = new_cmb2_box( array(
        'id' => $prefix . 'settings_head',
        'title' => '<span class="dashicons dashicons-admin-tools"></span> Testimonials Settings',
        'object_types' => array( 'ttml' ),
        'context' => 'side',
        'priority' => 'high',
        'closed' => true,
    ));
        
        $side_group->add_field( array(
            'name' => 'General settings',
            'id'   => $prefix . 'other_settings_desc',
            'type' => 'title',
            'row_classes' => 'de_hundred_side de_heading_side',
        ));
    
        $side_group->add_field( array(
            'name' => '<span class="dashicons dashicons-admin-appearance"></span> Main Color</span>',
            'id' => $prefix . 'color',
            'type' => 'colorpicker',
            'default' => '#2b99e2',
            'row_classes' => 'de_first de_hundred de_color de_input',
        ));
    
        $side_group->add_field( array(
            'name'    => '<span class="dashicons dashicons-arrow-down"></span> Testimonials layout',
			'id'      => $prefix . 'layout',
			'type'    => 'select',
            'desc'    => '<div style="margin-top:-20px;"><span style="color:#8a7463;"><span style="position:relative; top:-2px;" class="dashicons dashicons-lock"></span> PRO version has 3 more options.</span></div>',
			'options' => array(
			    'tb2'   => 'Text below, 2 columns',
			    'tb3'   => 'Text below, 3 columns',
                'tr2'   => 'Text on the right, 2 columns',
			),
			'default' => 'tb3',
            'row_classes' => 'de_hundred_side de_text_side',
        ));
    
        $side_group->add_field( array(
            'name'    => '<span class="dashicons dashicons-arrow-down"></span> Author block background',
			'id'      => $prefix . 'author_bg',
			'type'    => 'select',
            'desc'    => '<div style="margin-top:-20px;"><span style="color:#8a7463;"><span style="position:relative; top:-2px;" class="dashicons dashicons-lock"></span> PRO version has 1 more option.</span></div>',
			'options' => array(
			    'transparent'   => 'Transparent',
			    'whitesmoke'   => 'Light grey',
			),
			'default' => 'classic',
            'row_classes' => 'de_hundred_side de_text_side',
        ));
    
        $side_group->add_field( array(
            'name' => '<span class="dashicons dashicons-admin-generic"></span> Force original fonts',
            'desc' => 'By default this plugin will use your theme\'s font, check this to force the use of the plugin\'s original fonts.',
		    'id'   => $prefix . 'original_font',
		    'type' => 'checkbox',
            'row_classes' => 'de_hundred_side de_checkbox_side',
            'default' => false,
        ));
    
        $side_group->add_field( array(
            'name' => 'Picture settings',
            'id'   => $prefix . 'other_settings_picture',
            'type' => 'title',
            'row_classes' => 'de_hundred_side de_heading_side',
        ));
    
        $side_group->add_field( array(
            'name'    => '<span style="color:#8a7463;"><span class="dashicons dashicons-lock"></span> PRO Pictures\' size</span>',
			'id'      => $prefix . 'picture_size',
			'type'    => 'select',
			'options' => array(
                '80'   => 'Medium small',
			),
			'default' => '80',
            'row_classes' => 'de_hundred_side de_text_side',
        ));
    
        $side_group->add_field( array(
            'name' => 'Text settings',
            'id'   => $prefix . 'other_settings_text',
            'type' => 'title',
            'row_classes' => 'de_hundred_side de_heading_side',
        ));
    
        $side_group->add_field( array(
            'name'    => '<span class="dashicons dashicons-arrow-down"></span> Author\'s font size',
			'id'      => $prefix . 'author_size',
			'type'    => 'select',
			'options' => array(
				'17'   => 'Big',
                '15'   => 'Medium big',
                '14'   => 'Medium',
                '13'   => 'Medium small',
                '12'   => 'Small',
			),
			'default' => '14',
            'row_classes' => 'de_hundred_side de_text_side',
        ));
    
        $side_group->add_field( array(
            'name'    => '<span class="dashicons dashicons-arrow-down"></span> Job\'s font size',
			'id'      => $prefix . 'job_size',
			'type'    => 'select',
			'options' => array(
				'17'   => 'Big',
                '15'   => 'Medium big',
                '14'   => 'Medium',
                '13'   => 'Medium small',
                '12'   => 'Small',
			),
			'default' => '13',
            'row_classes' => 'de_hundred_side de_text_side',
        ));
    
        $side_group->add_field( array(
            'name'    => '<span class="dashicons dashicons-arrow-down"></span> Text\'s font size',
			'id'      => $prefix . 'text_size',
			'type'    => 'select',
			'options' => array(
				'17'   => 'Big',
                '15'   => 'Medium big',
                '14'   => 'Medium',
                '13'   => 'Medium small',
                '12'   => 'Small',
			),
			'default' => '14',
            'row_classes' => 'de_hundred_side de_text_side',
        ));
    
        $side_group->add_field( array(
            'name'    => '<span style="color:#8a7463;"><span class="dashicons dashicons-lock"></span> PRO Heading quote\'s font size</span>',
			'id'      => $prefix . 'heading_size',
			'type'    => 'select',
			'options' => array(
				'-'   => '-',
			),
			'default' => '19',
            'row_classes' => 'de_hundred_side de_text_side',
        ));     
    
        $side_group->add_field( array(
            'name'    => '<span style="color:#8a7463;"><span class="dashicons dashicons-lock"></span> PRO Text alignment</span>',
			'id'      => $prefix . 'text_align',
			'type'    => 'select',
            'desc'    => 'Only applies to "text below" layout.',
			'options' => array(
				'left'   => 'Left',
			),
			'default' => 'left',
            'row_classes' => 'de_hundred_side de_text_side',
        ));
    
    // Help group
    $help_group = new_cmb2_box( array(
        'id' => $prefix . 'help_metabox',
        'title' => '<span class="dashicons dashicons-sos"></span> Help & Support',
        'object_types' => array( 'ttml' ),
        'context' => 'side',
        'priority' => 'high',
        'closed' => true,
        'row_classes' => 'de_hundred de_heading',
    ));
    
        $help_group->add_field( array(
            'name' => '',
                'desc' => 'Find help at WPdarko.com<br/><br/><a target="_blank" href="http://wpdarko.com/support/"><span class="dashicons dashicons-arrow-right-alt2"></span> Support forum</a><br/><a target="_blank" href="https://wpdarko.zendesk.com/hc/en-us/articles/206340897-Get-started-with-the-Responsive-Testimonials-plugin"><span class="dashicons dashicons-arrow-right-alt2"></span> Documentation</a>',
                'id'   => $prefix . 'help_desc',
                'type' => 'title',
                'row_classes' => 'de_hundred de_info de_info_side',
        ));
    
    // PRO group
    $pro_group = new_cmb2_box( array(
        'id' => $prefix . 'pro_metabox',
        'title' => '<span class="dashicons dashicons-awards"></span> PRO version',
        'object_types' => array( 'ttml' ),
        'context' => 'side',
        'priority' => 'high',
        'closed' => true,
        'row_classes' => 'de_hundred de_heading',
    ));
    
        $pro_group->add_field( array(
            'name' => '',
                'desc' => 'This free version is <strong>not</strong> limited and does <strong>not</strong> contain any ad. Check out the PRO version for more great features.<br/><br/><a target="_blank" href="http://wpdarko.com/responsive-testimonials/"><span class="dashicons dashicons-arrow-right-alt2"></span> See plugin\'s page</a><br/><br/><span style="font-size:13px; color:#88acbc;">Coupon code <strong>9224661</strong> (10% OFF).</span>',
                'id'   => $prefix . 'pro_desc',
                'type' => 'title',
                'row_classes' => 'de_hundred de_info de_info_side',
        ));
    
    // Shortcode group
    $show_group = new_cmb2_box( array(
        'id' => $prefix . 'shortcode_metabox',
        'title' => '<span class="dashicons dashicons-visibility"></span> Display my Testimonials',
        'object_types' => array( 'ttml' ),
        'context' => 'side',
        'priority' => 'low',
        'closed' => false,
        'row_classes' => 'de_hundred de_heading',
    ));
    
        $show_group->add_field( array(
            'name' => '',
            'desc' => 'To display your Testimonials on your site, copy-paste the Testimonial\'s [Shortcode] in your post/page. <br/><br/>You can find this shortcode by clicking on the "Testimonials" tab in the menu on the left.',
            'id'   => $prefix . 'short_desc',
            'type' => 'title',
            'row_classes' => 'de_hundred de_info de_info_side',
        ));

}

add_action( 'cmb2_init', 'ttml_register_group_metabox' );

//Shortcode columns
add_action( 'manage_ttml_posts_custom_column' , 'dkttml_custom_columns', 10, 2 );

function dkttml_custom_columns( $column, $post_id ) {
    switch ( $column ) {
	case 'shortcode' :
		global $post;
		$slug = '' ;
		$slug = $post->post_name;
   
    
    	    $shortcode = '<span style="border: solid 3px lightgray; background:white; padding:7px; font-size:17px; line-height:40px;">[ttml name="'.$slug.'"]</strong>';
	    echo $shortcode; 
	    break;
    }
}

function add_ttml_columns($columns) {
    return array_merge($columns, 
              array('shortcode' => __('Shortcode'),
                    ));
}
add_filter('manage_ttml_posts_columns' , 'add_ttml_columns');

//ttml shortcode
function ttml_sc($atts) {
	extract(shortcode_atts(array(
		"name" => ''
	), $atts));
	
    global $post;
    $args = array('post_type' => 'ttml', 'name' => $name);
    $custom_posts = get_posts($args);
    foreach($custom_posts as $post) : setup_postdata($post);
    
	$testimonials = get_post_meta( get_the_id(), '_ttml_head', true );
    
    //fetching testimonial options
    $ttml_text_align = get_post_meta( $post->ID, '_ttml_text_align', true );
    $ttml_color = get_post_meta( $post->ID, '_ttml_color', true );
    
    $ttml_a_size = get_post_meta( $post->ID, '_ttml_author_size', true );
    $ttml_j_size = get_post_meta( $post->ID, '_ttml_job_size', true );
    $ttml_h_size = get_post_meta( $post->ID, '_ttml_heading_size', true );
    $ttml_t_size = get_post_meta( $post->ID, '_ttml_text_size', true );
    
    //generating the layout options
    $ttml_layout = get_post_meta( $post->ID, '_ttml_layout', true );
    if ($ttml_layout == 'tb2') {$ttml_columns = 2; $ttml_ly = 'default';}
    if ($ttml_layout == 'tb3') {$ttml_columns = 3; $ttml_ly = 'default';}
    if ($ttml_layout == 'tr2') {$ttml_columns = 2; $ttml_ly = 'list';}
    
    //generating the color options
    $ttml_author_bg = get_post_meta( $post->ID, '_ttml_author_bg', true );
    if ($ttml_author_bg == 'transparent'){$author_bg = 'background: transparent;'; $text_color_job = 'color:'.$ttml_color.' !important;'; $text_color = 'color:#333 !important;';}
    if ($ttml_author_bg == 'whitesmoke'){$author_bg = 'background: whitesmoke;'; $text_color_job = 'color:'.$ttml_color.' !important;'; $text_color = 'color:#333 !important;';}
    
    // Forcing original fonts?
    $original_font = get_post_meta( $post->ID, '_ttml_original_font', true );
    if ($original_font == true){
        $ori_f = 'ttml_ori_f';
    } else {
        $ori_f = '';
    }

    $output .= '<div class="ttml ttml_'.$name.'">';
    $output .= '<div class="ttml_'.$ttml_columns.'_columns ttml_'.$ttml_ly.'_layout ttml_'.$ttml_style.'_skin '.$ori_f.'">';
    $output .= '
        <div class="ttml_wrap ttml_picture_80">
                ';
                
                $i = 0;
                foreach ($testimonials as $key => $testimonial) {
            
                    if($i%$ttml_columns == 0) {
                        if($i > 0) { 
                            $output .= "</div>";
                            $output .= '<div class="clearer"></div>';
                        } // close div if it's not the first
                        
                        $output .= "<div class='ttml_container'>";
                    }
                    
                    //If as columns (default layout)
                    if ($ttml_ly == 'default'){
                        
                        $output .= '<div class="ttml_testimonial">';    
                            
                            $output .= '<div class="ttml_textblock">';
                                if (!empty($testimonial['_ttml_photo'])){
                                    $output .= '<div class="ttml_photo_box" style="width:80px; height:80px; float:left; margin-right:20px;"><img style="'.$image_border.'" src="'.$testimonial['_ttml_photo'].'" alt="'.$testimonial['_ttml_author'].'"/></div>';
                                }
                                $output .= '<div class="ttml_author_block" style="padding: 4px 15px 10px; '.$author_bg.'">';
                                    $output .= '<p class="ttml_author" style="text-align:left; '.$text_color.'  font-size:'.$ttml_a_size.'px;">'.$testimonial['_ttml_author'].'</p>';
                                    $output .= '<p class="ttml_job" style="text-align:left; '.$text_color_job.' font-size:'.$ttml_j_size.'px;">'.$testimonial['_ttml_job'].'</p>';
                                $output .= '</div>';
                                
                                $output .= '<div class="ttml_text" style="font-size:'.$ttml_t_size.'px; text-align:'.$ttml_text_align.';">'.$testimonial['_ttml_text'].'</div>';
                        
                            //clsing text block        
                            $output .= '</div>';
                        
                        //closing testimonial
                        $output .= '</div>';
                      
                    //If as list
                    } elseif ($ttml_ly == 'list') {
                        
                        $output .= '<div class="ttml_testimonial">';
          
                            $output .= '<div class="ttml_textblock">';                      
                        
                                $output .= '<div class="ttml_author_block" style="text-align:left; padding:10px 15px; '.$author_bg.'">';
                                    $output .= '<span class="ttml_author" style="'.$text_color.' font-size:'.$ttml_a_size.'px;">'.$testimonial['_ttml_author'].' </span> ';
                                    $output .= ' <span class="ttml_job" style="'.$text_color_job.' font-size:'.$ttml_j_size.'px; float:right;">'.$testimonial['_ttml_job'].'</span>';
                                $output .= '</div>';
                                
                                if (!empty($testimonial['_ttml_photo'])){  
                                    $output .= '<div style="float:left !important; padding: 0 14px 0px 0; width:80px; height:80px;" class="ttml_photo_box"><img style="'.$image_border.'" src="'.$testimonial['_ttml_photo'].'" alt="'.$testimonial['_ttml_author'].'"/></div>';
                                }
                                
                                $output .= '<div class="ttml_text" style="text-align:left; font-size:'.$ttml_t_size.'px;">'.$testimonial['_ttml_text'].'</div>';
                                
                            //closing text block
                            $output .= '</div><div style="clear:both;"></div>';
                        
                        //closing testimonial
                        $output .= '</div>';
                        
                    }
                    
                    $pages_count = count( $testimonial );
                    if ($key == $pages_count - 1) {
                        $output .= '<div class="clearer"></div>';
                    }
                    $i++; 
                } //closing foreach
    
    $output .= '</div>'; //closing container
    $output .= '</div>'; //closing wrap
    $output .= '</div>'; //closing column number
    $output .= '</div><div style="clear:both;"></div>'; //closing master (ttml)

    endforeach; wp_reset_query();
	
    return $output;

} //end of shortcode function

add_shortcode("ttml", "ttml_sc"); 
?>