<?php

	require('includes/paths.php');
	
	if (isset($_REQUEST['p']))
	{
		if (strpos($_REQUEST['p'], 'crid') === false)
		{/*bleb prog*/
			$file = $dir.'cache/html/bleb_program/'.$_REQUEST['p'].'.html';
		}
		else
		{
			$file = str_replace("crid://bbc.co.uk/", "crid_bbc.co.uk_", $_REQUEST['p']);
			$file = str_replace("crid:/bbc.co.uk/", "crid_bbc.co.uk_", $file);
			$file = $dir.'cache/html/bbc_program/'.$file.'.html';
		}
		if (file_exists($file) && ( filesize($file) > 0 ))
		{
			echo "<!--Cached File -->\n";
			include('header.php');
			include($file);
		}
		else
		{
			require('includes/io.php');
			require('includes/XML_Parser.php');
			require('includes/TV_Channel.php');
			require('includes/TV_Program.php');
			require('includes/TV_Related_Info.php');
			require('includes/TV_Genre.php');
			require('includes/TV_AVAttributes.php');
			require('includes/TV_Program_Schedule.php');
			require('includes/TV_ProgramParser.php');
			require('includes/TV_ScheduleParser.php');
			require('includes/TV_Outputter.php');
			require('includes/TV_View.php');
			require('/home/blueghos/db.php');
			require('includes/DB_Connection.php');
			require('includes/TV_DBConnection.php');
			require('includes/Bleb_ProgramParser.php');
			require('includes/Bleb_Program.php');
			require('includes/Bleb_AVAttributes.php');
			require('includes/Bleb_Program_Schedule.php');	
			require('includes/Bleb_Outputter.php');
			require('includes/Bleb_Channel.php');
			require('includes/AmazonSearch.php');
			require('includes/AmazonSearchResults.php');
			require('includes/AmazonItem.php');
			require('includes/AmazonSearchParser.php');
			
			echo "<!--Database File-->\n";
			$dbase   	= 'blueghos_tv';
			$Database 	= new TV_DBConnection();
			$Database->DB_connect($db_host, $db_user, $db_pwd, $dbase);
			
			if (strpos($_REQUEST['p'], 'crid') === false)
			{/*bleb prog*/
				//$pid = split("_", $_REQUEST['p'], 0)[0];
				$prog = $Database->findBlebProgram($_REQUEST['p']);
				$scheds = $Database->findBlebSchedules($_REQUEST['p']);
				$com_title = str_replace(" ", "+", $prog->title);
				$com_title = str_replace("'", "\'", $com_title);
				$xml_link = '/feeds/rss/series/'.$com_title;
				$xml_title = 'RSS Feed for SERIES:'.$com_title;
				include('header.php');
				$op = new Bleb_Outputter($prog);
				if ($op->parseProgramLongDescription($scheds))
				{
					echo str_replace("\n", "<br />", $op->getOutput());
					write_header($file, $op->getOutput());
				}
				else
				{
					echo '<h1>Error</h1>';
				}
			}
			else
			{/*tv-any prog*/
				$program = eregi_replace('crid:/', 'crid://', $_REQUEST['p']); // fix url pattern matching
				$prog = $Database->findProgram($program);
				$scheds = $Database->findSchedules($program);
				$xml_link = '/feeds/rss/series/'.$prog->series;
				$xml_title = 'RSS Feed for SERIES:'.$prog->title;
				include('header.php');
				$op = new TV_Outputter($prog);
				if ($op->parseProgramLongDescription($scheds))
				{
					echo str_replace("\n", "<br />", $op->getOutput());
					echo "<!-- File Saved to ".$file." -->\n";
					write_header($file, $op->getOutput());
				}
				else
				{
					echo '<h1>Error</h1>';
				}
			}
		}
	}
	else
	{
		include('header.php');
		echo '<div class="prog"><strong>No Program Specified</strong></div>';
	}
	include('footer.php');
?>