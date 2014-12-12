<?php
mysql_connect("localhost","yonjyuni","42@moab@100")or die("can't connect to db");
mysql_select_db("42goals");           

$query = mysql_query("Select  login from users where datediff(now(),registered) <= 30 order by  login ASC");
//$query = mysql_query("Select  login from users where datediff(now(),registered) <= 300 order by  login ASC");
if(mysql_num_rows($query)>0)
{
	while($rs = mysql_fetch_array($query))
	{
		
		// check dir //
		$docRoot='';
		$root = str_replace("fixdir.php","",$_SERVER['SCRIPT_FILENAME']);
		print $docRoot = $root."public/img/icons/".$rs["login"];
		if(is_dir($docRoot))
		{
			print "<br/>";
			print "exists";
			print "<br/>";
			chmod($docRoot, 0777); 
			exec('chmod -R 777 '.$docRoot);
		}
		else
		{
			print "<br/>";
			print "not exists";
			print "<br/>";
			// Lets create dir and chmod 777 on it  //
			mkdir($docRoot, 0777);
		}	
		
		
	}

}

?>
