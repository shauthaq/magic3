<?php // no direct access
defined('_JEXEC') or die('Restricted access'); ?>
<script language="javascript" type="text/javascript">
/* <![CDATA[ */
function submitbutton( pressbutton ) {
	var form = document.userform;
	var r = new RegExp("[<|\>|\"|\'|\%|\;|\(|\)|\&|\+|\-]", "i");

	if (pressbutton == 'cancel') {
		form.task.value = 'cancel';
		form.submit();
		return;
	}

	// do field validation
	if (form.name.value == "") {
		alert( "<?php echo JText::_( 'Please enter your name.', true );?>" );
	} else if (form.email.value == "") {
		alert( "<?php echo JText::_( 'Please enter a valid e-mail address.', true );?>" );
	} else if (((form.password.value != "") || (form.password2.value != "")) && (form.password.value != form.password2.value)){
		alert( "<?php echo JText::_( 'REGWARN_VPASS2', true );?>" );
	} else if (r.exec(form.password.value)) {
		alert( "<?php printf( JText::_( 'VALID_AZ09', true ), JText::_( 'Password', true ), 4 );?>" );
	} else {
		form.submit();
	}
}
/* ]]> */
</script>
<form action="index.php" method="post" name="userform">
<div class="Post">
    <div class="Post-tl"></div>
    <div class="Post-tr"><div></div></div>
    <div class="Post-bl"><div></div></div>
    <div class="Post-br"><div></div></div>
    <div class="Post-tc"><div></div></div>
    <div class="Post-bc"><div></div></div>
    <div class="Post-cl"><div></div></div>
    <div class="Post-cr"><div></div></div>
    <div class="Post-cc"></div>
    <div class="Post-body">
<div class="Post-inner">

<?php if ($this->params->def('show_page_title', 1)): ?>
<h2 class="PostHeaderIcon-wrapper"><?php echo JHTML::_('image.site', 'PostHeaderIcon.png', null, null, null, JText::_("PostHeaderIcon"), array('width' => '29', 'height' => '29')); ?> <span class="PostHeader">
<span class="componentheading<?php echo $this->params->get('pageclass_sfx')?>"><?php echo $this->escape($this->params->get('page_title')); ?></span>
</span>
</h2>

<?php endif; ?>
<div class="PostContent">

<table cellpadding="5" cellspacing="0" border="0" width="100%">
<tr>
	<td>
		<label for="username">
			<?php echo JText::_( 'User Name' ); ?>:
		</label>
	</td>
	<td>
		<span id="username"><?php echo $this->user->get('username');?></span>
	</td>
</tr>
<tr>
	<td width="120">
		<label for="name">
			<?php echo JText::_( 'Your Name' ); ?>:
		</label>
	</td>
	<td>
		<input class="inputbox" type="text" id="name" name="name" value="<?php echo $this->user->get('name');?>" size="40" />
	</td>
</tr>
<tr>
	<td>
		<label for="email">
			<?php echo JText::_( 'email' ); ?>:
		</label>
	</td>
	<td>
		<input class="inputbox" type="text" id="email" name="email" value="<?php echo $this->user->get('email');?>" size="40" />
	</td>
</tr>
<?php if($this->user->get('password')) : ?>
<tr>
	<td>
		<label for="password">
			<?php echo JText::_( 'Password' ); ?>:
		</label>
	</td>
	<td>
		<input class="inputbox" type="password" id="password" name="password" value="" size="40" />
	</td>
</tr>
<tr>
	<td>
		<label for="password2">
			<?php echo JText::_( 'Verify Password' ); ?>:
		</label>
	</td>
	<td>
		<input class="inputbox" type="password" id="password2" name="password2" size="40" />
	</td>
</tr>
<?php endif; ?>
</table>
<?php if(isset($this->params)) :  echo $this->params->render( 'params' ); endif; ?>
	<button class="button" type="submit" onclick="submitbutton( this.form );return false;"><?php echo JText::_('Save'); ?></button>

	<input type="hidden" name="username" value="<?php echo $this->user->get('username');?>" />
	<input type="hidden" name="id" value="<?php echo $this->user->get('id');?>" />
	<input type="hidden" name="gid" value="<?php echo $this->user->get('gid');?>" />
	<input type="hidden" name="option" value="com_user" />
	<input type="hidden" name="task" value="save" />
	<?php echo JHTML::_( 'form.token' ); ?>

</div>
<div class="cleared"></div>


</div>

    </div>
</div>

</form>
