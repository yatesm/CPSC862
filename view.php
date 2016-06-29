<html>
	<head>
		<title> Results </title>
	</head>
	<body>
	<?php
		include_once("dbconnect.inc.php");
		$docid = $_GET['docid'];
		$term = $_GET['terms'];
		$terms = explode(' ', $term);
		print "docid = ".$docid."<br />\n";
		$result = mysql_query("SELECT * FROM Documents where DocumentID = \"".$docid."\"");
		$fields = mysql_fetch_array($result, MYSQL_ASSOC);    					
   	if($fields) {
			$field_names = array_keys($fields);	
		  	print "<table align=\"left\" border=\"2\">\n";
			print "<tr style=\"background-color:#0000FF;color:#FFFFFF\">\n";
			foreach($field_names as $val)
				print "<td >".$val."</td>\n";
			print "</tr>\n";
  			print "<tr>\n";                    
	     	foreach($fields as $val) {
	     		foreach($terms as $highlight) {
		     		$val = str_ireplace($highlight, "<span style=\"background-color:#FF8888\">".$highlight."</span>", $val);
		     	}
  		      	print "<td>".$val."</td>";
  		   }
  		   print "</tr>\n</table>\n";
	   }	
	?>
	</body>
</html>
