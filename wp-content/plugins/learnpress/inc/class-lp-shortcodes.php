<?php
/**
 * @author  ThimPress
 * @package LearnPress/Shortcodes
 * @version 1.0
 */

if ( !defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * LP_Shortcodes class
 */
class LP_Shortcodes {
	/**
	 * Init shortcodes
	 */
	static function init() {
		$shortcodes = array(
			'learn_press_confirm_order'       => __CLASS__ . '::confirm_order',
			'learn_press_profile'             => __CLASS__ . '::profile',
			'learn_press_become_teacher_form' => __CLASS__ . '::become_teacher_form',
			'learn_press_cart'                => __CLASS__ . '::cart',
			'learn_press_checkout'            => __CLASS__ . '::checkout',
		);

		foreach ( $shortcodes as $shortcode => $function ) {
			add_shortcode( apply_filters( "{$shortcode}_shortcode_tag", $shortcode ), $function );
		}

		add_action( 'template_redirect', array( __CLASS__, 'auto_shortcode' ) );
	}

	static function auto_shortcode( $template ) {
		if ( is_page() ) {
			global $post, $wp_query, $wp;
			$page_id = !empty( $wp_query->queried_object_id ) ?
				$wp_query->queried_object_id :
				( !empty( $wp_query->query_vars['page_id'] ) ? $wp_query->query_vars['page_id'] : - 1 );
			if ( $page_id == learn_press_get_page_id( 'checkout' ) ) {
				if ( !preg_match( '/\[learn_press_checkout\s?(.*)\]/', $post->post_content ) ) {
					$post->post_content .= '[learn_press_checkout]';
				}
			} elseif ( $page_id == learn_press_get_page_id( 'profile' ) ) {
				if ( empty( $wp->query_vars['user'] ) ) {
					$current_user = wp_get_current_user();
					if ( !empty( $current_user->user_login ) ) {
						wp_redirect( learn_press_get_endpoint_url( '', $current_user->user_login, learn_press_get_page_link( 'profile' ) ) );
						die();
					} else {
						ob_start();
						wp_login_form(
							array(
								'form_id' => 'learn-press-form-login'
							)
						);
						$content            = ob_get_clean();
						$post->post_content = $content;
					}
				} else {
					$query = array();
					parse_str( $wp->matched_query, $query );
					if ( $query ) {
						$profile_endpoints = (array) LP()->settings->get( 'profile_endpoints' );
						$endpoints         = array_keys( $profile_endpoints );
						foreach ( $query as $k => $v ) {
							if ( ( $k == 'view' ) ) {
								if ( !$v ) {
									$v = reset( $profile_endpoints );
								}
								if ( !in_array( $v, apply_filters( 'learn_press_profile_tab_endpoints', $profile_endpoints ) ) ) {
									learn_press_404_page();
								}
							}
							if ( !empty( $v ) ) {
								$wp->query_vars[$k] = $v;
							}
						}
					}
				}
				if ( !preg_match( '/\[learn_press_profile\s?(.*)\]/', $post->post_content ) ) {
					$post->post_content .= '[learn_press_profile]';
				}
			} elseif ( $page_id == learn_press_get_page_id( 'cart' ) ) {
				if ( !preg_match( '/\[learn_press_cart\s?(.*)\]/', $post->post_content ) ) {
					$post->post_content .= '[learn_press_cart]';
				}
			} elseif ( $page_id == learn_press_get_page_id( 'become_a_teacher' ) ) {
				if ( !preg_match( '/\[learn_press_become_teacher_form\s?(.*)\]/', $post->post_content ) ) {
					$post->post_content .= '[learn_press_become_teacher_form]';
				}
			}
		}
		return $template;
	}

	/**
	 * Checkout form
	 *
	 * @return string
	 */
	static function checkout() {
		global $wp;
		ob_start();
		if ( isset( $wp->query_vars['lp-order-received'] ) ) {

			self::order_received( $wp->query_vars['lp-order-received'] );

		} else {
			// Check cart has contents
			if ( LP()->cart->is_empty() ) {
				learn_press_get_template( 'cart/empty-cart.php', array( 'checkout' => LP()->checkout() ) );
			} else {
				learn_press_get_template( 'checkout/form.php', array( 'checkout' => LP()->checkout() ) );
			}
		}

		return ob_get_clean();
	}

	private static function order_received( $order_id = 0 ) {

		learn_press_print_notices();

		$order = false;

		// Get the order
		$order_id  = absint( $order_id );
		$order_key = !empty( $_GET['key'] ) ? $_GET['key'] : '';

		if ( $order_id > 0 && ( $order = learn_press_get_order( $order_id ) ) && $order->post->post_status != 'trash' ) {
			if ( $order->order_key != $order_key )
				unset( $order );
		} else {
			learn_press_display_message( __( 'Invalid order!', 'learnpress' ), 'error' );
			return;
		}

		LP()->session->order_awaiting_payment = null;

		learn_press_get_template( 'checkout/order-received.php', array( 'order' => $order ) );
	}

	static function cart() {
		ob_start();
		// Check cart has contents
		if ( LP()->cart->is_empty() ) {
			learn_press_get_template( 'cart/empty-cart.php', array( 'cart' => LP()->cart ) );
		} else {
			learn_press_get_template( 'cart/form.php', array( 'cart' => LP()->cart ) );
		}
		return ob_get_clean();
	}

	static function confirm_order( $atts = null ) {
		$atts = shortcode_atts(
			array(
				'order_id' => !empty( $_REQUEST['order_id'] ) ? intval( $_REQUEST['order_id'] ) : 0
			),
			$atts
		);

		$order_id = null;

		extract( $atts );
		ob_start();

		$order = learn_press_get_order( $order_id );

		if ( $order ) {
			learn_press_get_template( 'order/confirm.php', array( 'order' => $order ) );
		}
		return ob_get_clean();
	}

	/**
	 * Display a form let the user can be join as a teacher
	 */
	static function become_teacher_form( $atts ) {
		$user = learn_press_get_current_user();

		$message = '';
		$code    = 0;

		if ( !is_user_logged_in() ) {
			$message = __( "Please login to fill out this form", 'learnpress' );
			$code    = 1;
		} elseif ( in_array( LP()->teacher_role, $user->user->roles ) ) {
			$message = __( "You are a teacher now", 'learnpress' );
			$code    = 2;
		} elseif ( get_transient( 'learn_press_become_teacher_sent_' . $user->id ) == 'yes' ) {
			$message = __( 'Your request has been sent! We will get in touch with you soon!', 'learnpress' );
			$code    = 3;
		} elseif ( learn_press_user_maybe_is_a_teacher() ) {
			$message = __( 'Your role is allowed to create a course', 'learnpress' );
			$code    = 4;
		}

		if ( !apply_filters( 'learn_press_become_a_teacher_display_form', true, $code, $message ) ) {
			return;
		}

		$atts   = shortcode_atts(
			array(
				'method'                     => 'post',
				'action'                     => '',
				'title'                      => __( 'Become a Teacher', 'learnpress' ),
				'description'                => __( 'Fill out your information and send to us to become a teacher', 'learnpress' ),
				'submit_button_text'         => __( 'Submit', 'learnpress' ),
				'submit_button_process_text' => __( 'Processing', 'learnpress' )
			),
			$atts
		);
		$fields = array(
			'bat_name'  => array(
				'title'       => __( 'Name', 'learnpress' ),
				'type'        => 'text',
				'placeholder' => __( 'Your name', 'learnpress' ),
				'def'         => $user->display_name
			),
			'bat_email' => array(
				'title'       => __( 'Email', 'learnpress' ),
				'type'        => 'email',
				'placeholder' => __( 'Your email address', 'learnpress' ),
				'def'         => $user->user_email
			),
			'bat_phone' => array(
				'title'       => __( 'Phone', 'learnpress' ),
				'type'        => 'text',
				'placeholder' => __( 'Your phone number', 'learnpress' )
			)
		);
		$fields = apply_filters( 'learn_press_become_teacher_form_fields', $fields );
		ob_start();
		$args = array_merge(
			array(
				'fields'  => $fields,
				'code'    => $code,
				'message' => $message
			),
			$atts
		);
		learn_press_get_template( 'global/become-teacher-form.php', $args );

		$html = ob_get_clean();

		LP_Assets::enqueue_script( 'become-teacher' );
		return $html;
	}

	static function profile() {
		global $wp_query, $wp;
		if ( isset( $wp_query->query['user'] ) ) {
			$user = get_user_by( 'login', urldecode( $wp_query->query['user'] ) );
		} else {
			$user = get_user_by( 'id', get_current_user_id() );
		}

		$output = '';
		ob_start();
		if ( !$user ) {
			if ( empty( $wp_query->query['user'] ) ) {
				learn_press_get_template( 'profile/private-area.php' );
			} else {
				learn_press_display_message( sprintf( __( 'The user %s is not available!', 'learnpress' ), $wp_query->query['user'] ), 'error' );
			}

		} else {
			$user = LP_User::get_user( $user->ID );
			$tabs = learn_press_user_profile_tabs( $user );
			if ( !empty( $wp->query_vars['view'] ) ) {
				$current = $wp->query_vars['view'];
			} else {
				$tab_keys = array_keys( $tabs );
				$current  = reset( $tab_keys );
			}
			$_REQUEST['tab'] = $current;
			$_POST['tab']    = $current;
			$_GET['tab']     = $current;
			if ( !learn_press_current_user_can_view_profile_section( $current, $user ) ) {
				learn_press_get_template( 'profile/private-area.php' );
			} else {
				if ( !empty( $tabs ) && !empty( $tabs[$current] ) ) :
					learn_press_get_template( 'profile/index.php',
						array(
							'user'    => $user,
							'tabs'    => $tabs,
							'current' => $current
						)
					);
				else:
					if ( $wp->query_vars['view'] == LP()->settings->get( 'profile_endpoints.profile-order-details' ) ) {
						/*
						$current_user = wp_get_current_user();
						if ( $wp_query->query_vars['user'] != $current_user->user_login ) {
							learn_press_get_template( 'profile/private-area.php' );
							return;
						}*/
						$order_id = 0;
						if ( !empty( $wp->query_vars['id'] ) ) {
							$order_id = $wp->query_vars['id'];
						}
						$order = learn_press_get_order( $order_id );
						if ( !$order ) {
							learn_press_display_message( __( 'Invalid order!', 'learnpress' ), 'error' );
						} else {
							learn_press_get_template( 'profile/order-details.php',
								array(
									'user'  => $user,
									'order' => $order
								)
							);
						}
					}
				endif;
			}
		}
		$output .= ob_get_clean();
		return $output;
	}
}

LP_Shortcodes::init();