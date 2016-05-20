<?php

// set default timezone
date_default_timezone_set('Asia/Kuala_Lumpur');


// get options
function getOptions () {
	$options = getopt('s:e:f:', array(
		'sukebei', 'failsleep:', 'fields:'
	));
	
	$return = array(
		'start' => 15,
		'end' => 20,
		'fileName' => 'output.json',
		'baseUrl' => 'http://www.nyaa.se/?tid=',
		'failSleep' => 10,
		'fields' => array(
			'id', 'name', 'category', 'timestamp', 'description', 'filesize', 'magnet'
		)
	);
	
	if (isset($options['s']) && preg_match('/^\d+$/', $options['s'])) {
		$return['start'] = (int)$options['s'];
	}
	if (isset($options['e']) && preg_match('/^\d+$/', $options['e'])) {
		$return['end'] = (int)$options['e'];
	}
	if (isset($options['f'])) {
		$return['fileName'] = $options['f'];
	}
	if (isset($options['sukebei'])) {
		$return['baseUrl'] = 'http://sukebei.nyaa.se/?tid=';
	}
	if (isset($options['failsleep']) && preg_match('/^\d+$/', $options['failsleep'])) {
		$return['failSleep'] = (int)$options['failsleep'];
	}
	if (isset($options['fields']) && is_string($options['fields'])) {
		$return['fields'] = explode(',', $options['fields']);
	}
	
	return $return;
}


// get domdocument inner html
function getDOMInnerHTML ($domNode) {
	$innerHTML = '';
	$children  = $domNode->childNodes;
	
	foreach ($children as $child) {
		$innerHTML .= $domNode->ownerDocument->saveHTML($child);
	}
	
	return $innerHTML;
}


// format timestamp from date string
function getTimestampFromStr ($date) {
	$timezone = new DateTimeZone('UTC');
	$datetime = DateTime::createFromFormat('Y-m-d, H:i \U\T\C', $date, $timezone);
	
	$datetime->setTime(
		$datetime->format('H'),
		$datetime->format('i'),
		0
	);
	
	return (int)$datetime->format('U');
}


// fetch url
function getHtmlFromUrl ($url) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_ENCODING, '');
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
	curl_setopt($ch, CURLOPT_HEADER, 0);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($ch, CURLOPT_TIMEOUT, 10);
	
	$content = curl_exec($ch);
	
	if (curl_errno($ch) || $ch === false) {
		$content = false;
	}
	
	curl_close($ch);
	return $content;
}


// fetch header
function getHeaderFromUrl ($url) {
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_ENCODING, '');
	curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
	curl_setopt($ch, CURLOPT_HEADER, 1);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
	
	$content = curl_exec($ch);
	
	if (curl_errno($ch) || $ch === false) {
		$content = false;
	}
	
	curl_close($ch);
	return $content;
}


// get doc and xpath
function getXpath ($content) {
	$doc = new DomDocument;
	$doc->preserveWhiteSpace = false;
	$doc->loadHTML($content);
	
	return new DOMXpath($doc);
}


// print log
function printLog ($str) {
	echo date('c').' '.$str."\n";
}

function checkValid ($valid) {
	$isValid = true;
	foreach ($valid as $key => $value) {
		$isValid = $isValid && $value;
		
		if (!$isValid) {
			break;
		}
	}
	
	return $isValid;
}
