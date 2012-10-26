<?php
	@session_start();
	include_once 'functions.php';
	ini_set('safe_mode','0');
	
	//AUMENTO EL TIEMPO DE EJECUCION DEL SERVIDOR PARA QUE DE TIEMPO A QUE ESPERE 1 HORA
	//set_time_limit(3700000);
	
	set_time_limit(0);
	
	$uploadfile=creaDirectorio(basename($_FILES['file_excel']['name']));
	$error = $_FILES['file_excel']['error'];
	$subido = false;
    $filename=basename($_FILES['file_excel']['name']);
	if(isset($_POST['sb_excel']) && $error==UPLOAD_ERR_OK) {
		$subido = copy($_FILES['file_excel']['tmp_name'], $uploadfile);
	}
	
	if($subido) {
		echo "El archivo se ha subido con exito <br>";
	} else {
		echo "Se ha producido un error: ".$error.'<br>';
	}
	
	//echo date('h:i:s');

	//retrasa la ejecucion 1h
	//sleep(3600);
	
	$excell=parseExcell($filename);
	
	//insertNTecnica();
