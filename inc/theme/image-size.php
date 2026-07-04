<?php if (is_admin()){
/*
* WordPress: Remove unwonted image sizes.
* In this code I remove the three sizes medium_large, 1536x1536, 2048x2048
* https://bloggerpilot.com/en/disable-wordpress-image-sizes/
*/
add_filter('intermediate_image_sizes',function($sizes) {
	$targets = ['medium_large','1536x1536','2048x2048','Medium_large'];
	foreach($sizes as $size_index=>$size){if(in_array($size, $targets)){unset($sizes[$size_index]);}}
return $sizes;},10,1);
/*
* Adding Theme size image 16:9 ratio
* https://developer.wordpress.org/reference/functions/add_image_size/
*/
add_action('init',function() 							{
 	add_image_size('1024x576'	,1024,576	,array('center','top'), true); // (true=crop,false=not crop)
	add_image_size('768x432'	,768,432	,array('center','top'), true); // (true=crop,false=not crop)
	add_image_size('640x360' 	,640,360	,array('center','top'), true); // (true=crop,false=not crop)
	#add_image_size('570x321' 	,570,321	,array('center','top'), true); // (true=crop,false=not crop)
	add_image_size('425x239' 	,425,239	,array('center','top'), true); // (true=crop,false=not crop)
	add_image_size('360x203' 	,360,203	,array('center','top'), true); // (true=crop,false=not crop)
});
/*
*
*/
/*add_filter( 'image_size_names_choose',function($sizes) {
    return array_merge( $sizes, array(
        '1024px' => __('Single Image Large'),
    ));
});*/
}