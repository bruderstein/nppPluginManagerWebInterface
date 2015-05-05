<?php if($step->from !== null): ?>
<div class="copy">
	<div>
		<label for="from_<?php echo $key ?>">From</label>
		<?php echo Form::input(null, $step->from, array('id'=>'form_'.$key, 'class'=>'wide i_from')) ?>
	</div>
	<div>
		<label for="to_<?php echo $key ?>">To</label>
		<?php $segments = explode('\\', $step->to, 2); ?>
		<a class="variable"><?php echo $segments[0] ?></a>
		<?php echo Form::input(null, isset($segments[1]) ? $segments[1] : '', array('id'=>'to_'.$key, 'class'=>'path i_to')) ?>
	</div>
	<div class="options">
		<?php echo Form::checkbox(null, 1, (bool) $step->validate, array('id'=>'validate_'.$key, 'class'=>'i_validate')) ?>
		<label for="validate_<?php echo $key ?>">Validate</label>
		<?php echo Form::checkbox(null, 1, (bool) $step->backup, array('id'=>'backup_'.$key, 'class'=>'i_backup')) ?>
		<label for="backup_<?php echo $key ?>">Backup</label>
		<?php echo Form::checkbox(null, 1, (bool) $step->is_dir, array('id'=>'directory_'.$key, 'class'=>'i_directory')) ?>
		<label for="directory_<?php echo $key ?>">Directory</label>
		<?php echo Form::checkbox(null, 1, (bool) $step->recursive, array('id'=>'recursive_'.$key, 'class'=>'i_recursive')) ?>
		<label for="recursive_<?php echo $key ?>">recursive</label>
                <?php echo Form::hidden('replace_'.$key, (bool) $step->replace, array('id'=>'replace_'.$key, 'class'=>'i_replace')) ?>
		<?php echo Form::hidden('isGpup_'.$key, (bool) $step->isgpup, array('id'=>'isgpup_'.$key, 'class'=>'i_isgpup')) ?>
	</div>
<?php elseif($step->url !== null): ?>
<div class="download">
	<div>
		<label for="from_<?php echo $key ?>">Download Url</label>
		<?php echo Form::input(null, $step->url, array('id'=>'form_'.$key, 'class'=>'wide i_url')) ?>
	</div>
	<div>
		<label for="md5_<?php echo $key ?>">Md5</label>
		<?php echo Form::input(null, $step->md5, array('id'=>'md5_'.$key, 'class'=>'wide i_md5')) ?>
	</div>
<?php elseif($step->run !== null): ?>
<div class="run">
	<div>
		<label for="run_<?php echo $key ?>">Run File</label>
		<?php echo Form::input(null, $step->run, array('id'=>'run_'.$key, 'class'=>'wide i_run')) ?>
	</div>
	<div>
		<label for="arguments_<?php echo $key ?>">Arguments</label>
		<?php echo Form::input(null, $step->arguments, array('id'=>'arguments_'.$key, 'class'=>'path i_arguments')) ?>
	</div>
	<div class="options">
		<?php echo Form::checkbox(null, 1, (bool) $step->outside, array('id'=>'outside_npp_'.$key, 'class'=>'i_outside')) ?>
		<label for="outside_npp_<?php echo $key ?>">Run when Notepad++ is closed</label>
	</div>
<?php else: ?>
<div class="delete">
	<div>
		<label for="delete_<?php echo $key ?>">Delete</label>
		<?php $segments = explode('\\', $step->delete, 2) ?>
		<a class="variable"><?php echo $segments[0] ?></a>
		<?php echo Form::input(null, $segments[1], array('id'=>'delete_'.$key, 'class'=>'path i_delete')) ?>
	</div>
<?php endif ?>
</div>
