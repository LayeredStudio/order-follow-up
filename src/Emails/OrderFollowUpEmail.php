<?php
namespace Layered\OrderFollowUp\Emails;

/**
 * Post order email
 */
class OrderFollowUpEmail extends \WC_Email {

	/**
	 * Set email defaults
	 */
	public function __construct() {
		$this->id				=	'order_follow_up';
		$this->customer_email	=	true;
		$this->title			=	__('Order Follow Up', 'layered');
		$this->description		=	__('Send a delayed email after a completed order', 'layered');
		$this->subject			=	__('Review your recent purchase with {site_title}', 'layered');
		$this->heading			=	__('Hi {customer_first_name}, we\'d love your feedback!', 'layered');
		$this->template_base	=	ORDER_FOLLOW_UP_TEMPLATES;
		$this->template_html	=	'emails/order-follow-up.php';
		$this->template_plain	=	'emails/plain/order-follow-up.php';
		$this->placeholders		=	array(
			'{site_title}'			=>	$this->get_blogname(),
			'{order_date}'			=>	'',
			'{order_number}'		=>	'',
			'{customer_first_name}'	=>	'',
			'{customer_last_name}'	=>	'',
			'{customer_email}'		=>	'',
			'{customer_order_count}'=>	'',
			'{customer_total_spent}'=>	''
		);

		// Trigger email after set number of days
		add_action('woocommerce_order_follow_up', array($this, 'trigger'), 500);

		// Call parent constructor to load any other defaults not explicity defined here
		parent::__construct();
	}

	/**
	 * Prepares email content and triggers the email
	 */
	public function trigger(int $orderId, $order = false) {
		if (!is_a($order, 'WC_Order')) {
			$order = wc_get_order($orderId);
		}

		if (is_a($order, 'WC_Order') && !get_post_meta($orderId, '_order_follow_up_email_sent', true)) {
			$this->order = $order;
			$this->customer = new \WC_Customer($order->get_customer_id());
			$this->recipient = $this->order->get_billing_email();
			$this->placeholders['{order_date}']   = wc_format_datetime( $this->order->get_date_created() );
			$this->placeholders['{order_number}'] = $this->order->get_order_number();
			$this->placeholders['{customer_first_name}'] = $this->customer->get_first_name();
			$this->placeholders['{customer_last_name}'] = $this->customer->get_last_name();
			$this->placeholders['{customer_email}'] = $this->customer->get_email();
			$this->placeholders['{customer_order_count}'] = $this->customer->get_order_count();
			$this->placeholders['{customer_total_spent}'] = $this->customer->get_total_spent();

			if ($this->is_enabled() && $this->get_recipient()) {
				$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
				$this->order->add_order_note(sprintf(__('%s email sent to the customer.', 'layered'), $this->title));
				update_post_meta($orderId, '_order_follow_up_email_sent', 1);
			}
		}
	}

	/**
	 * get_content_html function.
	 *
	 * @return string
	 */
	public function get_content_html() {
		return wc_get_template_html( $this->template_html, array(
			'order'					=> $this->order,
			'email_heading'			=> $this->get_heading(),
			'sent_to_admin'			=> false,
			'plain_text'			=> false,
			'email'					=> $this,
			'paragraph'				=> $this->format_string($this->get_option('paragraph'))
		), '', $this->template_base );
	}

	/**
	 * get_content_plain function.
	 *
	 * @return string
	 */
	public function get_content_plain() {
		return wc_get_template_html( $this->template_plain, array(
			'order'					=> $this->order,
			'email_heading'			=> $this->get_heading(),
			'sent_to_admin'			=> false,
			'plain_text'			=> true,
			'email'					=> $this,
			'paragraph'				=> $this->format_string($this->get_option('paragraph'))
		), '', $this->template_base );
	}

	/**
	 * Initialize settings form fields
	 */
	public function init_form_fields() {

		$this->form_fields = array(
			'enabled'		=> array(
				'title'			=>	__( 'Enable/Disable', 'layered' ),
				'type'			=>	'checkbox',
				'label'			=>	'Enable this email notification',
				'default'		=>	'yes'
			),
			'delay'			=> array(
				'title'			=>	__('Days delay', 'layered'),
				'type'			=>	'number',
				'desc_tip'		=>	true,
				'description'	=>	__( 'After how many days to send the follow up email?', 'layered' ),
				'placeholder'	=>	7,
				'default'		=>	3
			),
			'subject'		=> array(
				'title'			=>	__( 'Subject', 'layered' ),
				'type'			=>	'text',
				'desc_tip'		=>	true,
				'description'	=>	sprintf( __( 'Available placeholders: %s', 'layered' ), '<code>' . implode( '</code>, <code>', array_keys( $this->placeholders ) ) . '</code>' ),
				'placeholder'	=>	$this->get_default_subject(),
				'default'		=>	''
			),
			'heading'		=> array(
				'title'			=>	__( 'Email Heading', 'layered' ),
				'type'			=>	'text',
				'desc_tip'		=>	true,
				'description'	=>	sprintf( __( 'Available placeholders: %s', 'layered' ), '<code>' . implode( '</code>, <code>', array_keys( $this->placeholders ) ) . '</code>' ),
				'placeholder'	=>	$this->get_default_heading(),
				'default'		=>	''
			),
			'paragraph'		=> array(
				'title'			=>	__( 'Email Intro', 'layered' ),
				'type'			=>	'textarea',
				'desc_tip'		=>	true,
				'description'	=>	sprintf( __( 'Content before order items. Available placeholders: %s', 'layered' ), '<code>' . implode( '</code>, <code>', array_keys( $this->placeholders ) ) . '</code>' ),
				'default'		=>	sprintf(__('Thanks for buying from %s. Please review your purchase, it only takes a minute and really helps! Products in your order:', 'layered' ), '{site_title}')
			),
			'email_type'	=> array(
				'title'			=>	__( 'Email type', 'layered' ),
				'type'			=>	'select',
				'desc_tip'		=>	true,
				'description'	=>	__( 'Choose which format of email to send.', 'layered' ),
				'default'		=>	'html',
				'class'			=>	'email_type wc-enhanced-select',
				'options'		=>	$this->get_email_type_options()
			)
		);
	}

}
