<?php
namespace Layered\OrderFollowUp;

class Emails {

	protected static $leeway = 2;

	public function __construct() {

		// Add emails and triggers
		add_filter('woocommerce_email_classes', [$this, 'addEmails']);
		add_filter('woocommerce_email_actions', [$this, 'addEmailsActions']);

		// Order follow up action
		add_filter('check_order_follow_up', [$this, 'checkCompletedOrders']);
	}

	public static function start() {
		return new static();
	}

	public static function addEmails($email_classes) {
		$email_classes['Order_Follow_Up'] = new \Layered\OrderFollowUp\Emails\OrderFollowUpEmail;
		$email_classes['Review_Approved'] = new \Layered\OrderFollowUp\Emails\ReviewApprovedEmail;

		return $email_classes;
	}

	public static function addEmailsActions($emailActions) {
		$emailActions[] = 'wp_insert_comment';
		$emailActions[] = 'transition_comment_status';
		$emailActions[] = 'woocommerce_order_follow_up';

		return $emailActions;
	}

	public static function checkCompletedOrders() {
		$mails = WC()->mailer()->get_emails();
		$delay = $mails['Order_Follow_Up']->get_option('delay');

		$orders = wc_get_orders([
			'status'			=>	'completed',
			'date_completed'	=>	date('Y-m-d', time() - 3600 * 24 * ($delay + self::$leeway)) . '...' . date('Y-m-d', time() - 3600 * 24 * $delay),
			'return'			=>	'ids',
			'limit'				=>	3
		]);

		foreach ($orders as $order) {
			do_action('woocommerce_order_follow_up', $order);
		}
	}

	public static function onActivation() {
		if (!wp_next_scheduled('check_order_follow_up')) {
			wp_schedule_event(time(), 'daily', 'check_order_follow_up');
		}
	}

	public static function onDeactivation() {
		wp_clear_scheduled_hook('check_order_follow_up');
	}

}
