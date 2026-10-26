"""Local front for the Egypt Roamer test site: http://127.0.0.1:8080 (login admin/admin).

Starts WORKERS `php -S` processes (serve.php) and spreads requests over them, so one slow page
doesn't block the others. Text responses are gzipped like the production edge.

    python proxy.py            # Ctrl+C stops the front and the PHP workers
"""
import gzip
import http.client
import os
import queue
import subprocess
import sys
from http.server import BaseHTTPRequestHandler, ThreadingHTTPServer

HERE = os.path.dirname(os.path.abspath(__file__))
PHP = os.environ.get("PHP") or os.path.expandvars(
    r"%LOCALAPPDATA%\Microsoft\WinGet\Packages\PHP.PHP.8.3_Microsoft.Winget.Source_8wekyb3d8bbwe\php.exe"
)
PORT = int(os.environ.get("ER_PORT", 8080))
WORKERS = 6
FIRST = 8081
HOP = {"connection", "keep-alive", "transfer-encoding", "te", "trailer", "upgrade", "proxy-authorization", "proxy-authenticate"}
TEXT = ("text/", "application/javascript", "application/json", "application/xml", "image/svg+xml", "application/rss+xml")

ports = [FIRST + i for i in range(WORKERS)]
free = queue.Queue()  # one request per `php -S` worker at a time (it is single-threaded)
for _p in ports:
    free.put(_p)


class Front(BaseHTTPRequestHandler):
    protocol_version = "HTTP/1.0"  # one request per connection, like the old front

    def log_message(self, fmt, *args):
        pass

    def forward(self):
        length = int(self.headers.get("Content-Length") or 0)
        body = self.rfile.read(length) if length else None
        headers = {k: v for k, v in self.headers.items() if k.lower() not in HOP and k.lower() != "accept-encoding"}
        # `php -S` on Windows now and then drops a connection under load: GET/HEAD are retried on another worker.
        tries = 3 if self.command in ("GET", "HEAD") else 1
        for attempt in range(tries):
            port = free.get()
            try:
                conn = http.client.HTTPConnection("127.0.0.1", port, timeout=300)
                conn.request(self.command, self.path, body=body, headers=headers)
                res = conn.getresponse()
                data = res.read()
                conn.close()
                break
            except (OSError, http.client.HTTPException) as err:
                if attempt == tries - 1:
                    self.send_error(502, f"PHP worker {port}: {err}")
                    return
            finally:
                free.put(port)
        ctype = res.getheader("Content-Type", "")
        zipped = (
            "gzip" in (self.headers.get("Accept-Encoding") or "")
            and len(data) > 1024
            and ctype.startswith(TEXT)
            and not res.getheader("Content-Encoding")
        )
        if zipped:
            data = gzip.compress(data, 6)
        self.send_response(res.status, res.reason)
        for k, v in res.getheaders():
            if k.lower() not in HOP and k.lower() != "content-length":
                self.send_header(k, v)
        if zipped:
            self.send_header("Content-Encoding", "gzip")
            self.send_header("Vary", "Accept-Encoding")
        self.send_header("Content-Length", str(len(data)))
        self.end_headers()
        if self.command != "HEAD":
            self.wfile.write(data)

    do_GET = do_POST = do_HEAD = do_PUT = do_DELETE = do_OPTIONS = do_PATCH = forward


def main():
    if not os.path.isfile(PHP):
        sys.exit(f"php.exe not found: {PHP} (set PHP=...)")
    env = dict(os.environ, PHP_CLI_SERVER_WORKERS="1")
    procs = [
        subprocess.Popen([PHP, "-S", f"127.0.0.1:{p}", "-t", "wordpress", "serve.php"], cwd=HERE, env=env,
                         stdout=subprocess.DEVNULL, stderr=subprocess.DEVNULL)
        for p in ports
    ]
    ThreadingHTTPServer.request_queue_size = 64  # the default backlog of 5 resets parallel test runs
    server = ThreadingHTTPServer(("127.0.0.1", PORT), Front)
    print(f"Egypt Roamer local: http://127.0.0.1:{PORT}/ ({WORKERS} PHP workers on {ports[0]}-{ports[-1]})", flush=True)
    try:
        server.serve_forever()
    except KeyboardInterrupt:
        pass
    finally:
        server.server_close()
        for p in procs:
            p.terminate()


if __name__ == "__main__":
    main()
