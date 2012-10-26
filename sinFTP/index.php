<?php
	@session_start();	
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
</head>
	<body>
		<h1>Selecciona un archivo excell</h1>
		<form action="upload.php" method="post" enctype="multipart/form-data" name="frm_upl"> 
			<input type="file" name="file_excel" id="file_excel">
			<input type="submit" name="sb_excel" id="sb_excel" value="Subir Archivo">
		</form>
	</body>
</html>
	