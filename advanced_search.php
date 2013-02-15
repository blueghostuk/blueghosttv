<?php
	require('includes/TV_Channel.php');
	require('/home/blueghos/db.php');
	require('includes/DB_Connection.php');
	require('includes/TV_DBConnection.php');
	require('includes/Bleb_Channel.php');
	
	$pg_title = "Advanced Search";
	
	include('header.php');
?>
	<form id="form1" name="form1" method="post" action="advanced_search_processor.php">
  		<table>
    	<tr>
    	  <td colspan="2">This search will look back over the next 7 days and the past 31 days for relevant programs</td>
  	  	</tr>
		<tr>
    	  <td colspan="2">More customisation of this will follow</td>
  	  	</tr>
    	<tr>
      		<td>Search Text</td>
			<td><input name="sText" type="text" size="50" maxlength="25" /></td>
		</tr>
		<tr>
			<td>Common Channels</td>
			<td>
				<select name="common">
					<option selected="selected" value="na">N/A</option>
					<option value="terr">Terrestrial</option>
					<option value="freeview">Freeview TV</option>
					<option value="radio">All Radio</option>
				</select>			
			</td>
		</tr>
		<tr>
			<td>Other Channels (5 Max)</td>
			<td>
			<?php
				$dbase   	= 'blueghos_tv';
				$Database 	= new TV_DBConnection();
				$Database->DB_connect($db_host, $db_user, $db_pwd, $dbase);
				
				$channels = $Database->getAllChannels();
				$cText = "<option selected=\"selected\" value=\"na\">Select A Channel</option>";
				foreach ($channels as $channel){
					$cText .= "<option value=\"".$channel->id."\">".$channel->title."</option>";
				}
				for($i=0; $i <5; $i++){
			?>
				<select name="channel_group_<?php echo $i;?>">
					<?php echo $cText;?>
				</select>
			<?php
				}
			?>			
			</td>
		</tr>
		<tr>
		  	<td>&nbsp;</td>
		  	<td><input type="submit" name="Submit" value="Search" /></td>
		</tr>
		</table>
	</form>
<?php
	include('footer.php');
?>