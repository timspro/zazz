<html>
	<body>
		<h3> Test Environment Variables: </h3>
		<p> DOCUMENT_ROOT: <?= $_SERVER["DOCUMENT_ROOT"]; ?> </p>
		<p> HTTP_HOST: <?= $_SERVER['HTTP_HOST'] ?> </p>
		<p> SERVER_NAME: <?= $_SERVER['SERVER_NAME'] ?> </p>
		<p> SERVER_PORT: <?= $_SERVER['SERVER_PORT'] ?> </p>
		<p> COOKIE: <?= print_r($_COOKIE, true) ?> </p>
		<p> BLOWFISH: <?= CRYPT_BLOWFISH ?> </p>
		<p> FILENAME: <?= $_SERVER['SCRIPT_FILENAME'] ?> </p>
		<p> WEB PATH: <?= $_SERVER['PHP_SELF'] ?> </p>
		<p> RNG: 
			<?php
			openssl_random_pseudo_bytes(16, $strong);
			echo $strong
			?> 
		</p>
		<p> HTTPS: <?= $_SERVER['HTTPS'] ?> </p>
		<p> BLOWFISH TIME: 
			<?php
			include_once 'class.user.php';
			$start = time();
			for ($i = 0; $i < 100; $i++) {
				User::blowfish("atestofblowfish$i");
			}
			$end = time();
			echo ($end - $start) . ' seconds';
			?>
		</p>
	</body>
</html>