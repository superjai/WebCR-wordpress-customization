<?php
/**
* Plugin Name: WebCR plugin
* Plugin URI: https://noaa.gov/
* Description: NMS Condition Report Back End Customization.
* Version: 0.1
* Author: Jai Ranganathan
* Author URI: https://noaa.gov/
**/

/** 
 * Notes for myself
 * article for using media library for getting image
 * https://rudrastyh.com/wordpress/customizable-media-uploader.htm
 *  more options for sanitizing data fields
 * great blog post: https://rudrastyh.com/wordpress/sanitize-escape-validate.html
 * wp_kses() - sanitization where some HTML elements are allowed
 * https://developer.wordpress.org/reference/functions/wp_kses/
 * code for adding media manager
 * https://wordpress.stackexchange.com/questions/256043/how-to-add-meta-box-for-image-upload-using-wordpress-media-uploader
 * customizing admin columns
 * https://www.smashingmagazine.com/2017/12/customizing-admin-columns-wordpress/
 * wordpress codex on using javascript
 * https://codex.wordpress.org/Javascript_Reference/wp.media
 * javascript media guide
 * https://github.com/ericandrewlewis/wp-media-javascript-guide
 * 
**/

// Don't access this file directly
defined( 'ABSPATH' ) or die();

// Allow svg files to be uploaded to Wordpress content directory
function upload_svg_files( $allowed ) {
    if ( !current_user_can( 'manage_options' ) )
        return $allowed;
    $allowed['svg'] = 'image/svg+xml';
    return $allowed;
}
add_filter( 'upload_mimes', 'upload_svg_files');

// Create Custom Post Type for Scene
function webCR_custom_post_type() {
    register_post_type('scene',
        array(
            'labels'      => array(
                'name'          => __('Scenes', 'textdomain'),
                'singular_name' => __('Scene', 'textdomain'),
                'add_new_item'  => __( 'Add New Scene', 'textdomain' ),
            ),
                'public'      => true,
                'has_archive' => true,
                'publicly_queryable' => true,
                'show_ui' => true,
                'show_in_menu' => true,
                'show_in_rest' => true,
                'supports' => array('title', 'custom-fields', 'revisions'),
                'can_export' => true,
                'menu_icon' => 'dashicons-admin-site',
        )
    );
}
add_action('init', 'webCR_custom_post_type');

// Use Block Editor for entering WebCR info
function webCR_disable_gutenberg($current_status, $post_type) {
    if ($post_type === 'scene') return false;
    return $current_status;
}
add_filter('use_block_editor_for_post_type', 'webCR_disable_gutenberg', 10, 2);

// add all meta boxes to scene
function add_post_meta_boxes() {

    add_meta_box(
        'post_meta_box_scene_image', // meta box ID
        'Scene Image', // meta box title
        'misha_print_box', // callback function that prints the meta box HTML 
        'scene', // post type where to add it
        'normal', // priority
        'high' // position
    ); 

    // add scene region meta box
    add_meta_box(
        "post_meta_box_scene_region", // div id containing rendered fields
        "Scene Region", // section heading displayed as text
        "post_meta_box_scene_region", // callback function to render fields
        "scene", // name of post type on which to render fields
        "normal" , // location on the screen
        "high" // placement priority
    );

    // add scene intro meta box
    add_meta_box(
        "post_meta_box_scene_intro", // div id containing rendered fields
        "Scene Introduction", // section heading displayed as text
        "post_meta_box_scene_intro", // callback function to render fields
        "scene", // name of post type on which to render fields
        "normal" , // location on the screen
        "high" // placement priority
    );

    // add info link meta box
    add_meta_box(
        "post_meta_box_scene_info_link", // div id containing rendered fields
        "Scene Information URL", // section heading displayed as text
        "post_meta_box_scene_info_link", // callback function to render fields
        "scene", // name of post type on which to render fields
        "normal" , // location on the screen
        "high" // placement priority
    );

    // add image link meta box
    add_meta_box(
        "post_meta_box_scene_image_link", // div id containing rendered fields
        "Scene Image URL", // section heading displayed as text
        "post_meta_box_scene_image_link", // callback function to render fields
        "scene", // name of post type on which to render fields
        "normal" , // location on the screen
        "high" // placement priority
    );

    // add scene intro meta box
    add_meta_box(
            "post_meta_box_scene_comments", // div id containing rendered fields
            "Scene Comments", // section heading displayed as text
            "post_meta_box_scene_comments", // callback function to render fields
            "scene", // name of post type on which to render fields
            "normal" , // location on the screen
            "high" // placement priority
        );
        
}
add_action( "admin_init", "add_post_meta_boxes");

// callback function to render scene region field
function post_meta_box_scene_region(){
    global $post;
    $custom = get_post_custom( $post->ID );
    if (array_key_exists("_scene_region", $custom)){
                $sceneRegion = $custom[ "_scene_region" ][ 0 ];
    } else {
        $sceneRegion = '';
    }

    echo "<select style='width: 100%' name='_scene_region' value='".$sceneRegion."' >";
    echo "<option value=''>--Choose a region--</option>";
    echo "<option value='Channel Islands NMS'>Channel Islands NMS</option>";
    echo "<option value='Olympic Coast NMS'>Olympic Coast NMS</option></select>";
}

// callback function to render scene intro field
function post_meta_box_scene_intro(){
    global $post;
    $custom = get_post_custom( $post->ID );
    if (array_key_exists("_scene_intro", $custom)){
                $sceneIntroData = $custom[ "_scene_intro" ][ 0 ];
    } else {
        $sceneIntroData = "";
    }

    echo "<textarea style='width: 100%' rows=6 cols=120 name='_scene_intro'>".$sceneIntroData."</textarea>";
}

// callback function to render scene info link field
function post_meta_box_scene_info_link(){
    global $post;
    $custom = get_post_custom( $post->ID );
    $sceneInfoURL = $custom[ "_scene_info_link" ][ 0 ];
    echo "<input list='info_link_options' size = '80' style='width: 100%' name='_scene_info_link' value='".$sceneInfoURL."' >";
    echo "<datalist id='info_link_options'>";
    $infoLinkOptions = createUrlList("_scene_info_link", "scene");

    foreach ($infoLinkOptions as $value) {
        echo "<option value='".$value."'>";
    }
    echo "</datalist>";
}

// callback function to render scene image link field
function post_meta_box_scene_image_link(){
    global $post;
    $custom = get_post_custom( $post->ID );
    if (array_key_exists("_scene_image_link", $custom)){
        $sceneImageURL = $custom[ "_scene_image_link" ][ 0 ];
    } else {
        $sceneImageURL = "";
    }
    echo "<input list='image_link_options' size = '80' style='width: 100%' name='_scene_image_link' value='".$sceneImageURL."' >";
    echo "<datalist id='image_link_options'>";
    $imageLinkOptions = createUrlList("_scene_image_link", "scene");

    foreach ($imageLinkOptions as $value) {
        echo "<option value=\"".$value."\">";
    }
    echo "</datalist>";
}

// callback function to render scene comments field
function post_meta_box_scene_comments(){
    global $post;
    $custom = get_post_custom( $post->ID );
    if (array_key_exists("_scene_comments", $custom)){
        $sceneComments = $custom[ "_scene_comments" ][ 0 ];
    } else {
        $sceneComments = "";
    }
    echo "<textarea style='width: 100%' rows=6 cols=120 name='_scene_comments'>".$sceneComments."</textarea>";
}

// Generate drop down list of values that have been entered in a field previously
function createUrlList (string $customField, string $customPost){
    $the_query = new WP_Query(array('post_type' => $customPost,));

    if ( $the_query->have_posts() ) {
        $results = [];
        while ( $the_query->have_posts() ) {
            $the_query->the_post(); 
            $custom = get_post_custom( $the_query->ID );
            if (array_key_exists($customField, $custom)){
                $metabox_value = get_post_custom_values($customField)[0];
                array_push($results, $metabox_value);
            }
        }
        wp_reset_postdata();
        $results = array_unique($results);
        sort($results);
        return $results;
    }
}

// Add explanatory text and make initial title text visible
function add_explanatory_text_scene(){

     $screen = get_current_screen();
     if  ( 'scene' == $screen->post_type ) {
        ?>
        <script type="text/JavaScript">

          //  var element = document.getElementById("title-prompt-text");
          //  element.classList.remove("screen-reader-text");

            const helpArray = [
                ["post_meta_box_scene_image", "Explanatory text for Scene Image field."],
                ["post_meta_box_scene_region", "Explanatory text for Scene Region field."],
                ["post_meta_box_scene_intro", "Explanatory text for Scene Introduction field."],
                ["post_meta_box_scene_info_link", "Explanatory text for Scene Info field."],
                ["post_meta_box_scene_image_link", "Explanatory text for Scene Image field."],
                ["post_meta_box_scene_comments", "Explanatory text for Scene Comments field."]
            ];
            if (!(document.getElementById(helpArray[0][0]) === null)){
                let helpDiv = document.createElement("div");
                helpDiv.setAttribute('style', 'padding-left: 12px; padding-bottom: 10px;');
                let elementInsert;
                for (let i = 0; i < 6; i++) {
                    elementInsert = document.getElementById(helpArray[i][0]);
                    helpDiv.innerHTML = helpArray[i][1];
                    elementInsert.appendChild(helpDiv.cloneNode(true));
                }

                // Add styling to text title
                const titleWrapper = document.createElement("div");
                titleWrapper.classList.add("postbox-header");
                const titleH2 = document.createElement("h2");
                titleH2.innerHTML = "Scene Title";
                titleH2.classList.add("hndle");
                titleWrapper.append(titleH2);
                parentDiv = document.getElementById("titlewrap");
                parentDiv.classList.add("postbox");
                parentDiv.style.border = "1px solid #c3c4c7";
                childDiv = document.getElementById("title-prompt-text");
                parentDiv.insertBefore(titleWrapper, childDiv);
                const titleDiv = document.getElementById("title");
                titleDiv.style.width = "99%";
                titleDiv.style.margin = "5px";
                const titleHolder = document.getElementById("post-body-content");
                titleHolder.style.margin = "0px";
            }
         </script>
        <?php
    }
}
add_action( "shutdown", "add_explanatory_text_scene" );

// save field values
function save_post_meta_boxes(){
    if (isset($_POST['action']) && $_POST['action'] == 'editpost') {
        global $post;
        if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
            return;
        }
        update_post_meta( $post->ID, '_scene_region',  sanitize_text_field( $_POST[ '_scene_region' ] ) );
        update_post_meta( $post->ID, '_scene_intro',  sanitize_text_field( $_POST[ '_scene_intro' ] ) );
        update_post_meta( $post->ID, '_scene_info_link', sanitize_text_field( $_POST[ '_scene_info_link' ] ) );
        update_post_meta( $post->ID, '_scene_image_link', sanitize_text_field( $_POST[ '_scene_image_link' ] ) );
        update_post_meta( $post->ID, '_scene_comments', sanitize_text_field( $_POST[ '_scene_comments' ] ) );
        update_post_meta( $post->ID, 'second_featured_img', $_POST['second_featured_img'] );   
    }
}
add_action( 'save_post', 'save_post_meta_boxes' );

// turn off rearranging arrows around metaboxes
function customAdmin() {
    $screen = get_current_screen();
    if ( 'scene' == $screen->post_type ) {
        echo '<!-- custom admin css -->
        <style>
            .handle-order-higher, .handle-order-lower, .handlediv {
                display: none;
            }
        </style>
        <!-- /end custom admin css -->';
    }
}
add_action('admin_head', 'customAdmin');

// turn off dragging metaboxes
function disable_drag_metabox() {
    $screen = get_current_screen();
    if ( $screen->post_type == 'scene') {
       wp_deregister_script('postbox');
    }
// }
}
add_action( 'do_meta_boxes', 'disable_drag_metabox' );

// Change text on title
function webCR_change_title_text( $title ){
     $screen = get_current_screen();
     if  ( 'scene' == $screen->post_type ) {
          $title = 'Scene Title';
     }
     return $title;
}
add_filter( 'enter_title_here', 'webCR_change_title_text' );

// Add plugin javascript
//add_action( 'wp_enqueue_scripts', 'my_custom_script_load' );
//function my_custom_script_load(){
//  wp_enqueue_script( 'my-custom-script', plugin_dir_url( __FILE__ ) . '/custom-scripts', array( 'jquery' ) );
//}

// beginning of media library functions
function misha_include_myuploadscript() {
    /*
     * I recommend to add additional conditions just to not to load the scipts on each page
     * like:
     * if ( !in_array('post-new.php','post.php') ) return;
     */
    if ( ! did_action( 'wp_enqueue_media' ) ) {
        wp_enqueue_media();
    }

    wp_enqueue_script( 'myuploadscript', plugin_dir_url( __FILE__ ) . '/customscript.js', array('jquery'), null, false );
}

add_action( 'admin_enqueue_scripts', 'misha_include_myuploadscript' );

function misha_image_uploader_field( $name, $value = '') {
    $image = ' button">Upload image';
    $image_size = 'full'; // it would be better to use thumbnail size here (150x150 or so)
    $display = 'none'; // display state ot the "Remove image" button

    if( $image_attributes = wp_get_attachment_image_src( $value, $image_size ) ) {

        // $image_attributes[0] - image URL
        // $image_attributes[1] - image width
        // $image_attributes[2] - image height

        $image = '"><img src="' . $image_attributes[0] . '" style="max-width:95%;display:block;" />';
        $display = 'inline-block';

    } 

    return '
    <div>
        <a href="#" class="misha_upload_image_button' . $image . '</a>
        <input type="hidden" name="' . $name . '" id="' . $name . '" value="' . $value . '" />
        <a href="#" class="misha_remove_image_button" style="display:inline-block;display:' . $display . '">Remove image</a>
    </div>';
}

/*
 * Meta Box HTML
 */
function misha_print_box( $post ) {
    $meta_key = 'second_featured_img';
    echo misha_image_uploader_field( $meta_key, get_post_meta($post->ID, $meta_key, true) );
}

// Rearrange admin columns for Scene post type 
function webCR_scene_posts_columns( $columns ) {
  $columns['image'] = 'Image';
  $columns['_scene_intro'] = 'Introduction';
  return $columns;
}
add_filter( 'manage_scene_posts_columns', 'webCR_scene_posts_columns' );

function webCR_scene_admin_column( $column, $post_id ) {
  // Image column
    if ( 'image' === $column ) {
        echo get_post_meta( $post_id, 'second_featured_img', true );
       // echo get_the_post_thumbnail( $post_id, array(80, 80) );
    }
    if ( $column === '_scene_intro' ) {
        echo get_post_meta( $post_id, '_scene_intro', true );
    }
}
add_action( 'manage_scene_posts_custom_column', 'webCR_scene_admin_column', 10, 2);
