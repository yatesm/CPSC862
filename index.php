<html>
	<head>
		<title> CPSC 862: Document Matching </title>
      <script src="http://ajax.googleapis.com/ajax/libs/jquery/1.5/jquery.min.js"></script>
		<script>
         $(document).ready(function(){
         	$("#clear").click(function(event){
            	event.preventDefault();
            	$("#query").val("");
            });
           });
		</script>		
	</head>
	<body>
		<h1>Please input search terms!</h1>
		<form method="post" action="search.php" target="_blank">          
		<textarea name="query" id="query" rows="5" cols="80"></textarea>
		<input type="submit" value="Submit Query">
		<input type="submit" id="clear" value="Clear Query">
		</form>
	</body>
	
</html>
