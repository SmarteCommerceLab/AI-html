<?php if (is_admin()){
/* Post Sub Title - Aggiunta Meta Box					*/
add_action('add_meta_boxes',function(){add_meta_box('aihtml_post_sub_title','AI-HTML - Testi Aggiuntivi','aihtml_sub_title_post_meta_box','post','post_after_title','high',null);});
function aihtml_sub_title_post_meta_box($object,$box){?>
	<?php wp_nonce_field('aihl_save_post_subtitle', 'aihl_post_subtitle_nonce'); ?>
    <div>
        <div>
            <strong>Occhiello</strong></br>
            <span>Aggiungi un sottotitolo all'articolo </span>
        </div>
        <input 
            type 			= 'text'
            name			= 'post-sub-title-value'
            value			= '<?php echo esc_attr(get_post_meta($object->ID,'post-sub-title-value',true)); ?>'
            style 			= 'margin-top:10px;padding: 3px 8px;width: 100%;outline: 0;background-color: #fff;'
            placeholder 	= 'Aggiungi Sottotitolo'
            autocomplete 	= 'off'
        />        
    </div>
<?php }
/* Post Sub Title - Salvataggio 						*/
add_action('save_post',function($post_id,$post){global $pagenow;
	if (wp_is_post_autosave($post_id) || wp_is_post_revision($post_id)) return $post_id;
	if (!isset($_POST['aihl_post_subtitle_nonce']) || !wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['aihl_post_subtitle_nonce'])), 'aihl_save_post_subtitle')) return $post_id;
	// -- Verifica Permessi
	if(!current_user_can('edit_post',$post_id))return $post_id;
	// -- Verifica Pagina
	if(($pagenow != 'post.php') || ('post' !== get_post_type($post)))return $post_id;
	$new_meta_value = isset($_POST['post-sub-title-value'])
		? sanitize_text_field(wp_unslash((string) $_POST['post-sub-title-value']))
		: '';
	if ('' === $new_meta_value) {
		delete_post_meta($post_id, 'post-sub-title-value');
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
