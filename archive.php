<?php 
	get_header();

	// Get queried object

	$queried_object = get_queried_object();

	// Get cpt name

	$shared_array = array(

			"object"	=> $queried_object->name,

			"title"	=> $queried_object->labels->archives,

			"archive_link"	=> $queried_object->has_archive,

			"add_new"	=> $queried_object->labels->add_new_item

	);

	$cpt =  $queried_object->name;

	set_query_var( 'shared_array', $shared_array );

	pi_get_list_template_of($cpt);

	get_footer();

?>
