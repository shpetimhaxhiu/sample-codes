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

		pI_get_crud_template("view");

	}

	get_footer();

?>
