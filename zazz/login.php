<?php
include_once dirname(__FILE__) . '/includes/standard/initialize.php';

$auth = Authenticate::get();

$valid = '/zazz/index.php';

if ($auth->loggedIn()) {
	header('Location: ' . $valid);
}

function createFirstProject($user_id) {
	require_once dirname(__FILE__) . '/includes/custom/functions.php';

	function generateRandomString() {
		$strong = false;
		return substr(str_replace('/', '_', str_replace('+', '$', base64_encode(openssl_random_pseudo_bytes(16, $strong)))), 0, 16);
	}

	$count = 0;
	$good = false;
	while (!$good && $count < 5) {
		//try {
		$count++;
		$dbname = generateRandomString();
		$dbusername = generateRandomString();
		$dbpassword = generateRandomString();

		$PDO = Database::get()->PDO();
		$query = $PDO->prepare("CREATE USER '$dbusername'@'localhost' IDENTIFIED BY '$dbpassword';
CREATE DATABASE $dbname;
GRANT ALL PRIVILEGES ON $dbname.* TO '$dbusername'@'localhost';");
		$query->execute();
		$query->closeCursor();
		//$query->fetchAll();
		_User::get()->update(array('dbname' => $dbname, 'dbusername' => $dbusername,
				'dbpassword' => $dbpassword), array('user_id' => $user_id));
		$good = true;
		//} catch (PDOException $e) {
		//	Logger::get()->log($e->getMessage());
		//}
	}
	if ($count > 5) {
		exit();
	}

	$firstProject = 'project';
	createProject($firstProject, $user_id);
}

$error = '';
$login_error = '';
$password_error = '';
$global_password = /*!_!_!PASSWORD!_!_!*/''/*!_!_!PASSWORD!_!_!*/;
if (isset($_REQUEST['login_email']) && isset($_REQUEST['login_password'])) {
	$username = $_REQUEST['login_email'];
	$password = $_REQUEST['login_password'];
	if (isset($_REQUEST['create'])) {
		if ($_REQUEST['global'] !== $global_password) {
			$error = "The global password is incorrect.";
		} else if (strlen($username) < 6) {
			$error = "Your username is too short.";
		} else if (strlen($password) < 10) {
			$error = "Your password must be at least 10 characters.";
		} else if (strtolower($password) === $password) {
			$error = "Your password must contain at least 1 uppercase letter.";
		} else if (!preg_match('#[0-9]#', $password)) {
			$error = "Your password must contain at least 1 number.";
		} else if ($password !== $_REQUEST['password2']) {
			$error = "Your password did not match what you retyped.";
		} else {
			$success = $auth->create(array('username' => $username, 'password' => $password));
			if ($success && $auth->login($username, $password)) {
				createFirstProject(Authenticate::get()->getUser('user_id'));
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
if (isset($_REQUEST['change_email']) && isset($_REQUEST['change_new_password']) &&
		isset($_REQUEST['change_old_password']) && isset($_REQUEST['change_new_password2'])) {
	$force = false;
	if ($global_password === $_REQUEST['change_old_password']) {
		$force = true;
	}
	if ($auth->login($_REQUEST['change_email'], $_REQUEST['change_old_password'], $force)) {
		$password = $_REQUEST['change_new_password'];
		if (strlen($password) < 10) {
			$password_error = "Your password must be at least 10 characters.";
		} else if (strtolower($password) === $password) {
			$password_error = "Your password must contain at least 1 uppercase letter.";
		} else if (!preg_match('#[0-9]#', $password)) {
			$password_error = "Your password must contain at least 1 number.";
		} else if ($password !== $_REQUEST['change_new_password2']) {
			$password_error = "Your password did not match what you retyped.";
		}
		if (empty($password_error)) {
			if ($auth->changePassword($password)) {
				$password_error = 'Password changed successfully.';
			} else {
				$password_error = "There was an error changing the password.";
			}
		}
	} else {
		$password_error = $auth->getError();
	}
	$auth->logout();
}
?>
<!DOCTYPE html>
<html lang="en">
<?php require_once dirname(__FILE__) . '/includes/custom/header.php'; ?>
	<body style="background-color: #CFCFCF;">
		<form id='-zazz-login' method="post" class="-zazz-modal">
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
				<input id="change-password" type="button" name="password" value="Password" />
			</div>
		</form>
		<form id='-zazz-change-password' method="post" class="-zazz-modal"
<?=
(isset($_REQUEST['password']) ? 'style="display:block"' : '')
?>>
			<div class="-zazz-modal-header">Change Password</div>
			<div class="-zazz-modal-body">
				<p><?= $password_error ?></p>
				<table>
					<tr>
						<td>Email Address:</td>
						<td><input name="change_email" type="text" 
											 value="<?= ifisset($_REQUEST['login_email']) ?>"/></td>
					</tr>
					<tr>
						<td>Old (or Global) Password:</td>
						<td><input name="change_old_password" type="password" /></td>
					</tr>
					<tr>
						<td>New Password:</td>
						<td><input name="change_new_password" type="password" /></td>
					</tr>
					<tr>
						<td>Retype New Password:</td>
						<td><input name="change_new_password2" type="password" /></td>
					</tr>
				</table>
			</div>
			<div class="-zazz-modal-footer">
				<input type="button" class="-zazz-modal-close" name="login" value="Cancel" />
				<input type="submit" class="-zazz-modal-submit" name="password" value="Change" />
			</div>
		</form>
		<form id='-zazz-account-create' method="post" class="-zazz-modal" 
<?=
(isset($_REQUEST['create']) ? 'style="display:block"' : '')
?>>
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
			$('#-zazz-login').center().show();
			$('#-zazz-account-create').center();
			$('#-zazz-change-password').center();
			$('#create-account').click(function() {
				$('#-zazz-account-create').show();
			});
			$('.-zazz-modal-close').click(function() {
				$(this).closest('.-zazz-modal').hide();
			});
			$('#change-password').click(function() {
				$('#-zazz-change-password').show();
			});
		});
	</script>
</html>
