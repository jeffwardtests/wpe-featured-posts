<?php
/**
 * Featured posts widget.
 *
 * @package    WPE_FP
 * @subpackage WPE_FP/admin
 * @author     Jeff Ward <hi@hello-jeff.com>
 */

  class WPE_FP_Widget extends WP_Widget {

 	 ///////////////////////////////
 	 # Constructor function
 	 ///////////////////////////////

 	 function __construct() {
 		 parent::__construct(

 			 # Widget base ID
 			 'wpe_fp_widget',

 			 # Widget title that will appear in UI
 			 __('WPE Featured Posts'),

 			 // Widget description
 			 array( 'description' => __( 'Display featured posts' ), )

 		 );
 	 }

 	 /////////////////
 	 # Edit Widget
 	 /////////////////
 	 public function form( $instance ) {

 		 //////////////////////////
  		 # Title
 		 //////////////////////////
 		 $title = (isset($instance['title'])) ? $instance['title'] : 5;
 		 ?>
 		 <p>
 		   <label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label>
 		   <input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
 		 </p>
 		 <?php

 		 //////////////////////////
  		 # Number of items
 		 //////////////////////////
  		 $limit = (isset($instance['limit'])) ? $instance['limit'] : 5;
  		 ?>
  		 <p>
  			 <label for="<?php echo $this->get_field_id( 'limit' ); ?>"><?php _e( 'Number of posts to display:' ); ?></label>
  			 <input class="widefat" id="<?php echo $this->get_field_id( 'limit' ); ?>" name="<?php echo $this->get_field_name( 'limit' ); ?>" type="text" value="<?php echo esc_attr( $limit ); ?>" />
  		 </p>
  		 <?php

 		 //////////////////////////
 		 # Display thumbnails?
 		 //////////////////////////
 		 $show_thumbnails = (!empty($instance['show_thumbnails'])) ? true : false;
 		 $checked = (!empty($show_thumbnails)) ? 'checked="checked"' : '';
 		 ?>
 		 <p>
 		  <label for="<?php echo $this->get_field_id( 'show_thumbnails' ); ?>" style="display: block; margin-bottom: 5px;">
 				<?php _e( 'Show thumbnails?' ); ?>
 			</label>
 		 	<label class="switch">
 		   <input class="widefat" id="<?php echo $this->get_field_id( 'show_thumbnails' ); ?>" name="<?php echo $this->get_field_name( 'show_thumbnails' ); ?>" type="checkbox" value="1" <?php echo $checked; ?> />
 		 	  <span class="slider round"></span>
 		 	</label>
 		 </p>
 		 <?php

 		//////////////////////////
 		# Select post type
 		//////////////////////////

 		$selected_post_type = (isset($instance['post_type'])) ? $instance['post_type'] : 'post';

 		# Get all public post types
 		$args = array(
 			 'public'   => true,
 			 '_builtin' => false
 		);
 		$post_types = get_post_types( $args, $output = 'objects', $operator = 'or' );

 		# Setup ignored post types
 		$ignore_types = array(
 			'attachment'
 		);

 		# List the post types
 		if(!empty($post_types)){
 			echo '<label for="'.$this->get_field_id( 'post_type' ).'" style="display: block; margin-bottom: 5px;">'.__( 'Select post type:' ).'</label>';
 			echo '<select id="post-type-selector" name="'.$this->get_field_name('post_type').'">';
 			foreach ( $post_types  as $post_type_obj ) {
 				$post_type = $post_type_obj->name;
 				if(!in_array($post_type, $ignore_types)){
 					$post_type_label = $post_type_obj->label;
 					$selected = ($selected_post_type == $post_type) ? 'selected="selected"' : '';
 					echo '<option '.$selected.' value="'.$post_type.'">' . $post_type_label . '</p>';
 				 }
 			}
 			echo '<select>';
 		}

 	 }

 	 /////////////////////
 	 # Update widget
 	 /////////////////////
 	 public function update( $new_instance, $old_instance ) {

 		 # Loop through fields
 		 $instance = array();
 		 $fields_keys = array('title', 'limit', 'post_type', 'show_thumbnails');
 		 foreach($fields_keys as $key){
 		 	$instance[$key] = (!empty($new_instance[$key])) ? strip_tags($new_instance[$key]) : '';
 		 }

 		 # Check for thumbnails
 		 if(!empty($new_instance['show_thumbnails'])){
 			 $instance['show_thumbnails'] = 1;
 		 } else {
 			 $instance['show_thumbnails'] = 0;
 		 }

 		 return $instance;

 	 }

 	 /////////////////////
 	 # Display Widget
 	 /////////////////////
 	 public function widget( $args, $instance ) {

 		 # Setup the arguments
 		 $title = apply_filters('widget_title', $instance['title']);
 		 $limit = $instance['limit'];
  		 $post_type = $instance['post_type'];
  		 $show_thumbnails = $instance['show_thumbnails'];

 		 # Before widget
 		 echo $args['before_widget'];

 		 # Display the title
 		 echo '<h2 class="widget-title">'.$title.'</h2>';

  		 # Get the posts
  		 $query_args = array(
 			 'per_page' => $limit,
 			 'post_type' => $post_type,
 		 );
  		 $posts = wpe_fp_get_posts($query_args);

 		 echo '<ul class="wpe-featured-posts">';

 	 		 # Empty results
 			 if(empty($posts)){

 				 echo __('No posts found.');

 			 # Loop through the posts
 			 } else {
 				 foreach($posts as $post){

           $permalink = get_permalink($post->ID);
 					 $thumbnail = get_the_post_thumbnail( $post->ID, array(80, 80) );

 					 echo '<li id="wpe-featured-posts-'.$post->ID.'">';

   					 echo '<div class="thumbnail">';
   					 echo $thumbnail;
   					 echo '</div>';

   					 echo '<div class="content">';
   					 echo '<h5><a href="'.$permalink.'">'.$post->post_title.'</a></h5>';
   						// echo get_the_excerpt($post->ID);
   					 echo '</div>';

 					 echo '</li>';

 				 }
 			 }

 		 echo '</ul><!-- .wpe-featured-posts -->';

 		 # After widget
 		 echo $args['after_widget'];

 	 }

 }
