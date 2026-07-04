<?php if (is_admin()){
/* Post Sub Title - Aggiunta Meta Box					*/
add_action('add_meta_boxes',function(){add_meta_box('aihtml_post_sub_title','AI-HTML - Testi Aggiuntivi','aihtml_sub_title_post_meta_box','post','post_after_title','high',null);});
function aihtml_sub_title_post_meta_box($object,$box){?>
    <div>
        <div>
            <strong>Occhiello</strong></br>
            <span>Aggiungi un sottotitolo all'articolo </span>
        </div>
        <input 
            type 			= 'text'
            name			= 'post-sub-title-value'
            value			= '<?php echo esc_html(get_post_meta($object->ID,'post-sub-title-value',true),1); ?>' 
            style 			= 'margin-top:10px;padding: 3px 8px;width: 100%;outline: 0;background-color: #fff;'
            placeholder 	= 'Aggiungi Sottotitolo'
            autocomplete 	= 'off'
        />        
    </div>
<?php }
/* Post Sub Title - Salvataggio 						*/
add_action('save_post',function($post_id,$post){global $pagenow;
	// -- Verifica Permessi
	if(!current_user_can('edit_post',$post_id))return $post_id;
	// -- Verifica Pagina
	if(($pagenow != 'post.php') || (get_post_type() != 'post'))return $post_id;
	// -- 
	/*$meta_value 		= get_post_meta( $post_id, 'post-sub-title-value', true );
	if(isset($_POST['post-sub-title-value']) and !empty($_POST['post-sub-title-value'])){
	$new_meta_value 	= stripslashes( $_POST['post-sub-title-value']);}*/
	//-- Verifica POST Value
	if(!isset($_POST['post-sub-title-value']) or empty($_POST['post-sub-title-value'])){return $post_id;}
	// -- recupero valore
	$meta_value = get_post_meta( $post_id, 'post-sub-title-value', true );
	// -- recupero request
	if(isset($_POST['post-sub-title-value']) and !empty($_POST['post-sub-title-value'])){$new_meta_value = stripslashes($_POST['post-sub-title-value']);}
	// -- Verifica cambiamento
	if ( '' == $new_meta_value && $meta_value ){
		delete_post_meta($post_id,'post-sub-title-value',$meta_value);
	}else{
		update_post_meta($post_id,'post-sub-title-value',$new_meta_value);
	}
}, 10, 2 );
/* Post Sub Title - Posiziona al di sotto del Titolo 	*/
add_action('edit_form_after_title',function(){global $post, $wp_meta_boxes;
	// --
    do_meta_boxes(get_current_screen(),'post_after_title',$post);
	// --
    unset($wp_meta_boxes['post']['post_after_title'] );
});
/* */
add_action('admin_head', function(){
	echo '<style>
	#post_after_title-sortables{margin-top:1em;}
	</style>';
});

/**/}?>