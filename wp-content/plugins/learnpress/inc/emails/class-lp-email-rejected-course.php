<?php

/**
 * Class LP_Email_Rejected_Course
 *
 * @author  ThimPress
 * @package LearnPress/Classes
 * @version 1.0
 */

defined( 'ABSPATH' ) || exit();

class LP_Email_Rejected_Course extends LP_Email {
	function __construct() {
		$this->id    = 'rejected_course';
		$this->title = __( 'Rejected course', 'learnpress' );

		$this->template_html  = 'emails/rejected-course.php';
		$this->template_plain = 'emails/plain/rejected-course.php';

		$this->default_subject = __( '[{site_title}] Your course {course_name} has rejected', 'learnpress' );
		$this->default_heading = __( 'Rejected course', 'learnpress' );


		parent::__construct();
	}

	function admin_options( $obj ) {
		$view = learn_press_get_admin_view( 'settings/emails/rejected-course.php' );
		include_once $view;
	}

	function trigger( $course_id ) {
		if ( !$this->enable ) {
			return;
		}

		$course                    = learn_press_get_course( $course_id );
		$this->find['site_title']  = '{site_title}';
		$this->find['course_name'] = '{course_name}';

		$this->replace['site_title']  = $this->get_blogname();
		$this->replace['course_name'] = get_the_title( $course_id );

		$this->object = array(
			'course' => $course
		);

		$user_course     = learn_press_get_course_user( $course_id );
		$this->recipient = $user_course->user_email;

		if ( !$this->get_recipient() ) {
			return;
		}
		$return = $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
		return $return;
	}

	function get_content_html() {
		ob_start();
		learn_press_get_template( $this->template_html, $this->get_template_data( 'html' ) );
		return ob_get_clean();
	}

	function get_content_plain() {
		ob_start();
		learn_press_get_template( $this->template_plain, $this->get_template_data( 'plain' ) );
		return ob_get_clean();
	}

	function get_template_data( $format = 'plain' ) {
		return array(
			'email_heading' => $this->get_heading(),
			'footer_text'   => $this->get_footer_text(),
			'site_title'    => $this->get_blogname(),
			'course'        => $this->object['course'],
			'login_url'     => learn_press_get_login_url(),
			'plain_text'    => $format == 'plain'
		);
	}
}

return new LP_Email_Rejected_Course();