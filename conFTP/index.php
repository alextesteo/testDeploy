<?php
	@session_start();	
?>

<html>
	<body>
		<h1>Selecciona un archivo excell</h1>
		<form action="upload.php" method="post" enctype="multipart/form-data" name="frm_upl"> 
			<input type="file" name="file_excel" id="file_excel">
			<input type="submit" name="sb_excel" id="sb_excel" value="Subir Archivo">
		</form>
	</body>
</html>
	