<?php
	$dbhost='grupeina.com';
	$dbnom_usuari='servicenton';
	$dbclau='trnppmazei12';
	$db_defecte='ntec_es';
	
	$MYSQL_ERRNO='';
	$MYSQL_ERROR='';
	
	function db_connexio(){
		global $dbhost,$dbnom_usuari,$dbclau,$db_defecte;
		global $MYSQL_ERRNO,$MYSQL_ERROR;
		$link_id=mysql_connect($dbhost,$dbnom_usuari,$dbclau);
		mysql_set_charset('utf8');
		if(!$link_id){
			echo "link id=".$link_id."<p>";
			$MYSQL_ERRNO=0;
			$MYSQL_ERROR='connexio tallada';
			return 0;
		}
		else if (!(mysql_select_db($db_defecte))){
			$MYSQL_ERRNO=mysql_errno();
			$MYSQL_ERROR=mysql_error();
			return 0;
		}
		else{
			return $link_id;
		}
	}
	function sql_error(){
		global $MYSQL_ERRNO,$MYSQL_ERROR;
		if (empty($MYSQL_ERROR)){
			$MYSQL_ERRNO=mysql_errno();
			$MYSQL_ERROR=mysql_error();
		}
		return "$MYSQL_ERRNO:$MYSQL_ERROR";
	}
?>