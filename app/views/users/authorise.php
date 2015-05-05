<h1>Authorise</h1>

<?php if(isset($authorised) === true): ?>
<p class="success">User <?php echo $authorised; ?> has been authorised.</p>
<?php endif ?>

<?php 
$form = Form::open('users/authorise') . Form::hidden('token', Security::token()) ;
$rejectform = Form::open('users/reject') . Form::hidden('token', Security::token()) ;
?>
	<ul class="noBullets spaced">
	<?php foreach($users as $user):
		echo '<li>'.
			$form . Form::submit('username', $user->username, array('class' => 'submit')).
			' ('.Html::chars($user->email).
			') ' . Html::chars($user->plugins) . '</form>';
                echo $rejectform . Form::hidden('username', $user->username) . Form::submit('reject', 'Reject') . '</form>';
                echo '</li>';
	endforeach ?>
	</ul>
	
</form>
