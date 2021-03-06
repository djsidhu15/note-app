<?php
	function search($q) {
		
		// function to implement search feature, prints all filename.
		$fq = "src/" . $q . "*.txt";
		$result = glob($fq);
		echo '<div style="text-align: left;"><br><b>&emsp;The search results are:</b><br><br><hr>';
		foreach($result as $filename) {
			$lnk = substr($filename, 4, -4);
			echo "&emsp;<a href='http://xtnote.com/$lnk' style='text-decoration:none; color:#5e5e5e;'>$lnk</a><hr>";
		}
		echo "</div>";
	}
	
	function parseUrl()
	{
		// function to parse the url into filename, link-word, query and password
						
		$url=$_SERVER['REQUEST_URI'];                // eg: $url = /wazlnk?p=hai
		list($lnk_s,$query) = explode('?', $url, 2); // eg: $lnk_s(with-slash) = /wazlnk , $query = 'p=hai'
 		$file_name="src" . $lnk_s . ".txt";	     // eg: $file_name = src/wazlnk.txt
 		$lnk=substr($file_name,4,-4);                // eg: $lnk(without-slash) = wazlnk
 		
 		
 		$passwd=$_GET['p'];                  		// eg: $passwd = hai
 		
 		return array($file_name, $lnk, $query, $passwd);
	}
	
	function lnkSymbolsCheck($lnk)
	{
		// function to check whether the link-work only contains allowed character set 
		if(preg_match("/^[a-zA-Z0-9-]+$/",$lnk))
			return 1;
		else
			return 0;
	}
	
	function queryTypeCheck($query)
	{
		// function to check, querytype = "p="
		$type=substr($query,0,2);
		if(!empty($query) && !($type=='p='))
			return 0;
		else
			return 1;
	}
	
	function error($errnum, $lnk="")
	{
		// function to handle different errors
		if($errnum==101)
			echo '<script>window.location="http://xtnote.com/oops.php?error=101"</script>';
		if($errnum==102)
			echo '<script>window.location="http://xtnote.com/oops.php?error=102"</script>';
		if($errnum==103)
			echo "<script>window.location='http://xtnote.com/oops.php?error=103&lnk=" . $lnk . "'</script>";
		if($errnum==104)
			echo "<script>window.location='http://xtnote.com/oops.php?error=104&lnk=" . $lnk . "'</script>";
	}
	
	function addPasswdEntry($lnk, $passwd)
	{
		// function to add the link_word-password entry in the XML file.
		$xmldoc=new DOMDocument();
 		$xmldoc->load("passwd.xml");
 		$XMLroot=$xmldoc->getElementsByTagName("shribc")->item(0);
 		$XMLpslink=$xmldoc->createElement('pslink');
 		$XMLlnk=$xmldoc->createElement('lnk');
 		$XMLpass=$xmldoc->createElement('passwd');
 		$XMLstrlnk=$xmldoc->createTextNode($lnk);
 		$XMLstrpass=$xmldoc->createTextNode($passwd);
 		$XMLpslink=$XMLroot->appendChild($XMLpslink);
 		$XMLlnk=$XMLpslink->appendChild($XMLlnk);
 		$XMLpass=$XMLpslink->appendChild($XMLpass);
 		$XMLstrlnk=$XMLlnk->appendChild($XMLstrlnk);
 		$XMLstrpass=$XMLpass->appendChild($XMLstrpass);
 		$xmldoc->save("passwd.xml");
	}
	
	function getPasswd($lnk)
	{
		// function returns actual passwd if it exits - otherwise it returns empty string
		$passwd="";
		$xml=simplexml_load_file("passwd.xml");
 			foreach($xml->children() as $itr)
 			{
 				if($itr->lnk==$lnk)
 				{	
 					$passwd= $itr->passwd;
 					break;
 				}
 			}
 		return $passwd;
	}
	
	function getViewMode($lnk)
	{
		// function returns the view-mode of the file
		$vm="";
		$xml=simplexml_load_file("passwd.xml");
 			foreach($xml->children() as $itr)
 			{
 				if($itr->lnk==$lnk)
 				{	
 					$vm= $itr->view_mode;
 					break;
 				}
 			}
 		return $vm;
	}
	
	function FilePasswdManager($file_name, $lnk, $typedPasswd)
	{
		// function checks whether the file exists or not. 
		// If file exists, then gets the actual password, compares it with typed password and takes neccessary action.
		// If file doesn't exit, then creates the file and if password is given, updates the XML.
		
		if(file_exists($file_name))
		{
			$actualPasswd = getPasswd($lnk);
			
			if(empty($typedPasswd) && empty($actualPasswd)) {}
			elseif(!empty($typedPasswd) && !empty($actualPasswd) && $typedPasswd!=$actualPasswd)
			{
				error(104,$lnk);
				exit();
			}
			elseif(empty($typedPasswd) && !empty($actualPasswd))
			{
				$view_mode = getViewMode($lnk);
				if($view_mode=="1")
				{
					$lu_unix_time = filemtime($file_name);;
					echo "<script>
					document.onreadystatechange = function () {
	     					if (document.readyState == 'complete') {
							var a = new Date($lu_unix_time * 1000);
							var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
							var year = a.getFullYear();
							var month = months[a.getMonth()];
							var date = a.getDate();
							var hour = a.getHours();
							var min = a.getMinutes();
							var sec = a.getSeconds();
							var time = date + ' ' + month + ', ' + year;
							
							var lu_block = document.getElementById('lu_time');
							lu_block.innerHTML = time;
						}
					}
					</script>";
					
					echo "<xmp theme='united' style='display:none;'>";
					echo "> <code class='prettyprint lang- prettyprinted'>
					<span class='pln'>This page has been generated by xTnote.com since the user has set his/her</span> 
					<span class='typ'>note in read-only mode</span></code>";
					
					echo "\n\n";
					
					echo "> <code class='prettyprint lang- prettyprinted'>
					<span class='typ'>Last Modified on</span>
					<span class='pln' id='lu_time'></span></code>";
					
					echo "\n\n";
					echo file_get_contents($file_name);
					echo "</xmp>";
					echo "<script src='http://strapdownjs.com/v/0.2/strapdown.js'></script>";
					echo "<script>document.styleSheets[0].disabled = true;</script>";
					exit();
				}
				else {
				 	error(104,$lnk);
					exit();
				}
			}
			elseif(!empty($typedPasswd) && empty($actualPasswd))
			{
				addPasswdEntry($lnk, $typedPasswd);
			}
			else {}
		}
		else
 		{
 			$fh=fopen($file_name,"a");
			fclose($fh);
			if(!empty($typedPasswd))
			{
				addPasswdEntry($lnk, $typedPasswd);
			}
 			
 		}
	}

	$req_url = $_SERVER['REQUEST_URI'];
	$last_char = strlen($req_url)-1;
	
	if($req_url[$last_char]== '*') {
		search(substr($req_url, 1, -1));
		exit();
	}
	
	
	list($file_name, $lnk, $query, $passwd) = parseUrl();
	
	if(lnkSymbolsCheck($lnk)==0)
	{
		error(101);
		exit();
	}
	
	$qf = queryTypeCheck($query);
	
	if($qf==0)
	{
		error(102);
		exit();
	}
?>

<html>
<head>
	<link rel="stylesheet" type="text/css" href="css/style.css"/>
	<title>Instant note taking and sharing app..</title>
<head>

<body id="main-body" onFocus="parent_disable();" onClick="parent_disable();">
	<?php
		
 		FilePasswdManager($file_name, $lnk, $passwd);
 		
		if($_SERVER["REQUEST_METHOD"]=="POST")
		{
			$mynotes=$_POST["notes"];
			$fh=fopen($file_name,"w");
			fwrite($fh,$mynotes);
			fclose($fh);
			}
		
	?>
	
	<div class="header">
	<button class="button1" onclick="submitform()">Save</button>

	<button class="button2" onclick="options()">Options</button>
	</div>
	
	<form name="txtnote" method="post" action="<?php echo htmlspecialchars($_SERVER["REQUEST_URI"]);?>">
		<textarea id="txtbox"name="notes">
<?php 	
				echo file_get_contents($file_name);
				
			?></textarea>
		
	</form>

	<div class="footer">
		<!---<p>Some Rights Reserved.</p> -->
	</div>

	
	
<script type="text/javascript">
function submitform()
{

	document.txtnote.submit();
  
}

var popupWindow=null;

function options()
{
    	popupWindow = window.open('options.php?lnk=<?php echo $lnk; ?>','help','width=500px,height=500px,scrollbars=yes');
}
function parent_disable() {
	if(popupWindow && !popupWindow.closed)
	popupWindow.focus();
}

</script>

</body>
</html>