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
			$this->pending_post();
		} else if ($new == "publish") {
			$this->published_post();
		}
	}
	
	/**
	 * A post has been set as 'pending'. This means that the post has been submitted for approval, or
	 * an already pending post has been reviewed and edited.
	 *
	 */
	public function pending_post() {
		$postID 		= $_POST['post_ID'];
		$postAuthorID 	= $_POST['post_author'];
		$author 		= get_userdata($postAuthorID);
		$postTitle 		= $_POST['post_title'];	
		$emailSubject 	= "[" . get_bloginfo('name', 'display') . "] Pending Post Notification";
	
		if($postAuthorID == get_current_user_id()) {
			// A contributor has submitted a post for approval.
			$editors = new WP_User_Query(array('role' => 'Editor'));
			if(!empty($editors->results)) {
				foreach($editors->results as $editor) {
					$name = $editor->display_name;
					$email = $editor->user_email;
					$message = "A pending post \"" . $postTitle . "\" has been submitted for review.\n\n";
					$message .= "It was submitted by " . $author->display_name . ".\n\n";
					$message .= "Review it here: " . get_admin_url() . "post.php?post=" . $postID . "&action=edit";
					wp_mail($email, $emailSubject, $message);
				}
			}
		} else {
			// An editor has updated the pending post. Update the author of the edit.
			$message = "A pending post of yours, \"" . $postTitle . "\", has been reviewed and updated.\n\n";
			$message .= "View the updated post at " . get_admin_url() . "post.php?post=" . $postID . "&action=edit";
			wp_mail($author->user_email, $emailSubject, $message);
		}
	}

	/**
	 * A post has been published.
	 *
	 */
	public function published_post() {
		$postID 		= $_POST['post_ID'];
		$postAuthorID 	= $_POST['post_author'];
		$author 		= get_userdata($postAuthorID);
		$postTitle 		= $_POST['post_title'];
	
		if($postAuthorID != get_current_user_id()) {
			// Author's post has been published by someone else. Update the author of the published event.
			$subject = "[" . get_bloginfo('name', 'display') . "] Published Post Notification";
			$message = "A pending post of yours, \"" . $postTitle . "\", has been reviewed and published.\n\n";
			$message .= "View the published post at " . get_permalink($postID);
			wp_mail($author->user_email, $subject, $message);
		}
	}
}

// Initialise the plugin.
new EEN();