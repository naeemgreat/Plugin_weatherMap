<?php
// Sample Pluggable datasource for PHP Weathermap 0.9
// - read a pair of values from a database, and return it

// TARGET dbplug:databasename:username:pass:hostkey

class WeatherMapDataSource_mrtg extends WeatherMapDataSource {

	function Recognise($targetstring)
	{
		if(preg_match("/\.(htm|html)$/",$targetstring,$matches))
		{
			return TRUE;
		}
		else
		{
			return FALSE;
		}
	}

	function ReadData($targetstring, &$map, &$item)
	{
		$data[IN] = NULL;
		$data[OUT] = NULL;
		$data_time = 0;
		
		$matchvalue= $item->get_hint('mrtg_value');
		$matchperiod = $item->get_hint('mrtg_period');
		$swap = intval($item->get_hint('mrtg_swap'));
		$negate = intval($item->get_hint('mrtg_negate'));
				
		if($matchvalue =='') $matchvalue = "cu";
		if($matchperiod =='') $matchperiod = "d";	
				
		$fd=fopen($targetstring, "r");

		if ($fd)
		{
			while (!feof($fd))
			{
				$buffer=fgets($fd, 4096);
				debug("MRTG ReadData: Matching on '${matchvalue}in $matchperiod' and '${matchvalue}out $matchperiod'\n");

				if (preg_match("/<\!-- ${matchvalue}in $matchperiod ([-+]?\d+\.?\d*) -->/", $buffer, $matches)) { $data[IN] = $matches[1] * 8; }
				if (preg_match("/<\!-- ${matchvalue}out $matchperiod ([-+]?\d+\.?\d*) -->/", $buffer, $matches)) { $data[OUT] = $matches[1] * 8; }
			}
			fclose($fd);
			$data_time = filemtime($targetstring);
		}
		else
		{
			// some error code to go in here
			debug ("MRTG ReadData: Couldn't open ($targetstring). \n"); 
		}
		
		if($swap==1)
		{
			debug("MRTG ReadData: Swapping IN and OUT\n");
			$t = $data[OUT];
			$data[OUT] = $data[IN];
			$data[IN] = $t;
		}
		
		if($negate)
		{
			debug("MRTG ReadData: Negating values\n");
			$data[OUT] = -$data[OUT];
			$data[IN] = -$data[IN];
		}
		
		debug ("MRTG ReadData: Returning (".($data[IN]===NULL?'NULL':$data[IN]).",".($data[OUT]===NULL?'NULL':$data[IN]).",$data_time)\n");

		return( array($data[IN], $data[OUT], $data_time) );
	}
}

// vim:ts=4:sw=4:
?>
