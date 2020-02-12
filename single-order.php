<?php

	acf_form_head();
    
    pI_redirect_non_admins();

	get_header();

	// Get queried object

	$cpt = get_post_type();

	$queried_object = get_post_type_object( $cpt );

	// Get cpt name

	$shared_array = array(

				"object"	=> $queried_object->name,

				"title"	=> $queried_object->labels->archives,

				"archive_link"	=> $queried_object->has_archive,

				"id"	=> $post->ID,

		"cpt" => $cpt

	);

	$cpt =  $queried_object->name;

	set_query_var( 'shared_array', $shared_array );

	if(isset($_GET["action"])) {

		$action = $_GET["action"];

		pI_get_crud_template($action);

	} else {

        ?>
<!-- Main content -->
<div class="content">
    <div class="container-fluid">
        <div class="row">
            <div class="col-lg-12">
                <div class="card card-primary card-outline mt-5 ml-3 mr-3">
                    <div class="card-header">
                        <h5 class="card-title mt-1"><?php _e( get_the_title(), 'theme' ); ?></h5>
                        <div class="card-tools">
                            <a href="<?php pI_print_home_url(); echo $shared_array['archive_link']; ?>/"
                                class="btn btn-secondary btn-sm" style="margin-top:3px">
                                <span>
                                    <span><?php _e( 'Kthehu tek lista', 'theme' ); ?></span>
                                </span>
                            </a>
                            <a href="<?php the_permalink(); ?>?action=edit" class="btn btn-primary btn-sm"
                                style="margin-top:3px">
                                <span>
                                    <span><?php _e( 'Edito', 'theme' ); ?></span>
                                </span>
                            </a>
                            <a href="<?php echo site_url(); ?>/dergesa?porosia=<?php echo get_the_ID();?>" class="btn btn-danger btn-sm"
                                style="margin-top:3px" target="_blank">
                                <span>
                                    <span><?php _e( 'Fletedergesa', 'theme' ); ?></span>
                                </span>
                            </a>
                        </div>
                    </div>
                    <div class="card-body p-5">
                        <?php

					if (have_posts()):

						while (have_posts()) :
                            the_post();
                            $produkt_id = get_field("porosi_produktet_produkti");
                            $produkt_code = get_field("produkt_material_number", $produkt_id);

                            $klienti = get_field("porosi_klienti"); 
                            $sasia = (int) get_field("porosi_produktet_sasia");
                            $produkt_price = floatval(get_field("porosi_cmimi"));
                            $cmimi_me_zbritje = floatval(get_field("porosi_cmimi_me_zbritje"));

                            $manual = "";

                            if( $produkt_price ) {
                            
                                $cmimi_total = $produkt_price * $sasia;
                                // $zbritja = pI_get_client_discount($klienti);

                                $cmimi_me_zbritje = $cmimi_me_zbritje * $sasia;

                                $manual = "Manualisht";

                            } else {

                                $produkt_price = get_field("produkt_price", $produkt_id);
                                $cmimi_total = floatval(pI_get_product_price($produkt_id)) * $sasia;
                                $zbritja = pI_get_client_discount($klienti);
                                $cmimi_me_zbritje = pI_discounted_price($cmimi_total, $zbritja);
                                
                                $manual = "Automatikisht";
                            }


                            ?>
                        <div class="col-xs-12">
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th colspan="2" style="font-size:16px;">
                                            <?php _e( "Informatat e Porosise", 'theme' ); ?>
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Klienti</td>
                                        <td nowrap="nowrap">
                                            <?php 
                                            
                                            echo $klienti["user_nicename"];
                                             ?></td>

                                    </tr>
                                    <tr>
                                        <td>Data e Porosise</td>
                                        <td nowrap="nowrap">
                                            <?php the_field("porosi_data"); ?>

                                        </td>

                                    </tr>
                                    <tr>
                                        <td nowrap="nowrap" style="font-size:14px;">
                                            Produkti
                                        </td>
                                        <td nowrap="nowrap">
                                            <strong><?php echo $produkt_code; ?></strong>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td nowrap="nowrap" style="font-size:14px;">
                                            Sasia
                                        </td>
                                        <td nowrap="nowrap">
                                           <?php echo $sasia; ?> cope
                                        </td>
                                    </tr>
                                    <tr>
                                        <td nowrap="nowrap" style="font-size:14px;">
                                            Cmimi produktit
                                        </td>
                                        <td nowrap="nowrap">
                                            <?php echo $produkt_price; ?>€
                                        </td>
                                    </tr>
                                    <tr>
                                        <td nowrap="nowrap" style="font-size:14px;">
                                           Total
                                        </td>
                                        <td nowrap="nowrap">
                                            <?php echo $cmimi_total; ?>€
                                        </td>
                                    </tr>
                                    <tr>
                                        <td nowrap="nowrap" style="font-size:14px;">
                                            Totali me zbritje
                                        </td>
                                        <td nowrap="nowrap">
                                            <?php 
                                            echo $cmimi_me_zbritje;
                                             ?>€
                                        </td>
                                    </tr>
                                    <tr>
                                        <td nowrap="nowrap" style="font-size:14px;">
                                            Statusi i Porosise
                                        </td>
                                        <td>
                                            <?php the_field("porosi_statusi"); ?> : <?php echo $manual; ?>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td nowrap="nowrap" style="font-size:14px;">
                                            Statusi i Pageses
                                        </td>
                                        <td>
                                            <?php the_field("porosi_pagesa"); ?>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <?php
						endwhile;

					endif;

					wp_reset_postdata();
?>
                    </div>
                </div>
            </div>
            <!-- /.col-md-6 -->
        </div>
        <!-- /.row -->
    </div><!-- /.container-fluid -->
</div>
<!-- /.content -->


<?php

	}

	get_footer();

?>
