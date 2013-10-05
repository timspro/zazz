
<?php 
$_PDO = new PDO("mysql:host=localhost;dbname=zazz", "root", "");
$_PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
?>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
	<title>Zazz</title>
	<link rel="stylesheet" href="css/style.css" type="text/css" media="all" />
</head>
<body>
<div id="content" _zazz-order="1" class="-zazz-content" _zazz-id="content" _zazz-rid="1" _zazz-gid="1" _zazz-eid="2"><div id="row-group-0" _zazz-order="1" class="-zazz-row-group" _zazz-id="row-group-0"><div id="row-0" _zazz-order="1" class="-zazz-row" _zazz-id="row-0"><div id="element-0" _zazz-order="4" tabindex="1" class="-zazz-element" _zazz-id="element-0" style="width: 587px;">
<?php
$var = 'tim.spr@gmail.com';
?>

<?php
ob_start();
?>
SELECT * FROM user WHERE username = :var AND first_name = ':Tim' AND last_name = "'S\"pro:\"\'wl"
<?php
$_PDO_CODE = ob_get_clean();
$_PDO_QUERY = $_PDO->prepare($_PDO_CODE);
$_PDO_QUERY->bindValue(':var', $var);
$_PDO_QUERY->execute();
$_ROWS = $_PDO_QUERY->fetchAll(PDO::FETCH_ASSOC);
?>

<?php
echo nl2br(print_r($_ROWS, true));
?>
</div><div class="-zazz-element" _zazz-id="element-1" id="element-1" _zazz-order="1" tabindex="1" style="width: 676px;">     </div></div></div></div>
</body>
	<script src="js/jquery-1.10.2.js" type="text/javascript"></script>
	<script src="js/functions.js" type="text/javascript"></script>
</html>
