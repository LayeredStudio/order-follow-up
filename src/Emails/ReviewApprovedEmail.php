<?php
namespace Layered\OrderFollowUp\Emails;

/**
 * Email sent when a product review is approved
 */
class ReviewApprovedEmail extends \WC_Email {

	/**
	 * Set email defaults
	 */
	public function __construct() {
		$this->id				= 'review_approved';
		$this->customer_email	=	true;
		$this->title			=	__('Review approved', 'layered');
		$this->description		=	__('Send a email after a review is approved', 'layered');
		$this->subject			=	__('Thanks for your review on {site_title}', 'layered');
		$this->heading			=	__('Hi {review_author}, thanks for your feedback!', 'layered');
		$this->template_base	=	ORDER_FOLLOW_UP_TEMPLATES;
		$this->template_html	=	'emails/review-approved.php';
		$this->template_plain	=	'emails/plain/review-approved.php';
		$this->placeholders		=	[
			'{site_title}'				=>	$this->get_blogname(),
			'{review_author}'			=>	'',
			'{product_title}'			=>	'',
			'{product_with_link}'		=>	'',
			'{review_author_email}'		=>	'',
			'{review_author_url}'		=>	'',
			'{review_date}'				=>	'',
			'{review_content}'			=>	'',
			'{product_title}'			=>	'',
			'{product_sku}'				=>	'',
			'{product_price}'			=>	'',
			'{product_url}'				=>	'',
			'{product_with_link}'		=>	'',
			'{product_rating_counts}'	=>	'',
			'{product_average_rating}'	=>	'',
			'{product_review_count}'	=>	''	
		];

		// Trigger email when a product review is approved
		add_action('transition_comment_status', [$this, 'checkIfApprovedReview'], 500, 3);
		add_action('wp_insert_comment', [$this, 'trigger'], 500, 2);

		// Call parent constructor to load any other defaults not explicity defined here
		parent::__construct();
	}

	/**
	 * Check if the comment is approved and for a product
	 */
	public function checkIfApprovedReview(string $newStatus, string $oldStatus, \WP_Comment $comment) {
		if ($newStatus === 'approved') {
			$this->trigger($comment->comment_ID, $comment);
		}
	}

	/**
	 * Prepares email content and triggers the email
	 */
	public function trigger(int $reviewId, \WP_Comment $review) {

		if ($review->comment_approved && !get_comment_meta($reviewId, '_review_approved_email_sent', true)) {
			$product = wc_get_product($review->comment_post_ID);

			if (is_a($product, 'WC_Product')) {
				$this->review = $review;
				$this->product = $product;
				$this->recipient = $review->comment_author_email;

				if ($this->is_enabled() && $this->get_recipient()) {
					$this->placeholders['{review_author}'] = $review->comment_author;
					$this->placeholders['{review_author_email}'] = $review->comment_author_email;
					$this->placeholders['{review_author_url}'] = $review->comment_author_url;
					$this->placeholders['{review_date}'] = $review->comment_date;
					$this->placeholders['{review_content}'] = $review->comment_content;
					$this->placeholders['{product_title}'] = $product->get_title();
					$this->placeholders['{product_sku}'] = $product->get_sku();
					$this->placeholders['{product_price}'] = $product->get_price();
					$this->placeholders['{product_url}'] = $product->get_permalink();
					$this->placeholders['{product_with_link}'] = sprintf('<a href="%s">%s</a>', esc_url($product->get_permalink()), $product->get_title());
					$this->placeholders['{product_rating_counts}'] = $product->get_rating_counts();
					$this->placeholders['{product_average_rating}'] = $product->get_average_rating();
					$this->placeholders['{product_review_count}'] = $product->get_review_count();

					$this->send($this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments());
					update_comment_meta($reviewId, '_review_approved_email_sent', 1);
				}
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
			'review'				=> $this->review,
			'product'				=> $this->product,
			'email_heading'			=> $this->get_heading(),
			'paragraph'				=> $this->format_string($this->get_option('paragraph')),
			'sent_to_admin'			=> false,
			'plain_text'			=> false,
			'email'					=> $this
		), '', $this->template_base );
	}

	/**
	 * get_content_plain function.
	 *
	 * @return string
	 */
	public function get_content_plain() {
		return wc_get_template_html( $this->template_plain, array(
			'review'				=> $this->review,
			'product'				=> $this->product,
			'email_heading'			=> $this->get_heading(),
			'paragraph'				=> $this->format_string($this->get_option('paragraph')),
			'sent_to_admin'			=> false,
			'plain_text'			=> true,
			'email'					=> $this
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
				'description'	=>	sprintf( __( 'Content. Available placeholders: %s', 'layered' ), '<code>' . implode( '</code>, <code>', array_keys( $this->placeholders ) ) . '</code>' ),
				'default'		=>	sprintf(__('Thanks for your review on %s.', 'layered' ), '{product_with_link}')
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
