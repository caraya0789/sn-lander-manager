<div class="wrap" id="sn-lander-manager">
<h1>Templates <a class="page-title-action hide-if-no-customize" href="<?php menu_page_url('sn-lander-manager'); ?>&action=add">Add New Template</a></h1>

<h2>List of Templates</h2>
<p>These templates appear as page template in "add page" section, you can enable or disable them</p>

<form method="post">
<table class="striped widefat"> 
	<thead>
		<tr>
			<td><strong>Lander</strong></td>
			<td><strong>Used on</strong></td>
			<td><strong>Description</strong></td>
			<td style="text-align: center;"><strong>Enabled</strong></td>
		</tr>
	</thead>
	
	<tbody>
		<?php 
			foreach ($template_folders as $key => $value) {
				$json = SNLM_PATH . 'landers/'.$value.'/options.json';
				$options = file_get_contents($json);
				$options = json_decode($options, true);

				if(!$options)
					continue;

				$countPages = 0;
				foreach ($pages as $key => $page) {
					$templateSlug = basename(dirname(get_page_template_slug($page->ID)));
					if($templateSlug == $value){
						$countPages++;
					}
				}
			?>
				<tr>
					<td class="plugin-title column-primary"><?php echo $options['name'] ?></td>
					<td><?php echo $countPages; ?> pages</td>
					<td><?php echo $options['description'] ?></td>
					<td align="center">
						<input <?php echo in_array($value, $templates) ? 'checked' : '' ?> type="checkbox" name="active[]" value="<?php echo $value ?>">
					</td>
				</tr>

		<?php }?>
	</tbody>
</table>
<p><button class="button button-primary">Update Templates</button></p>
<input type="hidden" name="action" value="update-templates">
</form>
</div>





