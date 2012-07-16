 <?php 
$link = mysql_connect('williamjxj.ipowermysql.com', 'church_test', 'williamjxj'); 
if (!$link) { 
    die('Could not connect: ' . mysql_error()); 
} 
echo 'Connected successfully'; 
mysql_select_db(church); 
?> 
