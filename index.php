<?php
// The cookies loaded below make every proxied request an authenticated one, so
// this URL must only expose public, read-only listing pages. Anything
// identity-specific (/message, /api, /user/*, /prefs, /subreddits/mine) stays
// unreachable from the open internet.
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/';
$sort = '(hot|new|rising|top|controversial|best)';
$sub = '[A-Za-z0-9_]{1,30}';
$id = '[A-Za-z0-9]{1,20}';
$allowed = [
	"@^/{$sort}?/?(\.json)?$@",
	"@^/r/{$sub}/?(\.json)?$@",
	"@^/r/{$sub}/{$sort}/?(\.json)?$@",
	"@^/r/{$sub}/comments/{$id}(/[^/]*)?/?(\.json)?$@",
	"@^/comments/{$id}(/[^/]*)?/?(\.json)?$@",
];
$permitted = false;
foreach ($allowed as $pattern) {
	if (preg_match($pattern, $path)) {
		$permitted = true;
		break;
	}
}
if (!$permitted) {
	header('Content-Type: text/plain', true, 403);
	die("Not an allowed Reddit path: " . $path);
}


// The ancient client this proxy serves cannot cope with a reply to /hot/.json
// that is not a listing, so every failure is dressed up as a one-story listing
// whose single post explains what went wrong.
function reddit_error_listing($explanation)
{
	$now = time();
	$explanation_html = "&lt;!-- SC_OFF --&gt;&lt;div class=\"md\"&gt;&lt;p&gt;"
		. str_replace("\n\n", "&lt;/p&gt;&lt;p&gt;", htmlspecialchars($explanation, ENT_QUOTES))
		. "&lt;/p&gt;&lt;/div&gt;&lt;!-- SC_ON --&gt;";
	$post = [
		"approved_at_utc"            => null,
		"subreddit"                  => "proxy",
		"selftext"                   => $explanation,
		"author_fullname"            => "t2_proxy",
		"saved"                      => false,
		"mod_reason_title"           => null,
		"gilded"                     => 0,
		"clicked"                    => false,
		"is_gallery"                 => false,
		"title"                      => "Reddit Fetch Error",
		"link_flair_richtext"        => [],
		"subreddit_name_prefixed"    => "r/proxy",
		"hidden"                     => false,
		"pwls"                       => 6,
		"link_flair_css_class"       => null,
		"downs"                      => 0,
		"thumbnail_height"           => null,
		"top_awarded_type"           => null,
		"hide_score"                 => false,
		"media_metadata"             => new stdClass,
		"name"                       => "t3_fetcherr",
		"quarantine"                 => false,
		"link_flair_text_color"      => "",
		"upvote_ratio"               => 1.0,
		"author_flair_background_color" => null,
		"ups"                        => 1,
		"total_awards_received"      => 0,
		"media_embed"                => new stdClass,
		"thumbnail_width"            => null,
		"author_flair_template_id"   => null,
		"is_original_content"        => false,
		"user_reports"               => [],
		"secure_media"               => null,
		"is_reddit_media_domain"     => false,
		"is_meta"                    => false,
		"category"                   => null,
		"secure_media_embed"         => new stdClass,
		"gallery_data"               => new stdClass,
		"link_flair_text"            => null,
		"can_mod_post"               => false,
		"score"                      => 1,
		"approved_by"                => null,
		"is_created_from_ads_ui"     => false,
		"author_premium"             => false,
		"thumbnail"                  => "self",
		"edited"                     => false,
		"author_flair_css_class"     => null,
		"author_flair_richtext"      => [],
		"gildings"                   => new stdClass,
		"content_categories"         => null,
		"is_self"                    => true,
		"subreddit_type"             => "public",
		"created"                    => $now,
		"link_flair_type"            => "text",
		"wls"                        => 6,
		"removed_by_category"        => null,
		"banned_by"                  => null,
		"author_flair_type"          => "text",
		"domain"                     => "self.proxy",
		"allow_live_comments"        => false,
		"selftext_html"              => $explanation_html,
		"likes"                      => null,
		"suggested_sort"             => null,
		"banned_at_utc"              => null,
		"url_overridden_by_dest"     => "",
		"view_count"                 => null,
		"archived"                   => false,
		"no_follow"                  => true,
		"is_crosspostable"           => false,
		"pinned"                     => false,
		"over_18"                    => false,
		"all_awardings"              => [],
		"awarders"                   => [],
		"media_only"                 => false,
		"can_gild"                   => false,
		"spoiler"                    => false,
		"locked"                     => true,
		"author_flair_text"          => null,
		"treatment_tags"             => [],
		"visited"                    => false,
		"removed_by"                 => null,
		"mod_note"                   => null,
		"distinguished"              => null,
		"subreddit_id"               => "t5_proxy",
		"author_is_blocked"          => false,
		"mod_reason_by"              => null,
		"num_reports"                => null,
		"removal_reason"             => null,
		"link_flair_background_color" => "",
		"id"                         => "fetcherr",
		"is_robot_indexable"         => false,
		"report_reasons"             => null,
		"author"                     => "reddit-proxy",
		"discussion_type"            => null,
		"num_comments"               => 0,
		"send_replies"               => false,
		"contest_mode"               => false,
		"mod_reports"                => [],
		"author_patreon_flair"       => false,
		"author_flair_text_color"    => null,
		"permalink"                  => "/r/proxy/comments/fetcherr/reddit_fetch_error/",
		"stickied"                   => true,
		"url"                        => "https://old.reddit.com/r/proxy/comments/fetcherr/reddit_fetch_error/",
		"subreddit_subscribers"      => 0,
		"created_utc"                => $now,
		"num_crossposts"             => 0,
		"media"                      => null,
		"is_video"                   => false,
	];
	return json_encode([
		"kind" => "Listing",
		"data" => [
			"after" => null,
			"dist" => 1,
			"modhash" => "",
			"geo_filter" => null,
			"children" => [["kind" => "t3", "data" => $post]],
			"before" => null,
		],
	]);
}

$redditUrl = "https://old.reddit.com" . $_SERVER['REQUEST_URI'];
//$redditUrl = "https://old.reddit.com/hot/.json";
if (!strpos($redditUrl, ".reddit.com")) {
	die("Not a valid Reddit URL: " . $redditUrl);
}
$is_json = strpos($redditUrl, ".json") !== false;
if ($is_json) {
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
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$effective_url = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
$curl_error = curl_error($ch);
//curl_close($ch);

// Reddit answers a .json request with a 302 to its HTML login page when the
// session cookies are missing or expired, and CURLOPT_FOLLOWLOCATION follows it,
// so an HTML body under a JSON content type is the usual shape of failure here.
$problem = null;
if ($result === false) {
	$problem = "The proxy could not reach Reddit at all (curl said: {$curl_error}).";
} elseif (strpos($effective_url, '/login/') !== false) {
	$problem = "Reddit redirected the request to its login page, so the saved session cookies are no longer valid.";
} elseif ($http_code >= 400) {
	$problem = "Reddit answered with HTTP {$http_code}.";
} elseif ($is_json && json_decode($result) === null && json_last_error() !== JSON_ERROR_NONE) {
	$problem = "Reddit sent " . strlen($result) . " bytes that are not JSON (probably an interstitial or block page).";
}

if ($problem === null) {
	echo $result;
	exit;
}

if (is_readable($cookies_file)) {
	$age = round((time() - filemtime($cookies_file)) / 86400, 1);
	$cookie_state = "reddit-cookies.txt was last updated {$age} days ago.";
} elseif (file_exists($cookies_file)) {
	$cookie_state = "reddit-cookies.txt exists but the web server cannot read it.";
} else {
	$cookie_state = "reddit-cookies.txt is missing.";
}

$explanation = "The proxy could not fetch this page from Reddit.\n\n"
	. $problem . "\n\n"
	. $cookie_state . " To fix it, export your logged-in Reddit cookies in "
	. "Netscape format and replace that file in the folder above the web root.\n\n"
	. "Requested: " . $redditUrl . "\n\n"
	. "Checked at " . gmdate('Y-m-d H:i:s') . " UTC.";

// Deliberately 200: the point is that the old client parses and displays this.
header('X-Reddit-Proxy-Error: ' . str_replace(["\r", "\n"], ' ', $problem));
if ($is_json) {
	header('Content-Type: application/json', true, 200);
	echo reddit_error_listing($explanation);
} else {
	header('Content-Type: text/plain', true, 502);
	echo $explanation;
}
?>
