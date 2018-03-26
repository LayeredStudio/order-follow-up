<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action( 'woocommerce_email_header', $email_heading, $email ); ?>


<?php echo apply_filters('the_content', $paragraph) ?>


<table width="100%">
	<?php foreach ($order->get_items() as $item) : ?>
		<?php $product = $item->get_product(); ?>
		<tr>
			<td style="padding: 0 !important;width: 100px">
				<?php echo $product->get_image([100, 100]) ?>
			</td>
			<td>
				<?php printf('<a href="%s">%s</a>', esc_url($product->get_permalink() . '#comments'), $item->get_name()) ?>
			</td>
			<td style="padding: 0 !important; width: 180px">
				<?php foreach (range(1, 5) as $rating) : ?>
					<a href="<?php echo $product->get_permalink() ?>?rating=<?php echo $rating ?>#comments" style="text-decoration: none">
						<img src="<?php echo plugins_url('order-follow-up/assets/icon-star-bw.png') ?>" width="30" height="28" alt="Star" border=0>
					</a>
				<?php endforeach ?>
			</td>
		</tr>
	<?php endforeach ?>
</table>

<br>
<p><small>Order #<?php echo $order->get_order_number() ?> on <?php echo wc_format_datetime( $order->get_date_created() ) ?></small></p>


<?php

/**
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );
