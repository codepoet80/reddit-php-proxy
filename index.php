<?php
$redditUrl = "https://old.reddit.com" . $_SERVER['REQUEST_URI'];
//$redditUrl = "https://old.reddit.com/hot/.json";
if (!strpos($redditUrl, ".reddit.com")) {
	die("Not a valid Reddit URL: " . $redditUrl);
}
if (strpos($redditUrl, ".json")) {
	header('Content-Type: application/json');
}
$result = "";
$ch = curl_init();
$agent = 'Mozilla/5.0 (Windows NT 6.2; WOW64; rv:17.0) Gecko/20100101 Firefox/17.0';
curl_setopt($ch, CURLOPT_USERAGENT, $agent);
curl_setopt($ch, CURLOPT_REFERER, 'https://old.reddit.com/');
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_URL, $redditUrl);
// Reddit now blocks headless requests unless they carry a valid browser session.
// Export your logged-in cookies (Netscape format, e.g. "Get cookies.txt LOCALLY")
// and place the file in the parent folder, outside the web root, as reddit-cookies.txt.
$cookies_file = __DIR__ . '/../reddit-cookies.txt';
if (is_readable($cookies_file)) {
	curl_setopt($ch, CURLOPT_COOKIEFILE, $cookies_file);
}
$result=curl_exec($ch);
//curl_close($ch);
echo $result;
?>
