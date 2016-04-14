<link rel="stylesheet" href="//code.jquery.com/ui/1.11.4/themes/smoothness/jquery-ui.css">
<script src="http://code.jquery.com/jquery-1.8.3.min.js"></script>
<script src="http://code.jquery.com/ui/1.9.2/jquery-ui.js"></script>
<link rel="stylesheet" href="/resources/demos/style.css">
<?php
	$minDateForDatepicker = "";
	$result = mysql_query("SELECT * FROM pantheon_log WHERE status = 1 ORDER BY epoch ASC LIMIT 1");
	while($row = mysql_fetch_array($result)){$minDateForDatepicker = $row{"epoch"};}
?>
<script>
jQuery(function($) {
	$("#dailyDatePicker").datepicker({
		onSelect: function(dateText) {
			$(this).change();
		},
		firstDay: 1,
		dateFormat: 'yy-mm-dd',
		maxDate: new Date()
	}).on("change", function() {
		$("#dailyDateForm").submit();
	});
	$("#dailyDatePicker").datepicker('option','minDate','<?php echo gmdate('Y-m-d', $minDateForDatepicker); ?>');
});
</script>
<?php
	if(isset($_GET['ORDER_BY']) && isValidOrderBy($_GET['ORDER_BY']))
	{
		$ORDER_BY = $_GET['ORDER_BY'];
	}
	else
	{
		$ORDER_BY = "name";
	}
	if(isset($_GET['ORDER_TYPE']) && isValidOrderType($_GET['ORDER_TYPE']))
	{
		$ORDER_TYPE = $_GET['ORDER_TYPE'];
		if($ORDER_TYPE=="ASC")
		{
			$ORDER_TYPE_NEXT = "DESC";
		}
		else
		{
			$ORDER_TYPE_NEXT = "ASC";
		}
	}
	else
	{
		$ORDER_TYPE = "ASC";
		$ORDER_TYPE_NEXT = "DESC";
	}
?>
<table border='0' align='center' id='dailyTable'>
	<form action="<?php $_SERVER['PHP_SELF']; ?>" id="dailyDateForm" method='GET'>
		<tr>
			<td align="left" colspan='8'>
				<input type="hidden" name="p" value="daily" />
				<label for="dailyDatePicker">Date:</label>
				<input type="text" name="date" id="dailyDatePicker" value="<?php if(isset($_GET['date']) && validateDate($_GET['date'])){echo $_GET['date'];} else {echo date('Y-m-d', time()); $_GET['date'] = date('Y-m-d', time());} ?>" />
				<?php
					if (isset($_GET['ORDER_BY']) && isValidOrderBy($_GET['ORDER_BY']))
					{
						echo "
				<input type='hidden' name='ORDER_BY' value='" . $ORDER_BY . "' />
				<input type='hidden' name='ORDER_TYPE' value='" . $ORDER_TYPE . "' />
						";
					}
				?>
			</td>
		</tr>
<?php
	if(isset($_GET['date']) && validateDate($_GET['date']))
	{
			unset($time1);
			unset($time2);
			$result = mysql_query("SELECT * FROM pantheon_log WHERE status = 1 AND epoch < ". (strtotime($_GET['date']) + 23*60*60 )  ." ORDER BY epoch DESC LIMIT 1");
			while($row = mysql_fetch_array($result)){$time1 = $row{"epoch"};}
			$result = mysql_query("SELECT * FROM pantheon_log WHERE status = 1 AND epoch < ". strtotime($_GET['date']) ." ORDER BY epoch DESC LIMIT 1");
			while($row = mysql_fetch_array($result)){$time2 = $row{"epoch"};}
			if(!isset($time1) || !isset($time2))
			{
				return;
			}
			unset($dataArray);
			$result = mysql_query("SELECT * FROM pantheon WHERE epoch = " . $time1 . " ORDER BY name ASC");
			while($row = mysql_fetch_array($result))
			{
				$result2 = mysql_query("SELECT * FROM pantheon WHERE epoch = " . $time2 . " ORDER BY name ASC");
				while($row2 = mysql_fetch_array($result2))
				{
					if( $row{"member_id"} == $row2{"member_id"})
					{
						$dataArray[$row{"member_id"}] = array(
							$row{"member_id"},					// 0  member id
							$row{"name"},						// 1  new name
							$row{"prestige"},					// 2  new prestige
							$row2{"prestige"},					// 3  old prestige
							($row{"prestige"} - $row2{"prestige"}),			// 4  prestige gaint abs
							gainPercentage($row{"prestige"}, $row2{"prestige"}),	// 5  prestige gaint perc
							$row{"credits"},					// 6  new credits
							$row2{"credits"},					// 7  old credits
							($row{"credits"} - $row2{"credits"}),			// 8  credits gain abs
							gainPercentage($row{"credits"}, $row2{"credits"}),	// 9  credits gain prec
							$row{"resources"},					// 10 new construction
							$row2{"resources"},					// 11 old construction
							($row{"resources"} - $row2{"resources"}),		// 12 construction gain abs
							gainPercentage($row{"resources"}, $row2{"resources"})	// 13 construction gain perc
						);
					}
				}
			}
			echo "
				<tr>
					<td colspan='8' height='30px' style='text-align:left'>Data comparison between " . gmdate('Y-m-d H:i:s', $time1 ) . " and " . gmdate('Y-m-d H:i:s', $time2 ) . " GMT.</td>
				</tr>
			";
			echo "
				<tr id='currentTableTop'>
					<td width='30px' id='tableColumnSelector' rowspan='2'>#</td>
					<td rowspan='2'><a href='?p=daily&date=". $_GET['date'] ."' id='tableColumnSelector'>Name</a></td>
					<td width='200px' colspan='2' id='tableColumnSelector'>Prestige Gain</td>
					<td width='200px' colspan='2' id='tableColumnSelector'>Credits Donated</td>
					<td width='200px' colspan='2' id='tableColumnSelector'>Construction Resources Gain</td>
				</tr>
				<tr id='currentTableBotom1'>
					<td style='text-align:right;background:#DDD;'><a href='?p=daily&date=". $_GET['date'] ."&ORDER_BY=prestigeValue&ORDER_TYPE=" . $ORDER_TYPE_NEXT . "' id='tableColumnSelector'>Value</a></td>
					<td style='text-align:right;background:#DDD;' width='80px'><a href='?p=daily&date=". $_GET['date'] ."&ORDER_BY=prestigePercent&ORDER_TYPE=" . $ORDER_TYPE_NEXT . "' id='tableColumnSelector'>%</a></td>
					<td style='text-align:right;background:#DDD;'><a href='?p=daily&date=". $_GET['date'] ."&ORDER_BY=creditsValue&ORDER_TYPE=" . $ORDER_TYPE_NEXT . "' id='tableColumnSelector'>Value</a></td>
					<td style='text-align:right;background:#DDD;' width='80px'><a href='?p=daily&date=". $_GET['date'] ."&ORDER_BY=creditsPercent&ORDER_TYPE=" . $ORDER_TYPE_NEXT . "' id='tableColumnSelector'>%</a></td>
					<td style='text-align:right;background:#DDD;'><a href='?p=daily&date=". $_GET['date'] ."&ORDER_BY=resourcesValue&ORDER_TYPE=" . $ORDER_TYPE_NEXT . "' id='tableColumnSelector'>Value</a></td>
					<td style='text-align:right;background:#DDD;' width='80px'><a href='?p=daily&date=". $_GET['date'] ."&ORDER_BY=resourcesPercent&ORDER_TYPE=" . $ORDER_TYPE_NEXT . "' id='tableColumnSelector'>%</a></td>
				</tr>
			";
			if (isset($_GET['ORDER_BY']) && isValidOrderBy($_GET['ORDER_BY']))
			{
				unset($GLOBALS['var_order']);
				switch($_GET['ORDER_BY'])
				{
					case "prestigeValue":
						$GLOBALS['var_order'] = "4";
						break;
					case "prestigePercent":
						$GLOBALS['var_order'] = "5";
						break;
					case "creditsValue":
						$GLOBALS['var_order'] = "8";
						break;
					case "creditsPercent":
						$GLOBALS['var_order'] = "9";
						break;
					case "resourcesValue":
						$GLOBALS['var_order'] = "12";
						break;
					case "resourcesPercent":
						$GLOBALS['var_order'] = "13";
						break;
					default:
						$GLOBALS['var_order'] = "1";
				}
				if (isset($_GET['ORDER_TYPE']) && $_GET['ORDER_TYPE'] == "ASC")
				{
					function sortByOrder($a, $b) {
						return $a[$GLOBALS['var_order']] - $b[$GLOBALS['var_order']];
					} // usage -> usort($myArray, 'sortByOrder');
					uasort($dataArray, 'sortByOrder');
				}
				elseif (isset($_GET['ORDER_TYPE']) && isValidOrderType($_GET['ORDER_TYPE']))
				{
					function sortByOrder($a, $b) {
						return $b[$GLOBALS['var_order']] - $a[$GLOBALS['var_order']];
					} // usage -> usort($myArray, 'sortByOrder');
					uasort($dataArray, 'sortByOrder');
				}
			}
			$i = 1;
			foreach($dataArray as $member)
			{
				if(isset($_SESSION['member_id']) && $_SESSION['member_id']==$member["0"])
				{
					$background = "currentTableBottom2";
				}
				else
				{
					$background = "currentTableBottom". $i%2;
				}
					echo "
				<tr id='" . $background . "'>
					<td style='text-align:right'>" . $i . ".</td>
					<td style='text-align:left'><a href='https://eu.portal.sf.my.com/wall/". $member["0"] ."' style='text-decoration:none;color:#000;'>". $member["1"] ."</a></td>
					<td style='text-align:right'>" . number_format($member["4"]) . "</td>
					<td style='text-align:right'>" . $member["5"] . " %</td>
					<td style='text-align:right'>" . number_format($member["8"]) . "</td>
					<td style='text-align:right'>" . $member["9"] . " %</td>
					<td style='text-align:right'>" . number_format($member["12"]) . "</td>
					<td style='text-align:right'>" . $member["13"] . " %</td>
				</tr>
					";
				$i++;
			}
	}
?>
	</form>
</table>

