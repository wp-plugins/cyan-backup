<?php

	if( !is_admin() )
		wp_die(__('Access denied!', $this->textdomain));
		
	$notes = array();
	$nonce_field = 'backup';

	if (isset($_POST['remove_backup'])) {
		if ($this->wp_version_check('2.5') && function_exists('check_admin_referer'))
			check_admin_referer($nonce_field, self::NONCE_NAME);
		if (isset($_POST['remove'])) {
			$postdata = $this->get_real_post_data();
			$count = 0;
			foreach((array)$_POST['remove'] as $index => $bfile) {
				$file = $postdata['remove[' . $index . ']'];
				if (($file = realpath($file)) !== FALSE) {
					if (@unlink($file))
						$count ++;
				}
			}
			if ($count > 0) {
				$notes[] = "<strong>".__('ERROR: Failed to delete backup Files!', $this->textdomain)."</strong>";
			}
		}
	}

	// Output
	foreach( $notes as $note ) {
		echo '<div id="message" class="updated fade"><p>' . $note . '</p></div>';
		echo "\n";
	}

	
	$nonces =
		( $this->wp_version_check('2.5') && function_exists('wp_nonce_field') )
		? wp_nonce_field($nonce_field, self::NONCE_NAME, true, false)
		: '';
?>
<div class="wrap">

	<div id="icon-options-cyan-backup" class="icon32"><br /></div>

	<h2><?php _e('CYAN Backup', $this->textdomain);?></h2>

	<h3><?php _e('Run Backup', $this->textdomain);?></h3>

	<form method="post" id="backup_site" action="'.$this->admin_action.'">
		<?php echo $nonces;?>
		<input type="hidden" name="backup_site" class="button-primary sites_backup" value="<?php _e('Backup Now!', $this->textdomain)?>" class="button" style="margin-left:1em;" />
		<p style="margin-top:1em">
			<input type="submit" name="backup_site" class="button-primary sites_backup" value="<?php _e('Backup Now!', $this->textdomain)?>" class="button" style="margin-left:1em;" />
		</p>
	</form>

	<div id="progressbar"></div>
	<br>
	<div id="progresstext" style="margin-left: 13px;">&nbsp;</div>
	
	<h3><?php _e('Backup Files', $this->textdomain);?></h3>

	<form method="post" action="'.$this->admin_action.'">
		<?php echo $nonces;?>

		<table id="backuplist" class="wp-list-table widefat fixed" style="margin-top:0;">

			<thead>
				<tr>
					<th><?php _e('File Name', $this->textdomain);?></th>
					<th><?php _e('Date and Time', $this->textdomain);?></th>
					<th><?php _e('Size', $this->textdomain);?></th>
					<th style="text-align: center;"><input type="checkbox" id="switch_checkboxes" name="switch_checkboxes" style="margin: 0px 4px 0px 0px;" /></th>
				</tr>
			</thead>

			<tfoot>
				<tr>
					<th colspan="3"></th>
					<th style="width: 75px; text-align: center;"><input type="submit" name="remove_backup" class="button-primary" value="<?php _e('Delete', $this->textdomain);?>" class="button" /></th>
				</tr>
			</tfoot>

			<tbody>
<?php
		$backup_files = $this->backup_files_info($this->get_backup_files());
		$alternate = ' class="alternate"';
		if (count($backup_files) > 0) {
			$i = 0;
			foreach ($backup_files as $backup_file) {
				echo "\t\t\t\t<tr{$alternate}>\n";
				printf("\t\t\t\t\t<td>%s</td>\n", $backup_file['url']);

				$temp_time = strtotime( $backup_file['filemtime'] );

				printf("\t\t\t\t\t<td>%s</td>\n", date( get_option('date_format'), $temp_time ) . ' @ ' . date( get_option('time_format'), $temp_time ));
				printf("\t\t\t\t\t<td>%s MB</td>\n", number_format($backup_file['filesize'], 2));
				echo "\t\t\t\t\t<td style='text-align: center;'><input type=\"checkbox\" id=\"removefiles[{$i}]\" name=\"remove[{$i}]\" value=\"{$backup_file['filename']}\" /></td>\n";
				echo "\t\t\t\t</tr>\n";
				$i++;
				$alternate = empty($alternate) ? ' class="alternate"' : '';
			}
		}
?>
			</tbody>
		</table>
	</form>
</div>