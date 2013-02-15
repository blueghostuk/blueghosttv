<?php
switch ($_REQUEST['op']) {
default:
$output1 = "
<html>
<head>
<LINK TYPE=\"text/css\" REL=\"stylesheet\" HREF=\"http://uk.cricinfo.com//navigation/cricinfo/default.css\">
<title>BlueGhost Cricinfo</title>
</head>
<body>
<form action=\"scores.php\" method=\"post\">
<input type=\"hidden\" name=\"op\" value=\"process\">
Enter URL : <input type=\"text\" name=\"uri\" lenght=\"20\">
<input type=\"submit\" value=\"Send\">
</form>";

$url = "http://uk.cricinfo.com/homepage/index01.html";
$output = file_get_contents($url);
$output = eregi_replace ("<!-- Live scores panel -->", "<LIVEBALL>", $output);
$output = eregi_replace ("<td colspan=7 bgcolor=#9D928E>
    <img src=\"http://img.cricinfo.com/spacer.gif\" width=5 height=5 alt=\"\"><br>
   </td>
  </tr>
  </table>", "<td colspan=7 bgcolor=#9D928E>
    <img src=\"http://img.cricinfo.com/spacer.gif\" width=5 height=5 alt=\"\"><br>
   </td>
  </tr>
  </table><LIVEBALL2>", $output);
$output = eregi_replace ("<!--([^-]*([^-]|-([^-]|-[^>])))*-->", "", $output);
$output = eregi_replace ("<LIVEBALL>", "--><LIVEBALL>", $output);
$output = eregi_replace ("<LIVEBALL2>", "<LIVEBALL2><!--", $output);
$output = eregi_replace ("<html>", "<!--", $output);
$output = eregi_replace ("</html>", "-->", $output);
$output = eregi_replace ("<!--([^-]*([^-]|-([^-]|-[^>])))*-->", "", $output);
$output = eregi_replace ("<LIVEBALL>", "<!--SCORES START-->", $output);
$output = eregi_replace ("<LIVEBALL2>", "<!--SCORED END-->", $output);
//$output = eregi_replace("<img[^>]*>","",$output);
$output = eregi_replace ("href=\"", "href=\"scores.php?op=process2&amp;uri=http://uk.cricinfo.com", $output);
break;

case 'process';
$uri = $_REQUEST['uri'];
$output = file_get_contents($uri);
$output = eregi_replace ("<!--", "", $output);
$output = eregi_replace ("<!--//", "", $output);
$output = eregi_replace ("-->", "", $output);
$output = eregi_replace ("//-->", "", $output);
$output = eregi_replace ("<SCRIPT", "<!--", $output);
$output = eregi_replace ("</SCRIPT>", "-->", $output);
$output = eregi_replace ("&NBSP;", "", $output);
$output = eregi_replace ("ONLOAD", "", $output);
$output = eregi_replace ("CHECK_FRAMESET()", "", $output);
$output = eregi_replace ("<NOSCRIPT>", "<!--", $output);
$output = eregi_replace ("</NOSCRIPT>", "-->", $output);
$output = eregi_replace ("<br>", "<br/>", $output);
$output = eregi_replace ("target=\"main\"", "", $output);
$output = eregi_replace ("A HREF=\"/", "A HREF=\"scores.php?op=process&amp;uri=http://live.cricinfo.com/", $output);
$output = eregi_replace ("A HREF=\"ENG", "A HREF=\"scores.php?op=process&amp;uri=http://uk.cricinfo.com/link_to_database/ARCHIVE/2002-03/ENG_IN_AUS/SCORECARDS/ENG", $output);
$output = eregi_replace ("<IFRAME", "<!--", $output);
$output = eregi_replace ("</IFRAME>", "", $output);
$output = eregi_replace ("Advertise on CricInfo", "", $output);
$output = eregi_replace ("<!--([^-]*([^-]|-([^-]|-[^>])))*-->", "", $output);
$output = eregi_replace ("</style>[ ]*UK/EU/India Banner ad[ ]*<p>[ ]*<CENTER>", "</style><CENTER>", $output);
echo '<HEAD>
  <meta content="45; url=http://www.blueghost.co.uk/scores.php?op=process&uri='.$uri.'" http-equiv="refresh" />';
break;

case 'process2':
$uri = $_REQUEST['uri'];
$output = file_get_contents($uri);
$output = eregi_replace ("<!--", "", $output);
$output = eregi_replace ("<!--//", "", $output);
$output = eregi_replace ("-->", "", $output);
$output = eregi_replace ("//-->", "", $output);
$output = eregi_replace ("<FRAMESET", "--><FRAMESET", $output);
$output = eregi_replace ("</FRAMSET>", "</FRAMSET><!--", $output);
$output = eregi_replace ("<html>", "<!--", $output);
$output = eregi_replace ("</html>", "-->", $output);
$output = eregi_replace ("<!--([^-]*([^-]|-([^-]|-[^>])))*-->", "", $output);
$output = eregi_replace ("- /usr/cricinfo", "<!--", $output);
$output = eregi_replace ("MARGINWIDTH=\"8\"", "-->", $output);
$output = eregi_replace ("NAME=\"main\"", "<!--", $output);
$output = eregi_replace ("</FRAMSET>", "-->", $output);
$output = eregi_replace ("<!--([^-]*([^-]|-([^-]|-[^>])))*-->", "", $output);
$output = eregi_replace ("SCROLLING=\"AUTO\">", "<!--", $output);
$output = eregi_replace ("SRC=", "-->", $output);
$output = eregi_replace ("<!--([^-]*([^-]|-([^-]|-[^>])))*-->", "", $output);
$output = eregi_replace ("\"", "", $output);
$output = eregi_replace (" ", "", $output);
$output = eregi_replace ("SCROLLING=NO>","",$output);
$output = eregi_replace ("<FRAME-->","",$output);
echo"<META HTTP-EQUIV=Refresh CONTENT=\"0; URL=scores.php?op=process&uri=http://uk.cricinfo.com".$output."\">";
echo"Redirecting... or <a href=\"scores.php?op=process&uri=http://uk.cricinfo.com".$output."\">Click here</a>";
break;
}

echo $output1;
echo"<br/><br/>";
echo $output;
echo"<br/><br/><a href=\"scores.php\">More Live Scores</a>";
echo"<br/><br/><a href=\"http://www.cricinfo.com\">All Content Copyright Cricinfo.com</a>
</body>
</html>";
?>

