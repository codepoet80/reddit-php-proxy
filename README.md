# reddit-php-proxy

A tiny PHP proxy that forwards requests to `old.reddit.com` (handy for legacy clients that can't reach Reddit directly).

## Reddit session cookie required

Reddit now blocks fully headless/proxied requests even for public content. To work around the blocker, the proxy sends your browser's Reddit session cookies with each request, so it looks like a real logged-in user. You do **not** need a Reddit account — simply visiting reddit.com in a browser is enough to get a valid session cookie.

To export cookies (Firefox example):

1. Visit `reddit.com` (or `old.reddit.com`) in your browser.
2. Install a cookies exporter extension, e.g. ["Get cookies.txt LOCALLY"](https://addons.mozilla.org/en-US/firefox/addon/cookies-txt/) or [cookies.txt](https://addons.mozilla.org/en-US/firefox/addon/cookies-txt/).
3. Export your cookies in **Netscape format** to a file named `reddit-cookies.txt`.
4. Place that file in the **parent folder** of this app — i.e. one level *above* the web root, so it is **not** publicly downloadable. If it lives inside the web root, anyone could grab the file and hijack your Reddit session.
5. Make it readable by your web server: `chmod 644 reddit-cookies.txt`.

The proxy loads the file automatically (`../reddit-cookies.txt` relative to `index.php`). If the file is absent, the proxy still runs but Reddit may block it.

The session cookie will eventually expire; re-export and overwrite the file when Reddit requests start failing again.
