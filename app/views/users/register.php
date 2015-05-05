<h1>Register</h1>

<?php echo Form::open('users/register') ?>
	<p>This forms allows Notepad++ plugin developers to register for access 
  to maintain the plugin list for Plugin Manager.  If you're not involved in plugin
development or maintainance then you don't need access (nor will it be granted!).</p>

<p>After registering, you account is reviewed.  Only accounts from genuine plugin developers or maintainers will
be accepted.  This is to protect the security of the plugin list.
</p>
<p>Please don't forget to add which plugins you're involved in at the bottom of this form, that makes it easier for us
to identify you</p>
<p><b>Note to spammers: Please don't waste your time and ours by registering - accounts are reviewed by a human. That human 
could be writing open source code to help improve Notepad++ - that you probably use - instead of rejecting your accounts. </b></p>

	<div>
		<label for="username">Username</label>
		<?php echo Form::input('username', isset($data) === true ? $data['username'] : '', array('id'=>'username')) ?>
		<?php if(isset($errors, $errors['username']) === true): ?>
			<p class="error"><?php echo $errors['username'] ?></p>
		<?php endif ?>
	</div>
	<div>
		<label for="email">Email</label>
		<?php echo Form::input('email', isset($data) === true ? $data['email'] : '', array('id'=>'email')) ?>
		<?php if(isset($errors, $errors['email']) === true): ?>
			<p class="error"><?php echo $errors['email'] ?></p>
		<?php endif ?>
	</div>
	<div>
		<label for="pass">Password</label>
		<?php echo Form::password('password', '', array('id'=>'pass')) ?>
		<?php if(isset($errors, $errors['password']) === true): ?>
			<p class="error"><?php echo $errors['password'] ?></p>
		<?php endif ?>
	</div>
	<div>
		<label for="confirm_pass">Confirm password</label>
		<?php echo Form::password('password_confirm', '', array('id'=>'confirm_pass')) ?>
		<?php if(isset($errors, $errors['password_confirm']) === true): ?>
			<p class="error"><?php echo $errors['password_confirm'] ?></p>
		<?php endif ?>
	</div>
	<div>
		<label for="plugins">Plugins involved with / comments</label>
		<?php echo Form::textarea('plugins', isset($data) === true ? $data['plugins'] : '', array('id'=>'plugins', 'rows'=>'4', 'cols'=>'40')) ?>
		<?php if(isset($errors, $errors['plugins']) === true): ?>
			<p class="error"><?php echo $errors['plugins'] ?></p>
		<?php endif ?>
	</div>
	
	<?php echo Form::submit(null, 'Register', array('class' => 'submit')) ?>
</form>
