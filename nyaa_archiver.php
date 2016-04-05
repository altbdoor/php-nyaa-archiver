<?php

include 'helper.php';

$basePath = dirname(__FILE__);

$options = getOptions();
$currentPath = $basePath.'/'.$options['fileName'];

$failedText = array(
	'The torrent you are looking for does not appear to be in the database',
	'The torrent you are looking for has been deleted'
);
$failedText = '/('.implode('|', $failedText).')/i';

for ($i=$options['start']; $i<=$options['end']; $i++) {
	// set basics
	$currentUrl = $options['baseUrl'].$i;
	
	$continue = true;
	$temp = null;
	
	printLog('Fetching ID '.$i.'...');
	
	try {
		// curl for html
		$temp = getHtmlFromUrl($currentUrl.'&page=view');
		if ($temp == false) {
			$continue = false;
			throw new Exception('Failed ID '.$i.'. Blame: HTML curl.');
		}
		
		printLog('Parsing ID '.$i.'...');
		
		// get xpath
		$xpath = getXpath($temp);
		
		// check if id is valid
		$temp = $xpath->query('.//div[@class="content"]');
		if ($temp->length > 0 && preg_match($failedText, $temp->item(0)->textContent)) {
			throw new Exception('Failed ID '.$i.'. Blame: Does not exist.');
		}
		
		// prep json object
		$json = array();
		
		// id
		if (in_array('id', $options['fields'])) {
			$json['id'] = $i;
		}
		
		// name
		if (in_array('name', $options['fields'])) {
			$json['name'] = '';
			
			$temp = $xpath->query('.//td[@class="viewtorrentname"]')->item(0);
			if ($temp->textContent != '') {
				$json['name'] = $temp->textContent;
			}
			else {
				$continue = false;
				throw new Exception('Failed ID '.$i.'. Blame: Incomplete HTML, name.');
			}
		}
		
		// category
		if (in_array('category', $options['fields'])) {
			$json['category'] = array();
			
			$temp = $xpath->query('.//td[@class="viewcategory"]/a');
			foreach ($temp as $item) {
				$json['category'][] = $item->textContent;
			}
		}
		
		// timestamp
		if (in_array('timestamp', $options['fields'])) {
			$json['timestamp'] = 0;
			
			$temp = $xpath->query('.//td[@class="viewtorrentname"]')->item(0);
			$temp = $temp->parentNode->lastChild;
			if (preg_match('/^\d{4}-\d{2}-\d{2}, \d{2}:\d{2} UTC$/', $temp->textContent)) {
				$json['timestamp'] = getTimestampFromStr($temp->textContent);
			}
			else {
				$continue = false;
				throw new Exception('Failed ID '.$i.'. Blame: Incomplete HTML, timestamp.');
			}
		}
		
		// description
		if (in_array('description', $options['fields'])) {
			$json['description'] = '';
			
			$temp = $xpath->query('.//div[@class="viewdescription"]')->item(0);
			$json['description'] = getDOMInnerHTML($temp);
		}
		
		// file size
		if (in_array('filesize', $options['fields'])) {
			$json['filesize'] = '';
			
			$temp = $xpath->query('.//table[@class="viewtable"]/tr[last()]/td[last()]')->item(0);
			if ($temp->textContent != '') {
				$json['filesize'] = $temp->textContent;
			}
			else {
				$continue = false;
				throw new Exception('Failed ID '.$i.'. Blame: Incomplete HTML, filesize.');
			}
		}
		
		// magnet
		if (in_array('magnet', $options['fields'])) {
			$json['magnet'] = '';
			
			$magnet = getHeaderFromUrl($currentUrl.'&page=download&magnet=1');
			if ($magnet == false) {
				$continue = false;
				throw new Exception('Failed ID '.$i.'. Blame: Magnet empty.');
			}
			else {
				if (preg_match('/Location: magnet:\?xt=urn:btih:(.+?)&tr=/', $magnet, $temp)) {
					$json['magnet'] = $temp[1];
				}
				else if (preg_match('/Refresh: 1;url=http:\/\/www\.nyaa\.se\/\?tid=(\d+)&page=download&magnet=1/', $magnet, $temp)) {
					$json['magnet'] = '#'.$temp[1];
				}
			}
		}
		
		file_put_contents($currentPath, json_encode($json).",\n", FILE_APPEND);
		printLog('Success ID '.$i.'.');
	}
	catch (Exception $ex) {
		$message = $ex->getMessage();
		
		if (!$continue) {
			$message .= ' Retrying in '.$options['failSleep'].'s.';
			printLog($message);
			
			$i--;
			sleep($options['failSleep']);
		}
		else {
			printLog($message);
		}
	}
	
}
