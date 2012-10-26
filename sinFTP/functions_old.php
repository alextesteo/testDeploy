<?php
@session_start();
include 'comu_db.inc';
include 'Excel/reader.php';
include 'Excel/excelwriter.inc.php';
$_SESSION['res'] = array();
$_SESSION['directory'] = "";

//LEE EL .xls Y DEVUELVE EL OBJETO EXCEL_READER
function returnSheet($excell) {
    $data = new Spreadsheet_Excel_Reader();
    $data->setOutputEncoding('CP1251');
    $data->read($excell);
    return $data;
}

//RECUPERO EL ID DE LA GAMA
function returnGama($marca, $modelo, $gama){

    $query = "SELECT g.id from gama g inner join modelo m
				on g.idModelo=m.id inner join marca ma on m.idMarca=ma.id
				Where g.nombre='" . $gama . "' AND m.nombre='" . $modelo . "' AND ma.nombre='" . $marca . "';";

    $res = mysql_query($query);

    if ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
        return $row['id'];
    } else {
        return $marca . ' ' . $modelo . ' ' . $gama;
    }
}

//RETORNA TODOS LOS REGISTROS DE GROPER
function returnGrOper() {

    $query = "SELECT * FROM groper";

    $res = mysql_query($query);
    $arr = array();

    while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
        $arr[] = array('id' => $row['id'], 'nombre' => $row['nombre']);
    }
    return $arr;
}

//RETORNA TODOS LOS REGISTROS DE OPERACION
function returnOperaciones() {

    $query = "SELECT id,nombre FROM operacion;";
    $res = mysql_query($query);
    $arr = array();
    while ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
        $arr[] = array('id' => $row['id'], 'nombre' => $row['nombre']);
    }
    return $arr;
}

//PARSEA EL EXCELL SEPARANDO LOS COCHES QUE TIENEN ID Y LOS QUE NO
function parseExcell($nombre) {
    
    //$maxId = getMaxNTecnicaId();
    $tokens = explode('.', $nombre);
    $excell = returnSheet($_SESSION['directory'] . '/' . $nombre);
    $fallo = false;
    $outExcell = new ExcelWriter($_SESSION['directory'] . '/' . $tokens[0] . '_res.' . $tokens[1]); //CREO EL .XLS DE SALIDA
    $outExcell->writeRow();
    $outExcell->writeCol('<b>ID</b>');
	$outExcell->writeCol('<b>MODELO</b>');
	$outExcell->writeCol('<b>IDTECDOC</b>');


    //$idGrOpers = returnGrOper();
    //$idOperaciones = returnOperaciones();

    /*$_SESSION['num_excell'] = count($excell->sheets[0]['cells']);
    $_SESSION['num_inserts'] = 0;
    $_SESSION['num_inserts_err'] = 0;
    $_SESSION['num_errs'] = 0;*/
    for ($i = 1; $i <= count($excell->sheets[0]['cells']); $i++) {
        /*$groper = "";
        $operacion = "";
        $gama = "";
        $marca = $excell->sheets[0]['cells'][$i][1]; //marca actual
        $modelo = $excell->sheets[0]['cells'][$i][2]; //modelo actual
        $gama = $excell->sheets[0]['cells'][$i][3]; //gama actual
		*/

        //$gamaRes = returnGama($marca, $modelo, $gama);


        /*for ($z = 0; $z < count($idGrOpers); $z++) {//RECUPERO EL ID DE GROPER
            if (utf8_encode($excell->sheets[0]['cells'][$i][4]) == $idGrOpers[$z]['nombre']) {
                $groper = $idGrOpers[$z]['id'];
                break;
            }
        }


        for ($z = 0; $z < count($idOperaciones); $z++) {//RECUPERO EL ID DE LA OPERACION
            if ($excell->sheets[0]['cells'][$i][5] == $idOperaciones[$z]['nombre']) {
                $operacion = $idOperaciones[$z]['id'];
                break;
            }
        }*/
        
        $id=$excell->sheets[0]['cells'][$i][1];
		$nombre=$excell->sheets[0]['cells'][$i][2];
		$idTecDoc=$excell->sheets[0]['cells'][$i][3];
		
        
        
        $registro=checkGama($id,$nombre,$idTecDoc);
        
		
        if (!$registro) {
            if (!$fallo) {
                $failExcell = new ExcelWriter($_SESSION['directory'] . '/' . $tokens[0] . '_fail.' . $tokens[1]); //CREO EL .XLS DE ERRORES
                $failExcell->writeRow();
                $failExcell->writeCol('<b>ID</b>');
                $failExcell->writeCol('<b>MODELO</b>');
                $failExcell->writeCol('<b>GAMA</b>');
                $fallo = true;
            }
            $failExcell->writeRow();
            $failExcell->writeCol($id);
            $failExcell->writeCol($nombre);
            $failExcell->writeCol($idTecDoc);
        } else {            
            $outExcell->writeRow();
            $outExcell->writeCol($id);
			$outExcell->writeCol($nombre);
			$outExcell->writeCol($idTecDoc);
			$outExcell->writeCol($registro);
        }
    }

    $outExcell->close();
    if (file_exists($tokens[0] . '_fail.' . $tokens[1])) {
        $failExcell->close();
    }
	
}

function checkGama($id,$nombre,$idTecDoc){
	$link_id = db_connexio();
	//$query='SELECT * FROM gama WHERE id='.$id.';';
	$query='SELECT * FROM modelo WHERE id='.$id.';';
	$res=mysql_query($query);
	if(mysql_num_rows($res)!=FALSE){
		$query='UPDATE modelo 
				SET nombre="'.$nombre.'", idTecDoc='.$idTecDoc.'
				WHERE id='.$id.';';

		$res=mysql_query($query);
		
		if(mysql_affected_rows()!=-1){
			return 'ok';
		}else{
			return 'consulta fallida';
		}
	}else{
		return FALSE;
	}
}


function creaDirectorio($file) {
    $dir = date('Ymd') . '_';
    $i = 1;
    $ok = false;
    $str = "";
    while (!$ok) {
        $str = str_pad($i, 3, '0', STR_PAD_LEFT); //añado los zeros necesarios por la izquierda
        if (!file_exists($dir . $str)) {
            mkdir($dir . $str, 0777);
            $_SESSION['directory'] = $dir . $str;
            $ok = true;
        }
        $i++;
    }
    return $_SESSION['directory'] . '/' . $file;
}

//INSERTA CADA LINEA DEL EXCEL EN LA TABLA NTECNICA
function insertNTecnica() {

    $maxId = getMaxNTecnicaId();
    $tecnica = false;
    $gama = false;

    $queryNTecnica = "";
    $queryGama = "";

    for ($i = 0; $i < count($_SESSION['res']); $i++) {
        $gama = $tecnica = false;
        $queryNTecnica = "INSERT INTO NTecnica (id,groper,oper,descripcion,seguimiento,solucion,importancia) VALUES (" . $maxId . "," . $_SESSION['res'][$i]['groper'] . "," . $_SESSION['res'][$i]['operacion'] . ",'" . str_replace(';', ',', $_SESSION['res'][$i]['descripcion']) . "','" . str_replace(';', ',', $_SESSION['res'][$i]['seguimiento']) . "','" . str_replace(';', ',', $_SESSION['res'][$i]['solucion']) . "'," . $_SESSION['res'][$i]['importancia'] . ");";

        mysql_query($queryNTecnica);

        if (mysql_affected_rows() > 0) {
            $tecnica = true;
        }


        $queryGama = insertNTGama($maxId, $_SESSION['res'][$i]['gama']);
        mysql_query($queryGama);

        if (mysql_affected_rows() > 0) {
            $gama = true;
        }

        if ($tecnica && $gama) {
            $_SESSION['num_inserts']++;
        } else {
            $_SESSION['num_inserts_err']++;
            $fp = fopen($_SESSION['directory'] . '/' . 'sql_err.txt', 'a');
            fwrite($fp, $queryNTecnica . PHP_EOL);
            fwrite($fp, $queryGama . PHP_EOL);
            fwrite($fp, '///////////////////' . PHP_EOL);
            fclose($fp);
        }
        $maxId++;
    }
    mysql_close();
}

//BUSCA EL ID MAXIMO EN NTECNICA Y LE SUMA 1
function getMaxNTecnicaId() {
    $query = "SELECT max(id) as indice from NTecnica;";
    $res = mysql_query($query);
    if ($row = mysql_fetch_array($res, MYSQL_ASSOC)) {
        $id = $row['indice'];
    }
    return $id + 1;
}

function insertNTGama($idNTec, $gama) {
    return "INSERT INTO NTGama (idNTec,idGama) VALUES (" . $idNTec . "," . $gama . ");";
}