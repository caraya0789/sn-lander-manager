<?php defined("ABSPATH") or die() ?>
<?php if(isset($_GET['purge']) && $_GET['purge'] == 1): ?>
<div class="wrap">
	<p>Errors deleted</p>
	<a href="<?php echo admin_url( "tools.php?page=sn-api-errors" ); ?>" class="button">Go Back</a>
</div>
<?php else: ?>
<div class="wrap">
	<h2>Lander Manager API Errors
		<a href="<?php echo admin_url( "tools.php?page=sn-api-errors&purge=1" ); ?>" class="page-title-action">Purge</a>
	</h2>
	<?php if(count($errors)): ?>
	<table class="wp-list-table widefat">
		<thead>
			<tr>
				<th>API</th>
				<th>Message</th>
				<th>Browser</th>
				<th>Page</th>
				<th>Date</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach($errors as $err): ?>
			<tr>
				<td><?php echo esc_html( $err['api'] ); ?></td>
				<td><?php echo esc_html( $err['message'] ); ?></td>
				<td><?php echo isset($err['browser']) ? esc_html( $err['browser'] ) : ''; ?></td>
				<td><?php echo esc_html( $err['referer'] ); ?></td>
				<td><?php echo date( 'Y-m-d g:i:s a', $err['time'] ); ?></td>
			</tr>
			<?php endforeach ?>
		</tbody>
	</table>
	<?php else: ?>
	<p>No Errors yet... YAY!!!</p>
	<?php endif ?>
</div>
<?php endif ?>