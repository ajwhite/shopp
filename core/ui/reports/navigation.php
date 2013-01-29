<ul class="subsubsub">
	<?php
		$reports = Report::reports();
		foreach((array)$reports as $id => $rt):
			$this_report = isset($_GET['report']) && array_key_exists($_GET['report'],$reports)?$_GET['report']:'sales';
			$args = array('report'=> $id);
			$url = add_query_arg(array_merge($_GET,$args),admin_url('admin.php'));
			$classes = $this_report === $id?' class="current"':'';
			$separator = is_null($separator)?'':'| ';
	?>
		<li><?php echo $separator; ?><a href="<?php echo esc_url($url); ?>"<?php echo $classes; ?>><?php esc_html_e($reports[$id]['label']); ?></a></li>
	<?php endforeach; ?>
</ul>