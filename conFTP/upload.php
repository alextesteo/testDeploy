<?php
    @session_start();
    include_once 'functions.php';
	
	//$uploadfile=creaDirectorio();
    $uploadfile=creaDirectorio(basename($_FILES['file_excel']['name']));

    $error = $_FILES['file_excel']['error'];
    $subido = false;
    if(isset($_POST['sb_excel']) && $error==UPLOAD_ERR_OK){
    	$subido = copy($_FILES['file_excel']['tmp_name'], $uploadfile.'/'.basename($_FILES['file_excel']['name']));
    }
	
    if($subido) {
            echo "El archivo se ha subido con exito";
    } else {
            echo "Se ha producido un error: ".$error;
    }
   
   

    //$excell=parseExcell(basename($_FILES['file_excel']['name']));

    //insertNTecnica();



	
