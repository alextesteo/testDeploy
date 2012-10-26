<?php
    @session_start();
    include 'comu_db.inc';
    include 'Excel/reader.php';
    include 'Excel/excelwriter.inc.php';
    $_SESSION['res']=array();
    $_SESSION['directory']="";

    //LEE EL .xls Y DEVUELVE EL OBJETO EXCEL_READER
    function returnSheet($excell){
            $data= new Spreadsheet_Excel_Reader();
            $data->setOutputEncoding('CP1251');
            $data->read($excell);
            return $data;
    }

    //RECUPERO EL ID DE LA GAMA
    function returnGama($marca,$modelo,$gama){

            $query="SELECT g.id from gama g inner join modelo m
                            on g.idModelo=m.id inner join marca ma on m.idMarca=ma.id
                            Where g.nombre='".$gama."' AND m.nombre='".$modelo."' AND ma.nombre='".$marca."';";

            $res=mysql_query($query);

            if($row=mysql_fetch_array($res,MYSQL_ASSOC)){
                    return $row['id'];
            }else{
                    return $marca.' '.$modelo.' '.$gama;
            }
    }


    //RETORNA TODOS LOS REGISTROS DE GROPER
    function returnGrOper(){

            $query="SELECT * FROM groper";

            $res=mysql_query($query);
            $arr=array();

            while($row=mysql_fetch_array($res,MYSQL_ASSOC)){
                    $arr[]=array('id'=>$row['id'],'nombre'=>$row['nombre']);
            }
            return $arr;
    }


    //RETORNA TODOS LOS REGISTROS DE OPERACION
    function returnOperaciones(){

            $query="SELECT id,nombre FROM operacion";
            $res=mysql_query($query);
            $arr=array();
            while($row=mysql_fetch_array($res,MYSQL_ASSOC)){
                    $arr[]=array('id'=>$row['id'],'nombre'=>$row['nombre']);
            }
            return $arr;
    }

    //PARSEA EL EXCELL SEPARANDO LOS COCHES QUE TIENEN ID Y LOS QUE NO
    function parseExcell($nombre){
            $link_id=db_connexio();
            $tokens=explode('.',$nombre);
            $excell=returnSheet($_SESSION['directory'].'/'.$nombre);
            $fallo=false;
            $outExcell=new ExcelWriter($_SESSION['directory'].'/'.$tokens[0].'_res.'.$tokens[1]);//CREO EL .XLS DE SALIDA	
            $outExcell->writeRow();
            $outExcell->writeCol('<b>GAMA</b>');
            $outExcell->writeCol('<b>GR OPERS</b>');
            $outExcell->writeCol('<b>OPERACION</b>');
            $outExcell->writeCol('<b>DESCRIPCIÓN</b>');
            $outExcell->writeCol('<b>SEGUIMIENTO</b>');
            $outExcell->writeCol('<b>SOLUCION</b>');
            $outExcell->writeCol('<b>IMPORTANCIA</b>');

            $idGamas=array();
            $idGrOpers=returnGrOper();
            $idOperaciones=returnOperaciones();

            for($i=1;$i<=count($excell->sheets[0]['cells']);$i++){
                    $groper="";
                    $operacion="";
                    $gama="";
                    $marca=$excell->sheets[0]['cells'][$i][1];//marca actual
                    $modelo=$excell->sheets[0]['cells'][$i][2];//modelo actual
                    $gama=$excell->sheets[0]['cells'][$i][3];//gama actual


                    $gamaRes=returnGama($marca,$modelo,$gama);

                    for($z=0;$z<count($idGrOpers);$z++){//RECUPERO EL ID DE GROPER
                            if($excell->sheets[0]['cells'][$i][4]==$idGrOpers[$z]['nombre']){
                                    $groper=$idGrOpers[$z]['id'];
                                    break;
                            }
                    }


                    for($z=0;$z<count($idOperaciones);$z++){//RECUPERO EL ID DE LA OPERACION
                            if($excell->sheets[0]['cells'][$i][5]==$idOperaciones[$z]['nombre']){
                                    $operacion=$idOperaciones[$z]['id'];
                                    break;
                            }
                    }

                    if(!is_numeric($gamaRes)){
                            if(!$fallo){
                                    $failExcell=new ExcelWriter($_SESSION['directory'].'/'.$tokens[0].'_fail.'.$tokens[1]);//CREO EL .XLS DE ERRORES
                                    $failExcell->writeRow();
                                    $failExcell->writeCol('<b>MARCA</b>');
                                    $failExcell->writeCol('<b>MODELO</b>');
                                    $failExcell->writeCol('<b>GAMA</b>');
                                    $failExcell->writeCol('<b>GR OPERS</b>');
                                    $failExcell->writeCol('<b>OPERACION</b>');
                                    $failExcell->writeCol('<b>DESCRIPCIÓN</b>');
                                    $failExcell->writeCol('<b>SEGUIMIENTO</b>');
                                    $failExcell->writeCol('<b>SOLUCION</b>');
                                    $failExcell->writeCol('<b>IMPORTANCIA</b>');
                                    $fallo=true;
                            }
                            $failExcell->writeRow();
                            $failExcell->writeCol($marca);
                            $failExcell->writeCol($modelo);
                            $failExcell->writeCol($gama);
                            $failExcell->writeCol($groper);
                            $failExcell->writeCol($operacion);
                            $failExcell->writeCol($excell->sheets[0]['cells'][$i][6]);
                            $failExcell->writeCol(@$excell->sheets[0]['cells'][$i][7]);
                            $failExcell->writeCol($excell->sheets[0]['cells'][$i][8]);
                            $failExcell->writeCol($excell->sheets[0]['cells'][$i][9]);
                    }else{
                            $_SESSION['res'][]=array('gama'=>$gamaRes,
                                                    'groper'=>$groper,
                                                    'operacion'=>$operacion,
                                                    'descripcion'=>str_replace("'", '´',$excell->sheets[0]['cells'][$i][6]),
                                                    'seguimiento'=>str_replace("'", '´',$excell->sheets[0]['cells'][$i][7]),
                                                    'solucion'=>str_replace("'", '´',$excell->sheets[0]['cells'][$i][8]),
                                                    'importancia'=>$excell->sheets[0]['cells'][$i][9]);
                            $outExcell->writeRow();
                            $outExcell->writeCol($gamaRes);
                            $outExcell->writeCol($groper);
                            $outExcell->writeCol($operacion);
                            $outExcell->writeCol($excell->sheets[0]['cells'][$i][6]);
                            $outExcell->writeCol(@$excell->sheets[0]['cells'][$i][7]);
                            $outExcell->writeCol($excell->sheets[0]['cells'][$i][8]);
                            $outExcell->writeCol($excell->sheets[0]['cells'][$i][9]);
                    }
            }

            $outExcell->close();
            if(file_exists($tokens[0].'_fail.'.$tokens[1])){
                    $failExcell->close();	
            }

    }
	
/*	function creaDirectorio(){
		$cid = ftp_connect("ftp.ad-service.es");	
		
		$resultado = ftp_login($cid, "adservice11","servdiag2411");
		
		if ((!$cid) || (!$resultado)) {
			echo "Fallo en la conexión"; die;
		}
		
		ftp_pasv ($cid, true);
		
		ftp_chdir($cid, "httpdocs/exctomysql");	
		
		$dirlist=ftp_nlist($cid, '');//me devuelve un array con los nombres de todos los archivos y carpetas que hay en el directorio de la aplicacion
		
		$dir=date('Ymd').'_';
		$cont=1;
		$ok=false;
		
		while(!$ok){
			$str=str_pad($cont, 3,'0', STR_PAD_LEFT);//añado ceros por la izquierda
			if(!in_array($dir.$str,$dirlist)){
				$ok=true;
				$_SESSION['directory']=$dir.$str;
				break;
			}else{
				$cont++;
			}
		}
		
		ftp_mkdir($cid,$_SESSION['directory']);
		
		$file = $_SESSION['directory'].'/'.$_FILES['file_excel']['name'];
		$tmp_file = $_FILES['file_excel']['tmp_name'];
		
		if (ftp_put($cid, $tmp_file, $file, FTP_BINARY)) {
			echo "ARCHIVO SUBIDO";
		} 
		ftp_close($cid);

        return $_SESSION['directory'];
	}*/

    function creaDirectorio(){
        $dir=date('Ymd').'_';
        $i=1;
        $ok=false;
        $str="";
        while(!$ok){
            $str=str_pad($i, 3,'0', STR_PAD_LEFT);//a�ado los ceros necesarios por la izquierda
            if(!file_exists($dir.$str)){
                    mkdir($dir.$str);
					chown($dir.$str,'adservice11');
                    //chmod($dir.$str,0777);
                    $_SESSION['directory']=$dir.$str;				
                    $ok=true;
            }
            $i++;
        }

        return $_SESSION['directory'];
    }
   
   	

    //INSERTA CADA LINEA DEL EXCEL EN LA TABLA NTECNICA
    function insertNTecnica(){
        set_time_limit(300);
        $maxId=getMaxNTecnicaId();

        $queryNTecnica="";
        $queryGama="";

        for($i=0;$i<count($_SESSION['res']);$i++){
                $queryNTecnica="INSERT INTO ntecnica (id,groper,oper,descripcion,seguimiento,solucion,importancia) VALUES (".$maxId.",".$_SESSION['res'][$i]['groper'].",".$_SESSION['res'][$i]['operacion'].",'".str_replace(';', ',', $_SESSION['res'][$i]['descripcion'])."','".str_replace(';', ',', $_SESSION['res'][$i]['seguimiento'])."','".str_replace(';', ',', $_SESSION['res'][$i]['solucion'])."',".$_SESSION['res'][$i]['importancia'].");";
                mysql_query($queryNTecnica);

                $queryGama=insertNTGama($maxId,$_SESSION['res'][$i]['gama']);
                mysql_query($queryGama);
                $maxId++;
        }

        mysql_close();
    }

    //BUSCA EL ID MAXIMO EN NTECNICA Y LE SUMA 1
    function getMaxNTecnicaId(){		
        $query="SELECT max(id) as indice from ntecnica;";
        $res=mysql_query($query);
        if($row=mysql_fetch_array($res,MYSQL_ASSOC)){
                $id=$row['indice'];
        }		
        return $id+1;
    }

    function insertNTGama($idNTec,$gama){
            return "INSERT INTO ntgama (idNTec,idGama) VALUES (".$idNTec.",".$gama.");";		
    }