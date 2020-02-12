<?php 	

/*------------------------------------*\
    Custom Theme Functions
\*------------------------------------*/
function pI_get_latest_invoice() {
    $args = array(
        'numberposts' => 1,
        'orderby' => 'post_date',
        'order' => 'DESC',
        'post_type' => 'invoice',
        'post_status' => 'draft, publish, future, pending, private'
    );
    
    $recent_posts = wp_get_recent_posts( $args, ARRAY_A );

    return $recent_posts[0]["ID"];
}

function pI_invoice_number() {
    $x = pI_get_latest_invoice();
    $x++;
    return($x);
}

function pI_get_invoice($invoice_id) {
    $posts = get_posts(array(
        'numberposts'	=> 1,
        'post_type'		=> 'invoice',
        'meta_key'		=> 'invoice_number',
        'meta_value'	=> $invoice_id
      ));

      return $posts[0];
}

function pI_get_porosi($porosi_id) {
    $posts = get_posts(array(
        'numberposts'	=> 1,
        'p'             => $porosi_id,
        'post_type'		=> 'porosi'
      ));

      return $posts[0];
}


/*------------------------------------*\
    CPT Post Titles
\*------------------------------------*/

function pI_posts_titles($post_id) {

    $post_type = get_post_type($post_id);
    $title = "";
    $pt_name =  $post_type . "_name";

    $dont_include = array(
        'post' => "post",
        'page' => "page",
        'attachment' => "attachment",
        'revision' => "revision",
        'nav_menu_item' => "nav_menu_item",
        'custom_css' => "custom_css",
        'customize_changeset' => "customize_changeset",
        'oembed_cache' => "oembed_cache",
        'user_request' => "user_request",
        'wp_block' => "wp_block",
        'acf-field-group' => "acf-field-group",
        'acf-field' => "acf-field"
    );

    if(!in_array($post_type, $dont_include)) {
        // check for post type
        switch($post_type) {
            case "invoice":
                // Get invoice number in sequence
                $invoice_number = pI_invoice_number();
                $title = "INV" . $invoice_number;
                update_field( 'field_5da8c74d4a6ca', $title, $post_id );
                break;

                case "produkt":

                    $title = get_field("produkt_material_number", $post_id);
                    break;
                case "cmimore":

                    $title = get_field("cmimore_emri", $post_id);
                    break;

                case "porosi":

                    $title = "Porosia #" . $post_id;
                    break;

                case "stok":
                    $produkti = get_field("stok_produkti", $post_id);
                    $title = get_field("produkt_eurocode", $produkti);
                    break;
            
            case "request":
                // Prepare title
                $title = "REQ" . $post_id; 
                $key = pI_get_field_key('request_number', $post_id);

                // update request_number
                update_field( $key, $title, $post_id );
                break;

            default:
                $title = get_field($pt_name, $post_id); 
                break;
        }
        // set default date
        pI_set_default_date($post_id);
    }
    
    $content = array(
        'ID' => $post_id,
        'post_title' => $title
    );

    remove_action('acf/save_post', 'pI_posts_titles');
    wp_update_post($content);
    add_action('save_post', 'pI_posts_titles');
}
add_action('acf/save_post', 'pI_posts_titles', 10, 1);


function pI_get_client_name( $value, $post_id, $field )
{
    // run the_content filter on all textarea values
    $value = get_field("client_name", $value); 

    return $value;
}
add_filter('acf/load_value/name=invoice_client', 'pI_get_client_name', 10, 3);
add_filter('acf/load_value/name=request_client', 'pI_get_client_name', 10, 3);

function pI_get_stock_item_name( $value, $post_id, $field )
{
    $field = get_field("stock_item_name", $value);
    return $field;
}
add_filter('acf/load_value/name=item_stock_item', 'pI_get_stock_item_name', 10, 3);



function pI_set_default_date($post_id) {
    // get all fields
    $fields=get_field_objects($post_id);

    // loop fields
    foreach($fields as $field) {
    // check if field is date and is empty
      if($field["type"] === "date_picker" && empty($field["value"])) {
        // set today date
        update_field($field["key"], date("Y-m-d"), $post_id);
      }
    } 
}

function pI_get_field_key($field_name, $post_id){
    // get field key
    $field_object = get_field_object($field_name, $post_id);
    return $field_object['key'];
} 

function pI_get_product_price($post_id) {
    $price = get_field_object("produkt_price", $post_id);
    return $price["value"];
}

function pI_get_client_discount($user) {
    $cmimorja = get_field("klient_cmimorja", "user_" . $user["ID"]);
    $zbritja = get_field("cmimore_perqindja", $cmimorja);

    return $zbritja;
}

function pI_discounted_price($cmimi, $discount) {
    $nje_perqind = floatval($cmimi) / 100.00;
    $zbritja = floatval($discount) * $nje_perqind;

    $total = $cmimi - $zbritja;
    return number_format((float)floatval($total), 2, '.', '');



}


//get parent cats 
function pI_parent_cats() {
    $parent_cats = get_categories( array('hide_empty' => 0, 'parent' => 0) );
    $array = array();

    foreach($parent_cats as $parent_cat) {
        $array[$parent_cat->term_id] = $parent_cat->name  ;
    }

    return $array;
}

function pI_child_cats($parent_id) {
    $parent_cats = get_categories( array('hide_empty' => 0, 'parent' => $parent_id) );
    $array = array();

    foreach($parent_cats as $parent_cat) {
        $array[$parent_cat->term_id] = $parent_cat->name  ;
    }

    return $array;
}


function pI_redirect_non_admins() {
    $user = wp_get_current_user();
    if (!in_array( 'puntor', (array) $user->roles ) && in_array( 'klient', (array) $user->roles ) ) {
        wp_redirect( home_url('/klient-produktet') ); 
		exit();
    }
}

function pI_user_is_admin() {
    $user = wp_get_current_user();
    if ( in_array('administrator', (array) $user->roles ) ) {
       return true;
    } else {
        return false;

    }
}

function pI_client_home($link) {
    echo home_url('/klient-' . $link . '/');
}

function pI_block_admin() {
    $user = wp_get_current_user();

    if( ( !defined('DOING_AJAX') || ! DOING_AJAX ) && ( empty( $user ) || !in_array( "administrator", (array) $user->roles ) ) ) {
        wp_safe_redirect(home_url());
        exit;
    }
}
add_action( 'admin_init', 'pI_block_admin' );


function my_custom_login_stylesheet() {
    // wp_enqueue_style( 'custom-login', get_stylesheet_directory_uri() . '/assets/css/adminlte.min.css' );
    wp_enqueue_style( 'custom-login2', get_stylesheet_directory_uri() . '/assets/css/style-login.css' );
    wp_enqueue_script( 'jquery-login', get_stylesheet_directory_uri() . '/assets/libs/jquery/jquery-3.0.0.min.js' );
    wp_enqueue_script( 'custom-login', get_stylesheet_directory_uri() . '/assets/js/style-login.js', array(), '1.0.0', true );
}
//This loads the function above on the login page
add_action( 'login_enqueue_scripts', 'my_custom_login_stylesheet' );

// 
function login_error_override()
{
    return 'Detalet e casjes jane gabim!';
}
add_filter('login_errors', 'login_error_override');


// admin redirect
function admin_login_redirect( $redirect_to, $request, $user ) {
   global $user;
   
   if( isset( $user->roles ) && is_array( $user->roles ) ) {
      if( in_array( "administrator", $user->roles ) ) {
        return $redirect_to;
      } 
      else {
        return home_url();
      }
   }
   else {
   return $redirect_to;
   }
}
// add_filter("login_redirect", "admin_login_redirect", 10, 3);



function login_checked_remember_me() {
  add_filter( 'login_footer', 'rememberme_checked' );
}
add_action( 'init', 'login_checked_remember_me' );

function rememberme_checked() {
  echo "<script>document.getElementById('rememberme').checked = true;</script>";
}


function add_theme_caps() {
    $role = get_role( 'puntor' );
    $role->add_cap( 'upload_files' );
    $role->add_cap( 'edit_published_posts' );
    $role->add_cap( 'edit_others_posts' );
}
add_action( 'admin_init', 'add_theme_caps');



// Post view counter

function gt_get_post_view() {
    $count = get_post_meta( get_the_ID(), 'post_views_count', true );
    if($count == 1) {
        return "$count shikim";
    } else {
        return "$count shikime";
    }
}
function gt_set_post_view() {
    $key = 'post_views_count';
    $post_id = get_the_ID();
    $count = (int) get_post_meta( $post_id, $key, true );
    $count++;
    update_post_meta( $post_id, $key, $count );
}
function gt_posts_column_views( $columns ) {
    $columns['post_views'] = 'Shikimet';
    return $columns;
}
function gt_posts_custom_column_views( $column ) {
    if ( $column === 'post_views') {
        echo gt_get_post_view();
    }
}
add_filter( 'manage_produkt_posts_columns', 'gt_posts_column_views' );
add_action( 'manage_produkt_posts_custom_column', 'gt_posts_custom_column_views' );




// stok number
function pI_stok_number($produkti) {
    $produkti = get_post($produkti);
    $posts = get_posts(array(
	'numberposts'	=> -1,
	'post_type'		=> 'stok',
	'meta_key'		=> 'stok_produkti',
	'meta_value'	=> $produkti->ID
));

    $stok_id = $posts[0]->ID;
    

    return get_field("stok_sasia", $stok_id);
}

function pI_raft_number($produkti) {
    $produkti = get_post($produkti);
    $posts = get_posts(array(
	'numberposts'	=> -1,
	'post_type'		=> 'stok',
	'meta_key'		=> 'stok_produkti',
	'meta_value'	=> $produkti->ID
));

    $stok_id = $posts[0]->ID;
    

    return get_field("stok_rafti", $stok_id);
}

function pI_get_object_by_acf($object, $key, $value) {
    $posts = get_posts(array(
        'numberposts'=> -1,
        'post_type'=> $object,
        'meta_key' => $key,
        'meta_value' => $value
    ));

    return $posts[0];
}



//stock information
function pI_in_stock($produkt_id) {
    if(pI_stok_number($produkt_id) === NULL || pI_stok_number($produkt_id) == 0) {
        return false;
    } else {
        return true;
    }
}

function pI_decrease_stock($produkt_id, $number) {
    if(pI_in_stock($produkt_id) && pI_stok_number($produkt_id) >= $number) {
        $stok_item = pI_get_object_by_acf("stok", "stok_produkti", $produkt_id);
        $stok_id = $stok_item->ID;
        
        $sasia_aktuale = (int) get_field("stok_sasia", $stok_id);
        $sasia = $sasia_aktuale - $number;

        $sasia_key = pI_get_field_key("stok_sasia", $stok_id);
        update_field($sasia_key, $sasia, $stok_id);
        // return get_field("stok_sasia", $stok_id);

        return true;
    } else {
        return false;
    }
}



function pI_get_field_porosi_produktet_produkti($field) {
    $choices = $field["choices"];
    $choices2 = array();

    // modify array
    foreach($choices as $key => $value) {
        if(pI_in_stock($key)) {
            $choices2[$key] = $value;
        }
        $field["choices"] = $choices2;
    }
    
    return $field;
}
// add_filter('acf/load_field/name=porosi_produktet_produkti', 'pI_get_field_porosi_produktet_produkti');

function pI_get_field_porosi_produktet($field) {
    echo "<pre>";
    var_dump($field);
    echo "</pre>";
    return $field;
}
// add_filter('acf/load_field/name=porosi_produktet', 'pI_get_field_porosi_produktet');

function pI_get_products() {
$request  = new WP_REST_Request( 'GET', '/wp/v2/produkt' );
$response = rest_do_request( $request );
$data     = rest_get_server()->response_to_data( $response, true );

return $data;
}


function my_acf_save_post( $post_id ) {

    if( get_post_type($post_id) !== 'porosi') {
        
        return;
        
    }

    $flag = get_post_meta($post_id, 'my_flag_field', true);

    if ($flag == 'already done') {
        // this is not a new post
        return;
    }
    // set flag
    update_post_meta($post_id, 'my_flag_field', 'already done');
    
    $post = get_post( $post_id );
	
	
	// get custom fields (field group exists for content_form)
	$produkti = get_field('porosi_produktet_produkti', $post_id);
	$sasia = get_field('porosi_produktet_sasia', $post_id);
    
    pI_decrease_stock($produkti,$sasia);
    // do other stuff to new post

}
add_action('acf/save_post', 'my_acf_save_post', 20);


?>
