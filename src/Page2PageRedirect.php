<?php

namespace WPS\Core;

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! class_exists( '\WPS\Core\Page2PageRedirect' ) ) {
	/**
	 * Class Page2PageRedirect
	 *
	 * @package WPS\Core
	 */
	class Page2PageRedirect {

		/**
		 * Private Page ID.
		 *
		 * @var int
		 */
		public $page_for_logged_in_users;

		/**
		 * Public Page ID.
		 *
		 * @var int
		 */
		public $page_for_logged_out_users;

		/**
		 * Template loader.
		 *
		 * @var \WPS\Templates\Template_Loader
		 */
		private $template_loader;

		/**
		 * PrivatePage constructor.
		 *
		 * @param int $private_page_id Private Page ID.
		 * @param int $public_page_id  Public Page ID.
		 */
		public function __construct( $page_for_logged_in_users, $page_for_logged_out_users ) {
			$this->page_for_logged_in_users  = $page_for_logged_in_users;
			$this->page_for_logged_out_users = $page_for_logged_out_users;

			add_action( 'template_redirect', array( $this, 'template_redirect' ) );
		}

		/**
		 * Whether the current query has this's query var.
		 *
		 * @return bool
		 */
		private function is_page() {
			if ( ! is_singular() || is_admin() ) {
				return false;
			}

			return ( $this->is_page_for_logged_in_users() || $this->is_page_for_logged_out_users() );
		}

		/**
		 * Is the current page for logged in users?
		 *
		 * @return bool
		 */
		private function is_page_for_logged_in_users() {
			$queried_object = get_queried_object();

			return ( $queried_object->ID === $this->page_for_logged_in_users );
		}

		/**
		 * Is the current page for logged out users?
		 *
		 * @return bool
		 */
		private function is_page_for_logged_out_users() {
			$queried_object = get_queried_object();

			return ( $queried_object->ID === $this->page_for_logged_out_users );
		}

		/**
		 * Redirects to the Post by ID.
		 *
		 * @param int $id Post ID.
		 */
		private function redirect( $id ) {
			$permalink = get_permalink( $id );

			wp_safe_redirect( $permalink );
			exit;
		}

		/**
		 * Conditionally includes the template.
		 */
		public function template_redirect() {
			if ( ! $this->is_page() || is_admin() ) {
				return;
			}

			// If user is logged in & on private page, redirect to public page
			if ( is_user_logged_in() && $this->is_page_for_logged_out_users() ) {
				$this->redirect( $this->page_for_logged_in_users );
			}

			// If user is NOT logged in & on public page, redirect to private page
			if ( ! is_user_logged_in() && $this->is_page_for_logged_in_users() ) {
				$this->redirect( $this->page_for_logged_out_users );
			}
		}
	}
}
