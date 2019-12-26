<?php
/**
 * Plugin Name: WP Down Slack Alert - Worker
 * Description: This plugin’s purpose is to manage Slack connexion once WP Down Slack Alert plugin is activated.
 * Version: 0.3
 * License: GPLv2
 * License URI: http://www.gnu.org/licenses/gpl-2.0.html
 * Text-domain: wp-down-slack-alert
 */

function wpdsa_admin_screen_enqueues( $hook ) {
	if ( 'tools_page_settings-slack-notification' === $hook ) {
		wp_register_script( 'wp-down-slack-alert-scripts', WP_PLUGIN_URL . '/wp-down-slack-alert/js/wp-down-slack-alert.js', array( 'jquery', 'jquery-ui-core', 'jquery-ui-dialog' ) );
		wp_register_style( 'wp-down-slack-alert-styles', WP_PLUGIN_URL . '/wp-down-slack-alert/css/wp-down-slack-alert.css', array( 'wp-jquery-ui-dialog' ) );
		wp_enqueue_script( 'jquery-ui-dialog' );
		wp_enqueue_script( 'wp-down-slack-alert-scripts' );
		wp_enqueue_style( 'wp-down-slack-alert-styles' );
	}
}
add_action( 'admin_enqueue_scripts', 'wpdsa_admin_screen_enqueues' );

add_filter(
	'recovery_mode_email_rate_limit',
	function ( $interval ) {
		$recurrence_mail = esc_html( get_option( 'settings_slack_notification_recurrence_mail' ) );
		if ( '' !== $recurrence_mail ) {
			if ( 'anytime' === $recurrence_mail ) {
				$recurrence_mail = 0;
			} else {
				$recurrence_mail *= 3600;
			}
			$interval = $recurrence_mail;
		}
		return $interval;
	}
);
add_action( 'admin_menu', 'wpdsa_add_sub_menu' );

function wpdsa_add_sub_menu() {
	if ( defined( 'WPDSA_SETTINGS' ) && false === WPDSA_SETTINGS ) {
		// Don’t create the settings screen if the WPDSA_SETTINGS constant is defined to false.
	} else {
		add_submenu_page(
			'tools.php',
			esc_html__( 'WP Down Slack Alert', 'wp-down-slack-alert' ),
			esc_html__( 'Slack Alert', 'wp-down-slack-alert' ),
			'manage_options',
			'settings-slack-notification',
			'wpdsa_render_settings_callback'
		);
	}
}

add_filter(
	'recovery_mode_email',
	function ( $email ) {
		$option_name = 'settings_slack_notification_disable_email';
		if ( false === esc_html( get_option( $option_name ) ) ) {
			add_option( $option_name, true );
		}

		$disable_email = esc_html( get_option( 'settings_slack_notification_disable_email' ) );
		if ( isset( $disable_email ) && 1 === $disable_email ) {
			$email['to'] = '';
		}

		return $email;
	}
);

function wpdsa_admin_notice__success() {
	if ( isset( $_GET['page'], $_POST['submit_settings_slack_notification'] ) && 'settings-slack-notification' === $_GET['page'] ) : ?>
		<div class="notice notice-success is-dismissible">
			<p><?php esc_html_e( 'Settings saved.', 'wp-down-slack-alert' ); ?></p>
		</div>
		<?php
	endif;
}
add_action( 'admin_notices', 'wpdsa_admin_notice__success' );

function wpdsa_render_settings_callback() {
	$option_name = 'settings_slack_notification_disable_email';
	if ( esc_html( get_option( $option_name ) === false ) ) {
		add_option( $option_name, true );
	}

	if ( isset( $_POST, $_POST['submit_settings_slack_notification'] ) && ! empty( $_POST ) && wp_verify_nonce( $_POST['nonce'], 'wpdsa_nonce_slack' ) ) {

		// Slack channel
		if ( isset( $_POST['notification_channel'] ) ) {
			update_option( 'settings_slack_notification_channel', sanitize_text_field( $_POST['notification_channel'] ) );
		}
		if ( defined( 'WPDSA_NOTIFICATION_CHANNEL' ) && ! empty( WPDSA_NOTIFICATION_CHANNEL ) ) {
			update_option( 'settings_slack_notification_channel', sanitize_text_field( WPDSA_NOTIFICATION_CHANNEL ) );
		}

		// Bot name
		if ( isset( $_POST['notification_bot_name'] ) ) {
			$bot_name = str_replace( ' ', '_', $_POST['notification_bot_name'] );
			update_option( 'settings_slack_notification_bot_name', sanitize_text_field( $bot_name ) );
		}
		if ( defined( 'WPDSA_NOTIFICATION_BOTNAME' ) && ! empty( WPDSA_NOTIFICATION_BOTNAME ) ) {
			$bot_name = str_replace( ' ', '_', WPDSA_NOTIFICATION_BOTNAME );
			update_option( 'settings_slack_notification_bot_name', sanitize_text_field( $bot_name ) );
		}

		// Notification e-mail
		if ( isset( $_POST['notification_bot_disable_email'] ) ) {
			update_option( 'settings_slack_notification_disable_email', 1 );
		} else {
			update_option( 'settings_slack_notification_disable_email', 0 );
		}
		if ( defined( 'WPDSA_NOTIFICATION_DISABLE_EMAIL' ) && ! empty( WPDSA_NOTIFICATION_DISABLE_EMAIL ) ) {
			$email_checked = 0;
			if ( 1 === intval( WPDSA_NOTIFICATION_DISABLE_EMAIL ) || true === WPDSA_NOTIFICATION_DISABLE_EMAIL || 'true' === WPDSA_NOTIFICATION_DISABLE_EMAIL ) {
				$email_checked = 1;
			}
			update_option( 'settings_slack_notification_disable_email', $email_checked );
		}
		if ( isset( $_POST['mail_recurrence'] ) ) {
			update_option( 'settings_slack_notification_recurrence_mail', sanitize_text_field( $_POST['mail_recurrence'] ) );
		}

		// Notification recurrence
		if ( isset( $_POST['notification_recurrence'] ) ) {
			update_option( 'settings_slack_notification_recurrence', sanitize_text_field( $_POST['notification_recurrence'] ) );
		}
		if ( defined( 'WPDSA_NOTIFICATION_RECURRENCE' ) && ! empty( WPDSA_NOTIFICATION_RECURRENCE ) ) {
			update_option( 'settings_slack_notification_recurrence', sanitize_text_field( WPDSA_NOTIFICATION_RECURRENCE ) );
		}

		// Bot avatar image
		if ( isset( $_POST['image_attachment_id'] ) ) {
			update_option( 'settings_slack_notification_attachment_id', absint( $_POST['image_attachment_id'] ) );
		} // PHP constant below

		// Slack API Token
		if ( isset( $_POST['notification_token'] ) ) {
			update_option( 'settings_slack_notification_token', sanitize_text_field( $_POST['notification_token'] ) );
		}
		if ( defined( 'WPDSA_NOTIFICATION_TOKEN' ) && ! empty( WPDSA_NOTIFICATION_TOKEN ) ) {
			update_option( 'settings_slack_notification_token', sanitize_text_field( WPDSA_NOTIFICATION_TOKEN ) );
		}

		// Message title
		if ( isset( $_POST['notification_message_title'] ) ) {
			update_option( 'settings_slack_notification_message_title', sanitize_text_field( $_POST['notification_message_title'] ) );
		}
		if ( defined( 'WPDSA_NOTIFICATION_MESSAGE_TITLE' ) && ! empty( WPDSA_NOTIFICATION_MESSAGE_TITLE ) ) {
			update_option( 'settings_slack_notification_message_title', sanitize_text_field( WPDSA_NOTIFICATION_MESSAGE_TITLE ) );
		}

		// Message footer text
		if ( isset( $_POST['notification_message_footer'] ) ) {
			update_option( 'settings_slack_notification_message_footer', sanitize_text_field( $_POST['notification_message_footer'] ) );
		}
		if ( defined( 'WPDSA_NOTIFICATION_MESSAGE_FOOTER' ) && ! empty( WPDSA_NOTIFICATION_MESSAGE_FOOTER ) ) {
			update_option( 'settings_slack_notification_message_footer', sanitize_text_field( WPDSA_NOTIFICATION_MESSAGE_FOOTER ) );
		}
	}

	$token = sanitize_text_field( get_option( 'settings_slack_notification_token' ) );

	wp_enqueue_media();

	$recurrence = ( get_option( 'settings_slack_notification_recurrence' ) ) ? get_option( 'settings_slack_notification_recurrence' ) : 1;
	$notification_recurrence_disabled = '';
	if ( defined( 'WPDSA_NOTIFICATION_RECURRENCE' ) && ! empty( WPDSA_NOTIFICATION_RECURRENCE ) ) {
		$recurrence = sanitize_text_field( WPDSA_NOTIFICATION_RECURRENCE );
		$notification_recurrence_disabled = ' disabled';
	}

	$recurrence_mail = ( get_option( 'settings_slack_notification_recurrence_mail' ) ) ? get_option( 'settings_slack_notification_recurrence_mail' ) : 24;

	$id_image = ( get_option( 'settings_slack_notification_attachment_id' ) ) ? absint( get_option( 'settings_slack_notification_attachment_id' ) ) : '';
	if ( defined( 'WPDSA_NOTIFICATION_MESSAGE_IMAGE' ) && ! empty( WPDSA_NOTIFICATION_MESSAGE_IMAGE ) ) {
		$id_image = sanitize_text_field( WPDSA_NOTIFICATION_MESSAGE_IMAGE );
		update_option( 'settings_slack_notification_attachment_id', $id_image );
	}
	?>
	<div class="wrap">
		<h1><?php esc_html_e( 'WP Down Slack Alert', 'wp-down-slack-alert' ); ?></h1>
		<?php
		$notice_classes         = 'notice-warning';
		$dashicon_classes       = 'dashicons dashicons-dismiss';
		$color_style            = '#ffb900';
		$current_status_message = esc_html__( 'Status: Slack connexion not activated, no token provided.', 'wp-down-slack-alert' );
		if ( ! empty( $token ) ) {
			$connexion_status = wpdsa_check_slack_connexion( $token );
			if ( true === $connexion_status['connected'] ) {
				$notice_classes         = 'notice-success';
				$dashicon_classes       = 'dashicons dashicons-yes-alt';
				$color_style            = '#46b450';
				$current_status_message = sprintf(
					/* translators: %s Slack name */
					esc_html__( 'Status: connexion to %s Slack team is working perfectly!', 'wp-down-slack-alert' ),
					'<code>' . $connexion_status['team'] . '</code>'
				);
			} else {
				$notice_classes         = 'notice-error';
				$dashicon_classes       = 'dashicons dashicons-dismiss';
				$color_style            = '#dc3232';
				$current_status_message = sprintf(
					/* translators: %s Error message returned by the Slack API */
					esc_html__( 'Status: Slack connexion not activated, token not working. Error returned: %s.', 'wp-down-slack-alert' ),
					'<code>' . $connexion_status['error'] . '</code>'
				);
			}
		}

		?>
		<div class="slack-current-status notice <?php echo esc_attr( $notice_classes ); ?>" style="display:inline-block;">
			<p>
				<span class="<?php echo esc_attr( $dashicon_classes ); ?>" style="color:<?php echo esc_attr( $color_style ); ?>;"></span>
				<?php echo $current_status_message; ?>
			</p>
		</div>

		<form method="post">
			<h2><?php esc_html_e( 'Slack API connexion', 'wp-down-slack-alert' ); ?></h2>
			<input type="hidden" name="nonce" value="<?php echo wp_create_nonce( 'wpdsa_nonce_slack' ); ?>">
			<table class="form-table">
				<tr>
					<th>
						<label for="notification_token"><?php esc_html_e( 'Slack App token', 'wp-down-slack-alert' ); ?></label>
					</th>
					<td>
					<?php if ( defined( 'WPDSA_NOTIFICATION_TOKEN' ) && ! empty( WPDSA_NOTIFICATION_TOKEN ) ) : ?>
						<input id="notification_token" name='notification_token' type="text" value="<?php echo esc_html( WPDSA_NOTIFICATION_TOKEN ); ?>" disabled />
						<p class="description">
							<?php
							echo sprintf(
								/* translators: %s: Name of the constant */
								__( 'This setting is disabled as it’s already defined with the constant %s', 'wp-down-slack-alert' ),
								'<code>WPDSA_NOTIFICATION_TOKEN</code>'
							);
							?>
						</p>
					<?php else : ?>
						<input id="notification_token" name='notification_token' type="text" value="<?php echo esc_html( $token ); ?>"/>
						<?php if ( 'notice-success' !== $notice_classes ) : ?>
						<p>
							<?php esc_html_e( 'To get your Slack App Token, just follow this small tutorial. It takes less than 5 minutes!', 'wp-down-slack-alert' ); ?>
						</p>
						<?php endif; ?>
						<p>
							<button type="button" class="button-primary" id="toggle-slack-token-help">
								<?php esc_html_e( 'Get your Slack App Token', 'wp-down-slack-alert' ); ?>
							</button>
						</p>
						
						<div id="slack_token_help_step_1" class="slack_token_help_steps" data-title="<?php esc_attr_e( 'Connect your Slack team: STEP 1', 'wp-down-slack-alert' ); ?>">
							<p>
								<?php
								echo sprintf(
									/* translators: 1: Opening link tag. 2: Closing link tag. */
									__( 'Go to %1$sthis page%2$s and provide a name for your App, choose a Slack workspace and click on "Create App" button.', 'wp-down-slack-alert' ),
									'<a href="https://api.slack.com/apps?new_app=1" target="_blank">',
									'</a>'
								);
								?>
							</p>
							<p class="wpdsa-modal-buttons">
								<button data-target="#slack_token_help_step_2" type="button" class="button button-primary wpdsa-pagination wpdsa-pagination-next alignright"><?php esc_html_e( 'Next step' ); ?></button>
							</p>
							<img class="wpdsa-modal-image" src="<?php echo WP_PLUGIN_URL; ?>/wp-down-slack-alert/images/config-1.png" alt="" />
						</div>
						
						<div id="slack_token_help_step_2" class="slack_token_help_steps" data-title="<?php esc_attr_e( 'Connect your Slack team: STEP 2', 'wp-down-slack-alert' ); ?>">
							<p>
								<?php esc_html_e( 'In the "Features and functionality" section, click on the "Bots" panel.', 'wp-down-slack-alert' ); ?>
							</p>
							<p class="wpdsa-modal-buttons">
								<button data-target="#slack_token_help_step_3" type="button" class="button button-primary wpdsa-pagination wpdsa-pagination-next alignright"><?php esc_html_e( 'Next step' ); ?></button>
								<button data-target="#slack_token_help_step_1" type="button" class="button button-primary wpdsa-pagination wpdsa-pagination-previous alignleft"><?php esc_html_e( 'Previous step' ); ?></button>
							</p>
							<img class="wpdsa-modal-image" src="<?php echo WP_PLUGIN_URL; ?>/wp-down-slack-alert/images/config-2.png" alt="" />
						</div>
						
						<div id="slack_token_help_step_3" class="slack_token_help_steps" data-title="<?php esc_attr_e( 'Connect your Slack team: STEP 3', 'wp-down-slack-alert' ); ?>">
							<p>
								<?php esc_html_e( 'That will lead you to the "Bot user" screen. Click on "Add a Bot User" button.', 'wp-down-slack-alert' ); ?>
							</p>
							<p class="wpdsa-modal-buttons">
								<button data-target="#slack_token_help_step_4" type="button" class="button button-primary wpdsa-pagination wpdsa-pagination-next alignright"><?php esc_html_e( 'Next step' ); ?></button>
								<button data-target="#slack_token_help_step_2" type="button" class="button button-primary wpdsa-pagination wpdsa-pagination-previous alignleft"><?php esc_html_e( 'Previous step' ); ?></button>
							</p>
							<img class="wpdsa-modal-image" src="<?php echo WP_PLUGIN_URL; ?>/wp-down-slack-alert/images/config-3.png" alt="" />
						</div>

						<div id="slack_token_help_step_4" class="slack_token_help_steps" data-title="<?php esc_attr_e( 'Connect your Slack team: STEP 4', 'wp-down-slack-alert' ); ?>">
							<p>
								<?php esc_html_e( 'Leave the default names (you will be able to override that in the plugin’s settings), and click "Add bot user" button.', 'wp-down-slack-alert' ); ?>
							</p>
							<p class="wpdsa-modal-buttons">
								<button data-target="#slack_token_help_step_5" type="button" class="button button-primary wpdsa-pagination wpdsa-pagination-next alignright"><?php esc_html_e( 'Next step' ); ?></button>
								<button data-target="#slack_token_help_step_3" type="button" class="button button-primary wpdsa-pagination wpdsa-pagination-previous alignleft"><?php esc_html_e( 'Previous step' ); ?></button>
							</p>
							<img class="wpdsa-modal-image" src="<?php echo WP_PLUGIN_URL; ?>/wp-down-slack-alert/images/config-4.png" alt="" />
						</div>

						<div id="slack_token_help_step_5" class="slack_token_help_steps" data-title="<?php esc_attr_e( 'Connect your Slack team: STEP 5', 'wp-down-slack-alert' ); ?>">
							<p>
								<?php esc_html_e( 'Click on the "Install App" menu item in the navigation sidebar, then click on the "Install App to Workspace" button.', 'wp-down-slack-alert' ); ?>
							</p>
							<p class="wpdsa-modal-buttons">
								<button data-target="#slack_token_help_step_6" type="button" class="button button-primary wpdsa-pagination wpdsa-pagination-next alignright"><?php esc_html_e( 'Next step' ); ?></button>
								<button data-target="#slack_token_help_step_4" type="button" class="button button-primary wpdsa-pagination wpdsa-pagination-previous alignleft"><?php esc_html_e( 'Previous step' ); ?></button>
							</p>
							<img class="wpdsa-modal-image" src="<?php echo WP_PLUGIN_URL; ?>/wp-down-slack-alert/images/config-5.png" alt="" />
						</div>

						<div id="slack_token_help_step_6" class="slack_token_help_steps" data-title="<?php esc_attr_e( 'Connect your Slack team: STEP 6', 'wp-down-slack-alert' ); ?>">
							<p>
								<?php esc_html_e( 'Allow this Slack App to access your Slack team: click on the "Allow" button.', 'wp-down-slack-alert' ); ?>
							</p>
							<p class="wpdsa-modal-buttons">
								<button data-target="#slack_token_help_step_7" type="button" class="button button-primary wpdsa-pagination wpdsa-pagination-next alignright"><?php esc_html_e( 'Next step' ); ?></button>
								<button data-target="#slack_token_help_step_5" type="button" class="button button-primary wpdsa-pagination wpdsa-pagination-previous alignleft"><?php esc_html_e( 'Previous step' ); ?></button>
							</p>
							<img class="wpdsa-modal-image" src="<?php echo WP_PLUGIN_URL; ?>/wp-down-slack-alert/images/config-6.png" alt="" />
						</div>

						<div id="slack_token_help_step_7" class="slack_token_help_steps" data-title="<?php esc_attr_e( 'Connect your Slack team: STEP 7', 'wp-down-slack-alert' ); ?>">
							<p>
								<?php esc_html_e( 'Copy/paste the Bot User OAuth Access Token in the plugin’s settings field.', 'wp-down-slack-alert' ); ?>
							</p>
							<p class="wpdsa-modal-buttons">
								<button data-target="#slack_token_help_step_6" type="button" class="button button-primary wpdsa-pagination wpdsa-pagination-previous alignleft"><?php esc_html_e( 'Previous step' ); ?></button>
							</p>
							<img class="wpdsa-modal-image" src="<?php echo WP_PLUGIN_URL; ?>/wp-down-slack-alert/images/config-7.png" alt="" />
						</div>
					<?php endif; ?>
					</td>
				</tr>
			</table>
			<hr/>
			<h2><?php esc_html_e( 'General settings', 'wp-down-slack-alert' ); ?></h2>
			<table class="form-table">
				<tr>
					<th>
						<label for="notification_bot_disable_email"><?php esc_html_e( 'Disable administrator email notification', 'wp-down-slack-alert' ); ?></label>
					</th>
					<td>
					<?php if ( defined( 'WPDSA_NOTIFICATION_DISABLE_EMAIL' ) && ! empty( WPDSA_NOTIFICATION_DISABLE_EMAIL ) ) : ?>
						<?php
						$email_checked = 0;
						if ( 1 === WPDSA_NOTIFICATION_DISABLE_EMAIL || true === WPDSA_NOTIFICATION_DISABLE_EMAIL || 'true' === WPDSA_NOTIFICATION_DISABLE_EMAIL ) {
							$email_checked = 'checked';
						}
						?>
						<input <?php echo esc_attr( $email_checked ); ?> id="notification_bot_disable_email" name='notification_bot_disable_email' type="checkbox" value="Off" disabled />
						<span class="description"><?php esc_html_e( 'If checked, this will prevent sending recovery notification emails to website admin.', 'wp-down-slack-alert' ); ?></span>
						<p class="description">
							<?php
							echo sprintf(
								/* translators: %s: Name of the constant */
								__( 'This setting is disabled as it’s already defined with the constant %s', 'wp-down-slack-alert' ),
								'<code>WPDSA_NOTIFICATION_DISABLE_EMAIL</code>'
							);
							?>
						</p>
					<?php else : ?>
						<?php $email_checked = 1 === get_option( 'settings_slack_notification_disable_email' ) ? 'checked' : ''; ?>
						<input <?php echo esc_attr( $email_checked ); ?> id="notification_bot_disable_email" name='notification_bot_disable_email' type="checkbox" value="Off" />
						<span class="description"><?php esc_html_e( 'If checked, this will prevent sending recovery notification emails to website admin.', 'wp-down-slack-alert' ); ?></span>

						<?php $email_frequency_visibility = ! empty( $email_checked ) ? ' class="rtms_visually_hidden"' : ''; ?>
						<div<?php echo esc_attr( $email_frequency_visibility ); ?> id="tr_mail_recurrence">
							<p>
								<label for="mail_recurrence"><strong><?php esc_html_e( 'Email notifications frequency', 'wp-down-slack-alert' ); ?></strong></label>
							</p>
							<select id="mail_recurrence" name="mail_recurrence">
								<option value="3" <?php selected( $recurrence_mail, 3 ); ?>>
									<?php esc_html_e( 'Every 3 hours', 'wp-down-slack-alert' ); ?>
								</option>
								<option value="6" <?php selected( $recurrence_mail, 6 ); ?>>
									<?php esc_html_e( 'Every 6 hours', 'wp-down-slack-alert' ); ?>
								</option>
								<option value="12" <?php selected( $recurrence_mail, 12 ); ?>>
									<?php esc_html_e( 'Every 12 hours', 'wp-down-slack-alert' ); ?>
								</option>
								<option value="24" <?php selected( $recurrence_mail, 24 ); ?>>
									<?php esc_html_e( 'Every 1 day', 'wp-down-slack-alert' ); ?>
								</option>
								<option value="anytime" <?php selected( $recurrence_mail, 'anytime' ); ?>>
									<?php esc_html_e( 'Each time an error is triggered – not recommended (email flooding)', 'wp-down-slack-alert' ); ?>
								</option>
							</select>
							<p>
								<span class="description"><?php esc_html_e( 'By default an email is sent once a day to notify the site administrator.', 'wp-down-slack-alert' ); ?></span>
							</p>
						</div>
					<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th>
						<label for="notification_recurrence"><?php esc_html_e( 'Slack notifications frequency', 'wp-down-slack-alert' ); ?></label>
					</th>
					<td>
						<select id="notification_recurrence" name="notification_recurrence" <?php echo $notification_recurrence_disabled; ?>>
							<option value="0,5" <?php selected( $recurrence, '0,5' ); ?>>
								<?php esc_html_e( 'Every 30 minutes', 'wp-down-slack-alert' ); ?>
							</option>
							<option value="1" <?php selected( $recurrence, 1 ); ?>>
								<?php esc_html_e( 'Every 1 hour', 'wp-down-slack-alert' ); ?>
							</option>
							<option value="2" <?php selected( $recurrence, 2 ); ?>>
								<?php esc_html_e( 'Every 2 hour', 'wp-down-slack-alert' ); ?>
							</option>
							<option value="6" <?php selected( $recurrence, 6 ); ?>>
								<?php esc_html_e( 'Every 6 hour', 'wp-down-slack-alert' ); ?>
							</option>
							<option value="24" <?php selected( $recurrence, 24 ); ?>>
								<?php esc_html_e( 'Every 1 day', 'wp-down-slack-alert' ); ?>
							</option>
							<option value="anytime" <?php selected( $recurrence, 'anytime' ); ?>>
								<?php esc_html_e( 'Each time an error is triggered – not recommended (Slack flooding)', 'wp-down-slack-alert' ); ?>
							</option>
						</select>
					<?php if ( defined( 'WPDSA_NOTIFICATION_RECURRENCE' ) && ! empty( WPDSA_NOTIFICATION_RECURRENCE ) ) : ?>
						<p class="description">
							<?php
							echo sprintf(
								/* translators: %s: Name of the constant */
								__( 'This setting is disabled as it’s already defined with the constant %s', 'wp-down-slack-alert' ),
								'<code>WPDSA_NOTIFICATION_RECURRENCE</code>'
							);
							?>
						</p>
					<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th>
						<label for="notification_channel"><?php esc_html_e( 'Slack channel', 'wp-down-slack-alert' ); ?></label>
					</th>
					<td>
						<div style="position:relative;">
							<span style="position:absolute;left:1em;top:8px;color:#aaa;">#</span>
						<?php if ( defined( 'WPDSA_NOTIFICATION_CHANNEL' ) && ! empty( WPDSA_NOTIFICATION_CHANNEL ) ) : ?>
							<input class="regular-text" id="notification_channel" name='notification_channel' type="text" value="<?php echo esc_html( trim( WPDSA_NOTIFICATION_CHANNEL ) ); ?>" style="padding-left:2em;" disabled />
							<span class="description"><?php esc_html_e( 'The channel where you want to be notified.', 'wp-down-slack-alert' ); ?></span>
							<p class="description">
								<?php
								echo sprintf(
									/* translators: %s: Name of the constant */
									__( 'This setting is disabled as it’s already defined with the constant %s', 'wp-down-slack-alert' ),
									'<code>WPDSA_NOTIFICATION_CHANNEL</code>'
								);
								?>
							</p>
						<?php else : ?>
							<input class="regular-text" id="notification_channel" name="notification_channel" type="text" value="<?php echo esc_html( get_option( 'settings_slack_notification_channel' ) ); ?>" style="padding-left:2em;" />
							<span class="description"><?php esc_html_e( 'The channel where you want to be notified.', 'wp-down-slack-alert' ); ?></span>
						<?php endif; ?>
						</div>
					</td>
				</tr>
				<tr>
					<th>
						<label for="notification_bot_name"><?php esc_html_e( 'Bot Name', 'wp-down-slack-alert' ); ?></label>
					</th>
					<td>
					<?php if ( defined( 'WPDSA_NOTIFICATION_BOTNAME' ) && ! empty( WPDSA_NOTIFICATION_BOTNAME ) ) : ?>
						<input class="regular-text" id="notification_bot_name" name="notification_bot_name" type="text" value="<?php echo esc_html( WPDSA_NOTIFICATION_BOTNAME ); ?>" disabled />
						<p class="description">
							<?php
							echo sprintf(
								/* translators: %s: Name of the constant */
								__( 'This setting is disabled as it’s already defined with the constant %s', 'wp-down-slack-alert' ),
								'<code>WPDSA_NOTIFICATION_BOTNAME</code>'
							);
							?>
						</p>
					<?php else : ?>
						<input class="regular-text" id="notification_bot_name" name="notification_bot_name" type="text" value="<?php echo esc_html( get_option( 'settings_slack_notification_bot_name' ) ); ?>" />
					<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th>
						<label for="upload_image_button"><?php esc_html_e( 'Bot avatar image', 'wp-down-slack-alert' ); ?></label>

					</th>
					<td>
					<?php if ( defined( 'WPDSA_NOTIFICATION_MESSAGE_IMAGE' ) && ! empty( WPDSA_NOTIFICATION_MESSAGE_IMAGE ) ) : ?>
						<?php $image_src = $id_image; ?>
						<p class="description">
							<?php
							echo sprintf(
								/* translators: %s: Name of the constant */
								__( 'This setting is disabled as it’s already defined with the constant %s', 'wp-down-slack-alert' ),
								'<code>WPDSA_NOTIFICATION_MESSAGE_IMAGE</code>'
							);
							?>
						</p>
					<?php else : ?>
						<input id="upload_image_button" type="button" class="button" value="<?php esc_html_e( 'Upload image', 'wp-down-slack-alert' ); ?>"/>
						<input type="hidden" name="image_attachment_id" id="image_attachment_id" value="<?php echo (int) $id_image; ?>">
						<?php
						$image_src = 'http://assets.whodunit.fr/signatures/whodunit.png';
						if ( '' !== $id_image && wp_get_attachment_url( $id_image ) ) {
							$image_src = wp_get_attachment_url( $id_image );
						}
						?>
					<?php endif; ?>
						<div class="image-preview-wrapper">
							<img id="image-preview" src="<?php echo esc_url( $image_src ); ?>" style="height:100px;">
						</div>
					</td>
				</tr>
			</table>
			<hr/>
			<h2><?php esc_html_e( 'Alert settings', 'wp-down-slack-alert' ); ?></h2>
			<table class="form-table">
				<tr>
					<th>
						<label for="notification_message_title"><?php esc_html_e( 'Alert title', 'wp-down-slack-alert' ); ?></label>
					</th>
					<td>
					<?php if ( defined( 'WPDSA_NOTIFICATION_MESSAGE_TITLE' ) && ! empty( WPDSA_NOTIFICATION_MESSAGE_TITLE ) ) : ?>
						<input type="text" id="notification_message_title" name="notification_message_title" class="regular-text" value="<?php echo sanitize_text_field( WPDSA_NOTIFICATION_MESSAGE_TITLE ); ?>" disabled />
						<p class="description">
							<?php
							echo sprintf(
								/* translators: %s: Name of the constant */
								__( 'This setting is disabled as it’s already defined with the constant %s', 'wp-down-slack-alert' ),
								'<code>WPDSA_NOTIFICATION_MESSAGE_TITLE</code>'
							);
							?>
						</p>
					<?php else : ?>
						<?php
						$notification_message_title = esc_html__( 'Warning: website out of order', 'wp-down-slack-alert' );
						if ( get_option( 'settings_slack_notification_message_title' ) ) {
							$notification_message_title = esc_attr( get_option( 'settings_slack_notification_message_title' ) );
						}
						?>
						<input type="text" id="notification_message_title" name="notification_message_title" class="regular-text" value="<?php echo $notification_message_title; ?>" />
					<?php endif; ?>
					</td>
				</tr>
				<tr>
					<th>
						<label for="notification_message_footer"><?php esc_html_e( 'Notification footer text', 'wp-down-slack-alert' ); ?></label>
					</th>
					<td>
					<?php if ( defined( 'WPDSA_NOTIFICATION_MESSAGE_FOOTER' ) && ! empty( WPDSA_NOTIFICATION_MESSAGE_FOOTER ) ) : ?>
						<input type="text" id="notification_message_footer" name="notification_message_footer" class="regular-text" value="<?php echo sanitize_text_field( WPDSA_NOTIFICATION_MESSAGE_FOOTER ); ?>" disabled />
						<p class="description">
							<?php
							echo sprintf(
								/* translators: %s: Name of the constant */
								__( 'This setting is disabled as it’s already defined with the constant %s', 'wp-down-slack-alert' ),
								'<code>WPDSA_NOTIFICATION_MESSAGE_FOOTER</code>'
							);
							?>
						</p>
					<?php else : ?>
						<?php
						$notification_message_footer = esc_html__( 'WP Down Slack Alert, by Whodunit', 'wp-down-slack-alert' );
						if ( get_option( 'settings_slack_notification_message_footer' ) ) {
							$notification_message_footer = esc_attr( get_option( 'settings_slack_notification_message_footer' ) );
						}
						?>
						<input type="text" id="notification_message_footer" name="notification_message_footer" class="regular-text" value="<?php echo $notification_message_footer; ?>" />
					<?php endif; ?>
					</td>
				</tr>
			</table>
			<p class="submit">
				<input type="submit" name="submit_settings_slack_notification" value="<?php esc_html_e( 'Save changes', 'wp-down-slack-alert' ); ?>" class="button-primary">
			</p>
		</form>

		<script type='text/javascript'>
			jQuery(document).ready(function ($) {
				// Uploading files
				var file_frame;
				var wp_media_post_id = wp.media.model.settings.post.id; // Store the old id
				var set_to_post_id = '<?php echo (int) $id_image; ?>'; // Set this
				jQuery('#upload_image_button').on('click', function (event) {
					event.preventDefault();
					// If the media frame already exists, reopen it.
					if (file_frame) {
						// Set the post ID to what we want
						file_frame.uploader.uploader.param('post_id', set_to_post_id);
						// Open frame
						file_frame.open();
						return;
					} else {
						// Set the wp.media post id so the uploader grabs the ID we want when initialised
						wp.media.model.settings.post.id = set_to_post_id;
					}
					// Create the media frame.
					file_frame = wp.media.frames.file_frame = wp.media({
						title: 'Select a image to upload',
						button: {
							text: 'Use this image',
						},
						multiple: false // Set to true to allow multiple files to be selected
					});
					// When an image is selected, run a callback.
					file_frame.on('select', function () {
						// We set multiple to false so only get one image from the uploader
						attachment = file_frame.state().get('selection').first().toJSON();
						// Do something with attachment.id and/or attachment.url here
						$('#image-preview').attr('src', attachment.url).css('width', 'auto');
						$('#image_attachment_id').val(attachment.id);
						// Restore the main post ID
						wp.media.model.settings.post.id = wp_media_post_id;
					});
					// Finally, open the modal
					file_frame.open();
				});
				// Restore the main ID when the add media button is pressed
				jQuery('a.add_media').on('click', function () {
					wp.media.model.settings.post.id = wp_media_post_id;
				});
			});
		</script>
	</div>
	<?php
}

function wpdsa_check_slack_connexion( $token ) {

	$response = wp_remote_get( 'https://slack.com/api/auth.test?token=' . $token . '&pretty=1' );

	$connexion_status = array(
		'connected' => false,
		'team'      => false,
		'error'     => false,
	);

	if ( ! is_wp_error( $response ) ) {
		if ( 200 === wp_remote_retrieve_response_code( $response ) ) {
			$body                = wp_remote_retrieve_body( $response );
			$is_connected_object = json_decode( $body );

			$connexion_status['team'] = false;
			if( isset( $is_connected_object->team ) ) {
			    $connexion_status['team'] = $is_connected_object->team;
            }

			$connexion_status['error'] = false;
            if( isset( $is_connected_object->error ) ) {
                $connexion_status['error'] = $is_connected_object->error;
            }
			$connexion_status['connected'] = $is_connected_object->ok;
		} else {
			error_log( wp_remote_retrieve_response_message( $response ) );
		}
	} else {
		error_log( $response->get_error_message() );
	}

	if ( empty( get_option( 'settings_slack_notification_channel' ) ) ) {
		$connexion_status['connected'] = false;
		$connexion_status['error']     = 'Empty Slack channel';
	}

	return $connexion_status;
}

add_filter( 'wp_php_error_message', 'wpdsa_send_slack_notification', 10, 2 );
function wpdsa_send_slack_notification( $message, $error ) {
	$new_value = time();

	$option_name = 'who_recovery_mode_time';
	$data = get_option( $option_name );

	$channel = sanitize_text_field( get_option( 'settings_slack_notification_channel' ) );
	if ( defined( 'WPDSA_NOTIFICATION_CHANNEL' ) && ! empty( WPDSA_NOTIFICATION_CHANNEL ) ) {
		$channel = sanitize_text_field( WPDSA_NOTIFICATION_CHANNEL );
	}

	$bot_name = sanitize_text_field( get_option( 'settings_slack_notification_bot_name' ) );
	if ( defined( 'WPDSA_NOTIFICATION_BOTNAME' ) && ! empty( WPDSA_NOTIFICATION_BOTNAME ) ) {
		$bot_name = sanitize_text_field( WPDSA_NOTIFICATION_BOTNAME );
	}

	$message_title_custom = sanitize_text_field( get_option( 'notification_message_title' ) );
	$message_title_default = esc_html__( 'Warning: website out of order', 'wp-down-slack-alert' );
	$message_title = ! empty( $message_title_custom ) ? $message_title_custom : $message_title_default;
	if ( defined( 'WPDSA_NOTIFICATION_MESSAGE_TITLE' ) && ! empty( WPDSA_NOTIFICATION_MESSAGE_TITLE ) ) {
		$message_title = sanitize_text_field( WPDSA_NOTIFICATION_MESSAGE_TITLE );
	}

	$message_footer_custom = sanitize_text_field( get_option( 'notification_message_footer' ) );
	$message_footer_default = esc_html__( 'WP Down Slack Alert, by Whodunit', 'wp-down-slack-alert' );
	$message_footer = ! empty( $message_footer_custom ) ? $message_footer_custom : $message_footer_default;
	if ( defined( 'WPDSA_NOTIFICATION_MESSAGE_FOOTER' ) && ! empty( WPDSA_NOTIFICATION_MESSAGE_FOOTER ) ) {
		$message_footer = sanitize_text_field( WPDSA_NOTIFICATION_MESSAGE_FOOTER );
	}

	$recurrence = sanitize_text_field( get_option( 'settings_slack_notification_recurrence' ) );
	if ( defined( 'WPDSA_NOTIFICATION_RECURRENCE' ) && ! empty( WPDSA_NOTIFICATION_RECURRENCE ) ) {
		$recurrence = sanitize_text_field( WPDSA_NOTIFICATION_RECURRENCE );
	}
	$recurrence = ( isset( $recurrence ) && '' !== $recurrence ) ? $recurrence : 1;
	$recurrence = ( 'anytime' === $recurrence ) ? 0 : $recurrence * 3600; // 3600s -> 1h

	$token = sanitize_text_field( get_option( 'settings_slack_notification_token' ) );
	if ( defined( 'WPDSA_NOTIFICATION_TOKEN' ) && ! empty( WPDSA_NOTIFICATION_TOKEN ) ) {
		$token = sanitize_text_field( WPDSA_NOTIFICATION_TOKEN );
	}

	$connexion_status = wpdsa_check_slack_connexion( $token );

	if ( true === $connexion_status['connected'] ) {
		if ( $new_value > ( $data + $recurrence ) ) {
			update_option( $option_name, $new_value );
			$url = esc_html__( 'Site URL:', 'wp-down-slack-alert' ) . ' ' . get_bloginfo( 'url' ) . "\n";
			if ( defined( 'RECOVERY_MODE_EMAIL' ) ) {
				$email = esc_html__( 'Technical contact:', 'wp-down-slack-alert' ) . ' ' . RECOVERY_MODE_EMAIL . "\n";
			} else {
				$email = esc_html__( 'Admin email:', 'wp-down-slack-alert' ) . ' ' . get_bloginfo( 'admin_email' ) . "\n";
			}
			$error = $error['message'] . ' ' . esc_html__( 'at line', 'wp-down-slack-alert' ) . ' ' . $error['line'] . "\n";
			$username = ( isset( $bot_name ) && '' !== $bot_name ) ? $bot_name : 'WP_Recovery_Mode';
			$img = wp_get_attachment_url( get_option( 'settings_slack_notification_attachment_id' ) );
			$icon_url = ( isset( $img ) && '' !== $img ) ? $img : 'http://assets.whodunit.fr/signatures/whodunit.png';
			$disable_email = esc_html( get_option( 'settings_slack_notification_disable_email' ) );
			if ( defined( 'WPDSA_NOTIFICATION_DISABLE_EMAIL' ) && ! empty( WPDSA_NOTIFICATION_DISABLE_EMAIL ) ) {
				if ( 1 === intval( WPDSA_NOTIFICATION_DISABLE_EMAIL ) || true === WPDSA_NOTIFICATION_DISABLE_EMAIL || 'true' === WPDSA_NOTIFICATION_DISABLE_EMAIL ) {
					$disable_email = 1;
				} else {
					$disable_email = 0;
				}
			}
			$email_sent = esc_html__( 'An email was sent to the website admin.', 'wp-down-slack-alert' );
			if ( isset( $disable_email ) && 1 === $disable_email ) {
				$email_sent = esc_html__( 'No email was sent to the website admin.', 'wp-down-slack-alert' );
			}
			$pretext = sanitize_text_field( get_option( 'settings_slack_notification_message_title' ) );
			if ( defined( 'WPDSA_NOTIFICATION_MESSAGE_TITLE' ) && ! empty( WPDSA_NOTIFICATION_MESSAGE_TITLE ) ) {
				$pretext = sanitize_text_field( WPDSA_NOTIFICATION_MESSAGE_TITLE );
			}
			$pretext = ( isset( $pretext ) && ! empty( $pretext ) ) ? $pretext : esc_html__( 'Broken Website Notification', 'wp-down-slack-alert' );

			$attachments = array(
				array(
					'fallback'    => esc_html__( 'Error notification', 'wp-down-slack-alert' ),
					'pretext'     => $pretext,
					'title'       => esc_html__( 'Site name:', 'wp-down-slack-alert' ) . ' ' . get_bloginfo( 'name' ),
					'text'        => $url . $email . $error,
					'fields'      => array(
						array(
							'title' => esc_html__( 'Email notification', 'wp-down-slack-alert' ),
							'value' => $email_sent,
							'short' => true,
						),
						array(
							'title' => esc_html__( 'WordPress version', 'wp-down-slack-alert' ),
							'value' => get_bloginfo( 'version' ),
							'short' => true,
						),
					),
					'color'       => 'C03',
					'footer'      => $message_footer,
					'footer_icon' => 'https://whodunit.fr/logo-slack.png',
				),
			);

			$body = array(
				'channel'     => $channel,
				'username'    => $username,
				'attachments' => wp_json_encode( $attachments ),
				'icon_url'    => $icon_url,
			);
			$body = wp_json_encode( $body );

			$response = wp_remote_post(
				'https://slack.com/api/chat.postMessage',
				array(
					'headers' => array(
						'Authorization' => 'Bearer ' . $token,
						'Content-Type'  => 'application/json; charset=utf-8',
					),
					'body'    => $body,
				)
			);

			if ( ! is_wp_error( $response ) ) {
				if ( 200 !== wp_remote_retrieve_response_code( $response ) ) {
					error_log( wp_remote_retrieve_response_message( $response ) );
				}
			} else {
				error_log( $response->get_error_message() );
			}
		}
	}

	return $message;
}

/**
 * Add a link to the settings on the Plugins screen.
 */
function wpdsa_add_settings_link( $links, $file ) {
	if ( 'wp-down-slack-alert/wp-down-slack-alert.php' === $file && current_user_can( 'manage_options' ) ) {
		if ( 'plugin_action_links' === current_filter() ) {
			$url = admin_url( 'tools.php?page=settings-slack-notification' );
		}
		// Prevent warnings in PHP 7.0+ when a plugin uses this filter incorrectly.
		$links   = (array) $links;
		$links[] = sprintf( '<a href="%s">%s</a>', $url, esc_html__( 'Settings', 'wp-down-slack-alert' ) );
	}

	return $links;
}
add_filter( 'plugin_action_links', 'wpdsa_add_settings_link', 10, 2 );
