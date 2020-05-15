<?php
/**
 * Plugin Name:			Local Development
 * Description:			📆 By Nettvendt.
 * Plugin URI:          https://nettvendt.no/
 * Version:				1.0
 * Author:				Knut Sparhell
 * Author URI:			https://profiles.wordpress.org/knutsp/
 * Requires at least:	5.1.1
 * Requires PHP:        7.1
 * Tested up to:		5.4.1
 * Domain Path:         /languages
 * Text Domain:         wp-local-dev-master
 *
 * @author knutsp
 */

function webfacing_config_dir(): ?string {
	$config_file = 'wp-config.php';
	$parent_dir = dirname( ABSPATH );
	if ( file_exists( ABSPATH . $config_file ) ) {
		$dir = ABSPATH;
	} elseif ( @file_exists( $parent_dir . '/' . $config_file ) && ! @file_exists( $parent_dir . '/wp-settings.php' ) ) {
		$dir = $parent_dir;
	} else {
		$dir = null;
	}
	return $dir;
}

add_filter( 'admin_menu', function() {
	$constant = 'WP_LOCAL_DEV';
	$textdomain = 'wp-local-dev-master';
	$const_user = 'WEBFACING_DEVELOPER_LOGIN';
	$restricted_user = defined( $const_user ) ? constant( $const_user ) : false;
	add_management_page( __( 'Local Development', $textdomain ), __( 'Local Development', $textdomain ), 'manage_options', $textdomain, function() use( $constant, $textdomain, $const_user, $restricted_user ) { ?>
		<div class="wrap">
		<h1><?=get_admin_page_title()?></h1>
		<p><?=sprintf(__('Set or change a few constants in %s.',$textdomain),'<code>wp-config.php</code>')?></p>
<?php
		$has_access = current_user_can( 'install_plugins' ) && ( ! $restricted_user || wp_get_current_user()->user_login == $restricted_user );
		$local_dev_file = webfacing_config_dir() . '/' . $textdomain . '.php';
		if ( $_SERVER['REQUEST_METHOD'] == 'POST' ) {
			if ( $has_access ) {
				$sub_local_dev = ! empty( $_POST['local_dev'] );
				$saved = file_put_contents( $local_dev_file, '<?php const ' . $constant . ' = ' . ( $sub_local_dev ? 'true' : 'false' ) . ';' . PHP_EOL );
				echo '<p>', $saved ? __( 'Constant successfylly set.', $textdomain ) : __( 'There was an error setting the constant.', $textdomain ), '</p>';
			} else {
				wp_die( __( "You don't have access to this page.", $textdomain ) . ' ' . $restricted_user ) .'.';
			}
		}
		$content = file_get_contents( $local_dev_file );
		$local_dev = trim( end( explode( ' ', $content ) ) ) === 'true;'; ?>
			<form action="<?=add_query_arg(['page'=>esc_attr($_GET['page'])],$_SERVER['REQUEST_URI'])?>" method="post">
				<p>
					<fieldset>
						<label for="local-dev">
							<input type="checkbox" id="local-dev" name="local_dev" value="1" <?=checked($local_dev,true,true)?>/> <code><?=$constant?></code>
						</label>
					</fieldset>
				</p>
				<button type="submit" class="button-primary"<?=disabled($has_access,false,true)?>><?=__('Save Changes')?></button>
			</form>
<?php
		if ( ! defined( $constant ) || constant( $constant ) != $local_dev ) { ?>
			<hr style="height: 2em;" />
			<h2><?=sprintf(__('Make the lower part of your %s file like this',$textdomain),'<code>wp-config.php</code>')?></h2>
			<pre style="background-color: white; padding: 0 1em; width: 60em; font-family: 'Lucida Console';">/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_LOCAL_DEV
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
<span style="background-color: yellow;">include '<?=$textdomain?>.php';</span>
<span style="background-color: yellow;">const <?=$const_user?>      = '<?=$restricted_user?$restricted_user:wp_get_current_user()->user_login?>';</span>
const WP_DEBUG                       = <span style="background-color: yellow;"><?=$constant?>;</span>
const WP_DISABLE_FATAL_ERROR_HANDLER =   WP_DEBUG;
const SCRIPT_DEBUG                   =   WP_DEBUG;
const SAVEQUERIES                    =   WP_DEBUG;
const WP_ALLOW_REPAIR                =   WP_DEBUG;
const JETPACK_DEV_DEBUG              =   WP_DEBUG;
const TWO_FACTOR_DISABLE             =   WP_DEBUG;
const JETPACK_DEV_DEBUG              =   WP_DEBUG;
const ENABLE_HOT_RELOADING_FOR_DEV   =   WP_DEBUG;
const FORCE_SSL_ADMIN                = ! WP_DEBUG;
const CONCATENATE_SCRIPTS            = ! WP_DEBUG;
const COMPRESS_SCRIPTS               = ! WP_DEBUG;
const COMPRESS_CSS                   = ! WP_DEBUG;
const CORE_UPGRADE_SKIP_NEW_BUNDLED  = ! WP_DEBUG;
const WP_DEBUG_DISPLAY               = false;
const DISALLOW_FILE_EDIT             = true;
const ALLOW_UNFILTERED_UPLOADS       = true;
const IMAGE_EDIT_OVERWRITE           = true;
const WP_CACHE                       = true;
const WP_POST_REVISIONS              = 10;
const WP_MEMORY_LIMIT                = '256M';
const WP_MAX_MEMORY_LIMIT            = '512M';
const WP_DEBUG_LOG                   = __DIR__ . '/php.log';
@ini_set( 'error_log', WP_DEBUG_LOG );

/* That's all, stop editing! Happy publishing. */</pre>
<?php
		} ?>
		</div>
<?php
	}, 9999 );
}, 9999 );