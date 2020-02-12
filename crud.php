<?php 
/*------------------------------------*\
    Crud Operations and Functions
\*------------------------------------*/

// List views
function pI_get_list_template_of($cpt) {
    get_template_part('template-parts/crud/object/list');
}

// Read and Edit views
function pI_get_crud_template($action) {
    get_template_part('template-parts/crud/object/' . $action);
}

// Function to print home url
function pI_print_home_url() {
    echo esc_url( home_url( '/' ) );
}

// Function to call other template parts
function pI_other_template($template) {
    get_template_part('template-parts/other/' . $template );
}

// Fields table for View
function pI_view_fields_table() { 
    $fields = get_field_objects();
    $parent_id = array_values($fields)[0]['parent'];
    $group_name = get_the_title($parent_id); ?>

    <div class="col-xs-12">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th colspan="2" style="font-size:16px;">
                        <?php _e( $group_name, 'theme' ); ?>
                    </th>
                </tr>
            </thead>
            <tbody>
                <?php 

                $dt_fill = "";
                $dt_mbar = "";

                foreach($fields as $field) { 
                    if($field['hide_in_views'] == 1) {
                        continue;
                    } ?>
                
                <tr>
                    <td nowrap="nowrap" style="font-size:14px;">
                        <?php $label = trim($field["label"]); _e($label, 'theme' ); ?>
                    </td>
                    <td nowrap="nowrap">

                        <?php 
                        $field_type = $field['type'];
                        echo $field["value"];
                        ?>
                    </td>
                </tr>

                <?php } ?>
            </tbody>
        </table>
    </div>
<?php }

// Check if hide in table is selected
function pI_hide_in_table($content) {
    $un_s = unserialize($content);
    $key = "hide_in_table";

    if(array_key_exists($key, $un_s) && $un_s[$key] == 1) {
        return true;
    } else {
        return false;
    }
}

// Fields table for List
function pI_list_fields_table($post_type) { ?>
    <table id="example2" class="table table-bordered table-striped">
        <thead>
            <tr>
                <?php 

                $fields_group_ids = pI_get_field_group_id($post_type); 
                $all_fields = array();

                foreach($fields_group_ids as $fields_group_id) {

                    $args = array(
                            'post_parent' => $fields_group_id,
                            'post_type'   => 'acf-field', 
                            'posts_per_page' => -1,
                            'order'=> 'ASC',
                            'post_status' => 'any' 
                        );

                    $fields = get_children( $args );

                    if( $fields ): 
                        
                        foreach( $fields as $field ): 

                            array_push($all_fields, $field);
                            $content =  $field->post_content;

                            if(pI_hide_in_table($content)) {
                                continue;
                            }

                            echo "<th>";
                            print_r($field->post_title);
                            echo "</th>";

                        endforeach;

                        wp_reset_postdata();
                    
                    endif; 

                    wp_reset_query();

                } ?>
                <th class="text-right">
                    <?php _e( 'Veprimet', 'theme' ); ?>
                </th>
            </tr>
        </thead>
        <tbody>
            <?php
                
                $args = array( 'post_type' => $post_type, 'post_status' => 'publish', 'posts_per_page' => -1 );

                $loop = new WP_Query( $args );

                while ( $loop->have_posts() ) : $loop->the_post(); ?>
                    <tr>
                        <?php 

                        foreach( $all_fields as $field ): 

                            $content =  $field->post_content;

                            if(pI_hide_in_table($content)) {
                                continue;
                            }

                            echo "<td>";

                            if(get_field($field->post_excerpt)) {

                                $current_field = get_field_object($field->post_excerpt);
                                the_field($field->post_excerpt);
                            }

                            echo "</td>";

                        endforeach; 

                        $the_link = get_permalink(); 
                        $del_link =  get_delete_post_link( get_the_ID() ); ?>

                        <td class='text-right' nowrap='nowrap'>
                            <a href='<?php echo $the_link; ?>' class='btn btn-success btn-sm'>
                                <i class='fa fa-eye'></i>
                            </a>
                            <a href='<?php echo $the_link; ?>?action=edit' class='btn btn-primary  btn-sm'>
                                <i class='fa fa-edit'></i>
                            </a>
                            <a onclick="return confirm('<?php _e( 'Are you sure you want to delete this record?', 'theme' ); ?>');" class="btn btn-danger btn-sm" href="<?php echo get_delete_post_link(); ?>">
                                <i class='fa fa-times'></i>
                            </a>
                            <?php 
                                if($post_type === "invoice") {

                                    $invoice_id = get_field("invoice_number", get_the_id());
                                    
                                    // show invoice button
                                    echo "<a target='_blank' href='" .  home_url()  . "/invoice?invoice_id=" . $invoice_id . "' class='btn btn-info  btn-sm'>
                                                <i class='fa fa-print'></i> 
                                            </a>";
                                }

                            ?>
                        </td>
                    </tr>

                <?php endwhile; ?>

        </tbody>
    </table>
<?php }

// Returns cpt values
function pI_get_post_type_values( $post_type ) {
    $values = array();

    $defaults = 
        array(
            'post_type' => $post_type,
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'title',
            'order' => 'ASC'
        );

    $query = new WP_Query( $defaults );

    if ( $query->found_posts > 0 ) {

        foreach ( $query->posts as $post ) {
            $value = $post_type === "item" ? get_the_title( $post->ID ) . " - " . get_field("item_price", $post->ID ) . "€" : get_the_title( $post->ID ) ;
          $values[$post->ID] = $value;
        }

    }

    return $values;
} ?>
