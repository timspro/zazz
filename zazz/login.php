<?php
include_once dirname(__FILE__) . '/includes/standard/initialize.php';

$auth = Authenticate::get();

$valid = '/zazz/index.php';

if ($auth->loggedIn()) {
	header('Location: ' . $valid);
}

function createUser($auth) {
	require_once dirname(__FILE__) . '/includes/standard/classes/auto/_Project.php';
	require_once dirname(__FILE__) . '/includes/standard/classes/auto/_Page.php';
	require_once dirname(__FILE__) . '/includes/standard/classes/auto/_User.php';
	require_once dirname(__FILE__) . '/includes/standard/classes/auto/_Code.php';
	require_once dirname(__FILE__) . '/includes/standard/classes/auto/_Layout.php';

	$firstProject = 'project-1';
	$firstPage = 'index.php';
	$user_id = $auth->getUser('user_id');
	$id = _Project::get()->create(array('project' => $firstProject, 'user_id' => $user_id));
	$page_id = _Page::get()->create(array('page' => $firstPage, 'project_id' => $id));
	_User::get()->update(array('active_project' => $id), array('user_id' => $user_id));
	_Code::get()->create(array('zazz_id' => 'element-0', 'page_id' => $page_id, 'type' => 'css',
		'code' => "#element-0 {\n\n}", 'zazz_order' => '0'));
	_Code::get()->create(array('zazz_id' => 'row-0', 'page_id' => $page_id, 'type' => 'css',
		'code' => "#row-0 {\n\n}", 'zazz_order' => '0'));
	_Code::get()->create(array('zazz_id' => 'row-group-0', 'page_id' => $page_id, 'type' => 'css',
		'code' => "#row-group-0 {\n\n}", 'zazz_order' => '0'));
	ob_start();
	?>
	<div id="content" _zazz-order="0" tabindex="1" class="-zazz-content" _zazz-id="content" 
			 _zazz-rid='1' _zazz-gid='1' _zazz-eid='1'><div 
			class="-zazz-outline-right -zazz-outline"> </div><div 
			class="-zazz-outline-top -zazz-outline"> </div><div 
			class="-zazz-outline-bottom -zazz-outline"> </div><div 
			class="-zazz-outline-left -zazz-outline"> </div><div 
			id="row-group-0" _zazz-order="0" tabindex="1" class="-zazz-row-group" _zazz-id="row-group-0"><div 
				id="row-0" _zazz-order="0" tabindex="1" class="-zazz-row" _zazz-id="row-0"><div 
					id="element-0" _zazz-order="0" tabindex="1" class="-zazz-element" _zazz-id="element-0"></div
				></div
			></div
		></div>
	<?php
	$layout = ob_get_flush();
	_Layout::get()->create(array('page_id' => $page_id, 'layout' => $layout));
}

$error = '';
$login_error = '';
if (isset($_REQUEST['login_email']) && isset($_REQUEST['login_password'])) {
	$username = $_REQUEST['login_email'];
	$password = $_REQUEST['login_password'];
	if (isset($_REQUEST['create'])) {
		if ($_REQUEST['global'] === '72ns-3k3j#!j9EJ03jJ#0s)(FDajw') {
			$error = "The global password is incorrect.";
		} else if (strlen($username) < 7) {
			$error = "Your username is too short.";
		} else if (strlen($password) < 6) {
			$error = "Your password must be at least 6 characters.";
		} else if ($password !== $_REQUEST['password2']) {
			$error = "Your password did not match what you retyped.";
		} else {
			$success = $auth->create(array('username' => $username, 'password' => $password));
			if ($success && $auth->login($username, $password)) {
				createUser($auth);
				header('Location: ' . $valid);
			} else {
				$error = $auth->getError();
			}
		}
	} else if ($auth->login($username, $password)) {
		header('Location: ' . $valid);
	} else {
		$login_error = $auth->getError();
	}
}
?>
<!DOCTYPE html>
<html lang="en">
	<?php require_once dirname(__FILE__) . '/includes/custom/header.php'; ?>
	<body style="background-color: #CFCFCF;">
		<form id='-zazz-login' method="post" class="-zazz-modal" style="display: block;">
			<div class="-zazz-modal-header">Account Login</div>
			<div class="-zazz-modal-body">
				<p><?= $login_error ?></p>
				<table>
					<tr>
						<td>Email Address:</td>
						<td><input name="login_email" type="text" 
											 value="<?= ifisset($_REQUEST['login_email']) ?>"/></td>
					</tr>
					<tr>
						<td>Password:</td>
						<td><input name="login_password" type="password" /></td>
					</tr>
				</table>
			</div>
			<div class="-zazz-modal-footer">
				<input type="submit" class="-zazz-modal-submit" name="login" value="Login" />
				<input id="create-account" type="button" name="create" value="Create" />
			</div>
		</form>
		<form id='-zazz-account-create' method="post" class="-zazz-modal" 
					<?= (isset($_REQUEST['create'])
							? 'style="display:block"' : '') ?>>
			<div class="-zazz-modal-header">Account Creation</div>
			<div class="-zazz-modal-body">
				<p><?= $error ?></p>
				<table>
					<tr>
						<td>Email Address:</td>
						<td><input name="login_email" type="text" 
											 value="<?= ifisset($_REQUEST['login_email']) ?>"/></td>
					</tr>
					<tr>
						<td>Password:</td>
						<td><input name="login_password" type="password" /></td>
					</tr>			
					<tr>
						<td>Retype Password:</td>
						<td><input name="password2" type="password" /></td>
					</tr>
					<tr>
						<td>Global Password:</td>
						<td><input name="global" type="password" /></td>
					</tr>
				</table>
			</div>
			<div class="-zazz-modal-footer">
				<input type="button" class="-zazz-modal-close" name="login" value="Cancel" />
				<input type="submit" class="-zazz-modal-submit" name="create" value="Create" />
			</div>
		</form>
	</body>
	<script src="/zazz/js/jquery-1.10.2.js" type="text/javascript"></script>
	<script>
		$(document).ready(function() {
			$.fn.center = function() {
				this.css("position", "absolute");
				this.css("top", Math.max(0, (($(window).height() - $(this).outerHeight()) / 2) +
					$(window).scrollTop()) + "px");
				this.css("left", Math.max(0, (($(window).width() - $(this).outerWidth()) / 2) +
					$(window).scrollLeft()) + "px");
				return this;
			};
			$('#-zazz-login').center();
			$('#-zazz-account-create').center()
			$('#create-account').click(function() {
				$('#-zazz-account-create').show();
			});
			$('.-zazz-modal-close').click(function() {
				$(this).closest('.-zazz-modal').hide();
			});
		});
	</script>
</html>
