<?php
/*
Plugin Name: Editorial Email Notifications
Plugin URI: http://github.com/edwardcasbon/wp-een/
Description: Email editorial notifications when contributors have submitted, and editors have published posts.
Author: Edward Casbon
Version: 1.0
Author URI: http://www.edwardcasbon.co.uk
*/
class EEN {
	
	public function __construct() {
		add_action('transition_post_status', array($this, 'state_change'));
	}
	
	public function state_change($new = 'new', $old = 'new', $post = false) {
		if($new == "pending") {
			$this->notify_editors($_POST['post_ID']);
		} else if ($new == "publish") {
			$this->notify_contributor($_POST['post_ID']);
		}
	}
	
	public function notify_editors() {
		$postID 		= $_POST['post_ID'];
		$postAuthorID 	= $_POST['post_author'];
		$author 		= get_userdata($postAuthorID);
		$postTitle 		= $_POST['post_title'];
	
		if($postAuthorID == get_current_user_id()) {
			$editors = new WP_User_Query(array('role' => 'Editor'));
			if(!empty($editors->results)) {
				foreach($editors->results as $editor) {
					$name = $editor->display_name;
					$email = $editor->user_email;
					$subject = "[" . get_bloginfo('name', 'display') . "] Pending Post Notification";
					$message = "A pending post \"" . $postTitle . "\" has been submitted for review.\n\n";
					$message .= "It was submitted by " . $author->display_name . ".\n\n";
					$url = get_admin_url() . "post.php?post=" . $postID . "&action=edit";
					$message .= "Review it here: " . $url;
					wp_mail($email, $subject, $message);
				}
			}
		}
	}

	public function notify_contributor() {
		$postID 		= $_POST['post_ID'];
		$postAuthorID 	= $_POST['post_author'];
		$author 		= get_userdata($postAuthorID);
		$postTitle 		= $_POST['post_title'];
	
		if($postAuthorID != get_current_user_id()) {
			$subject = "[" . get_bloginfo('name', 'display') . "] Published Post Notification";
			$message = "A pending post of yours, \"" . $postTitle . "\", has been reviewed and published.\n\n";
			$message .= "View the published post at " . get_permalink($postID);
			wp_mail($author->user_email, $subject, $message);
		}
	}
}

// Initialise the plugin.
new EEN();