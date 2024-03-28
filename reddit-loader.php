<?php
$redditUrl = "https://old.reddit.com" . $_SERVER['REQUEST_URI'];
//eg: $redditUrl = "https://old.reddit.com/hot/.json";
if (!strpos($redditUrl, ".json")) {
	die("Not a valid Reddit URL: " . $redditUrl);
}
header('Content-Type: application/json');
$result = "";
$ch = curl_init();
// If it breaks, try different user agents
$agent = 'Mozilla/5.0 (Windows NT 6.2; WOW64; rv:17.0) Gecko/20100101 Firefox/17.0';
curl_setopt($ch, CURLOPT_USERAGENT, $agent);
curl_setopt($ch, CURLOPT_REFERER, 'https://old.reddit.com/');
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, $redditUrl);
$result=curl_exec($ch);
curl_close($ch);
echo $result;
?>
