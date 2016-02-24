<?php

// set default timezone
date_default_timezone_set('Asia/Kuala_Lumpur');


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
