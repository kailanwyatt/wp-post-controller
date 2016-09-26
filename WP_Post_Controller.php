<?php

if( ! class_exists('WP_Post_Controller') ):

class WP_Post_Controller{
	/**
	 * [$id description]
	 * @var int
	 */
	public $id;
	/**
	 * [$post_slug description]
	 * @var [type]
	 */
	public $post_slug;
	/**
	 * [$post_content description]
	 * @var [type]
	 */
	public $post_content;
	/**
	 * [$post_author description]
	 * @var [type]
	 */
	public $post_author;
	/**
	 * [$post_title description]
	 * @var [type]
	 */
	public $post_title;
	/**
	 * [$post_status description]
	 * @var [type]
	 */
	public $post_status;
	/**
	 * [$post_type description]
	 * @var [type]
	 */
	public $post_type;
	/**
	 * [$post_excerpt description]
	 * @var [type]
	 */
	public $post_excerpt;
	/**
	 * [$post description]
	 * @var [type]
	 */
	public $post;
	/**
	 * [__construct description]
	 * @param integer $post_id [description]
	 */
	public function __construct($post_id = 0){
		$this->init( $post_id );
	}
	/**
	 * [init description]
	 * @param  [type] $post_id [description]
	 * @return [type]          [description]
	 */
	protected function init( $post_id ){
		$this->id = absint( $post_id );
		if($this->id){
			$this->post = get_post( $this->id );
			$this->post_title = $this->get_title();
			$this->post_content = $this->post->post_content;
			$this->post_status = $this->post->post_status;
		}
	}
	/**
	 * [set_author description]
	 * @param [type] $author [description]
	 */
	public function set_author($author){
		if( ! is_int( $author ) ){
			 if (!username_exists( $author ) ){
			 	$this->post_author = '';
			 }
			 $user = get_user_by( 'login', $author );
			 $author = $user->id;
		}
		$this->post_author = int( $author );
	}
	/**
	 * [get_author description]
	 * @return [type] [description]
	 */
	public function get_author(){
		return $this->post_author;
	}
	/**
	 * [set_post_type description]
	 * @param [type] $post_type [description]
	 */
	public function set_post_type($post_type){
		$this->post_type = $post_type;
	}
	/**
	 * [get_post_type description]
	 * @return [type] [description]
	 */
	public function get_post_type(){
		return $this->post_type;
	}
	/**
	 * [get_thumbnail_id description]
	 * @return [type] [description]
	 */
	public function get_thumbnail_id(){
		return get_post_thumbnail_id( $this->id );
	}
	/**
	 * [get_thumbnail_src description]
	 * @param  string $size [description]
	 * @return [type]       [description]
	 */
	public function get_thumbnail_src( $size = 'thumbnail' ){
		$attachment_id = $this->get_thumbnail_id();
		$image_attributes = wp_get_attachment_image_src( $attachment_id, $size ); // returns an array
		if( $image_attributes ) {
			return $image_attributes[0];
		}
		return false;
	}
	/**
	 * [create description]
	 * @param  [type] $args [description]
	 * @return [type]       [description]
	 */
	public function create( $args = array() ){
		$result = array();
		$post_type = $this->post_type;
		$defaults = array (
			'post_type' 	=> $this->post_type,
			'post_author'   => $this->get_author(),
			'post_status' 	=> "draft"
		);

		$args = wp_parse_args( $args, $defaults );
		$args = apply_filters( "wp_post_controller_pre_{$post_type}_args", $args );
		//if post_id exists then perform update else insert
		if( empty($this->id) ){
			$this->id 	= wp_insert_post( $args );
			do_action('wp_post_controller_after_insert', $this->id, $args);
			do_action("wp_post_controller_after_{$post_type}_insert", $this->id, $args);
		}else{
			$args['ID'] = $this->id;
			$this->id 	= wp_update_post( $args );
			do_action('wp_post_controller_after_update', $this->id, $args);
			do_action('wp_post_controller_after_{$post_type}_update', $this->id, $args);
		}

		if ( is_wp_error( $this->id) ) {
			$errors = $post_id->get_error_messages();
			$result['result'] = false;
			foreach ($errors as $error) {
				$result['error_messages'][] =  $error;
			}
		}else{
			$result['result'] 	= true;
			$result['post_id'] 	= $this->id;
			do_action( 'wp_post_controller_save_complete', $this->id );
			do_action( "wp_post_controller_{$post_type}_save_complete", $this->id );
		}
		return apply_filters('wp_post_controller_return_args', $result);
	}
	/**
	 * [after_post_save description]
	 * @param  string $post_id [description]
	 * @return [type]          [description]
	 */
	public function after_post_save( $post_id = '' ){

	}
	/**
	 * [get_post description]
	 * @return [type] [description]
	 */
	public function get_post(){
		if( empty($this->id) ){
			return false;
		}
		return $this->post;
	}

	/**
	 * [delete description]
	 * @param  boolean $force_delete [description]
	 * @return [type]                [description]
	 */
	public function delete( $force_delete = false ){
		return wp_delete_post( $this->id, $force_delete );
	}

	/**
	 * [set_title description]
	 * @param [type] $title [description]
	 */
	public function set_title($title){
		$post_array();
		if(!$title){
			return false;
		}
		$post_array['post_title'] = sanitize_text_field($title);
		$this->update_post($post_array);
	}
	/**
	 * [get_title description]
	 * @return [type] [description]
	 */
	public function get_title(){
		return $this->post->post_title;
	}
	/**
	 * [set_slug description]
	 * @param [type] $slug [description]
	 */
	public function set_slug( $slug ){
		$post_array();
		if( empty( $slug ) ){
			return false;
		}
		$post_array['post_name'] = sanitize_text_field( $slug );
		$this->update_post( $post_array );
	}

	public function get_slug(){
		return $this->post_slug;
	}

	public function set_content(){

	}

	public function update_post( $post_array = array() ){
		 if( $this->id && ! empty( $post_array )){
		 	wp_update_post( $post_array );
		 }
	}

	public function get_posts( $args,  $per_page = 20, $page = 1){
		$offset = $per_page * $page;
		$paged = ( get_query_var('paged') ) ? get_query_var('paged') : $page;
		$defaults = array (
			'posts_per_page' => $per_page,
			'paged' => $paged
		);

		$args = wp_parse_args( $args, $defaults );
		$args = apply_filters('wp_post_controller_get_query_args', $args);
		$posts = get_posts( $args );
		return $posts;
	}

	public function get_meta( $meta_key='', $single=true ){
		if( empty($meta_key) ){
			return;
		}
		return get_post_meta($this->id, $meta_key, $single);
	}
	/**
	 * [reArrayFiles description]
	 * @param  [type] $file_post [description]
	 * @return [type]            [description]
	 */
	public function reArrayFiles( $file_post ) {

		$file_array = array();
		$file_count = count( $file_post['name'] );
		$file_keys = array_keys( $file_post );

		for ($i=0; $i<$file_count; $i++) {
			foreach ( $file_keys as $key ) {
				$file_array[$i][$key] = $file_post[$key][$i];
			}
		}

		return $file_array;
	}
	/**
	 * Upload files to Media folder
	 * @param  string  $files_key         [description]
	 * @param  boolean $insert_attachment [description]
	 * @param  boolean $overwrite         [description]
	 * @return [type]                     [description]
	 */
	public function upload_media( $files_key='', $insert_attachment = true, $overwrite = true ){
		if ( ! function_exists( 'wp_handle_upload' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/file.php' );
		}
		// These files need to be included as dependencies when on the front end.
		require_once( ABSPATH . 'wp-admin/includes/image.php' );
		require_once( ABSPATH . 'wp-admin/includes/file.php' );
		require_once( ABSPATH . 'wp-admin/includes/media.php' );
		if(empty($files_key)){
			return false;
		}
		if(empty($_FILES[$files_key])){
			return false;
		}
		$result = array();
		$upload_overrides = array( 'test_form' => false );
		$attachment_id = media_handle_upload( $files_key, $this->id);
		if ( !is_wp_error( $attachment_id ) ) {
			if($insert_attachment){
				if($overwrite){
					//$this->delete_post_media( $this->id );
				}
				//$movefile['extension'] = $file_type['ext'];
				//$movefile['attachment_id'] = $attach_id;
			}
			$result[] = $attachment_id;
		}else{
			  $error_string = $attachment_id->get_error_message();
		}
		return $result;
	}

	/**
	 * [delete_post_media description]
	 * @param  [type] $post_id [description]
	 * @return [type]          [description]
	 */
	public function delete_post_media( $post_id ) {

		$attachments = get_posts( array(
			'post_type'      => 'attachment',
			'posts_per_page' => -1,
			'post_status'    => 'any',
			'post_parent'    => $post_id
		) );

		foreach ( $attachments as $attachment ) {
			if ( false === wp_delete_attachment( $attachment->ID ) ) {
				// Log failure to delete attachment.
			}
		}
	}
}

endif;
