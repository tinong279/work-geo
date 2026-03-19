import sys
import os
import ssl
import time
import urllib.request


def save_first_jpeg_frame(url, output_path, timeout=15):
    ssl_ctx = ssl.create_default_context()
    ssl_ctx.check_hostname = False
    ssl_ctx.verify_mode = ssl.CERT_NONE

    req = urllib.request.Request(
        url,
        headers={
            "User-Agent": "Mozilla/5.0",
            "Cache-Control": "no-cache",
            "Pragma": "no-cache",
        },
    )

    with urllib.request.urlopen(req, timeout=timeout, context=ssl_ctx) as resp:
        content_type = (resp.headers.get("Content-Type") or "").lower()

        output_dir = os.path.dirname(output_path)
        if output_dir:
            os.makedirs(output_dir, exist_ok=True)

        # 如果來源本身就是單張 JPEG
        if "image/jpeg" in content_type or "image/jpg" in content_type:
            data = resp.read()
            if data:
                with open(output_path, "wb") as f:
                    f.write(data)
                return True
            return False

        # 如果是串流 / MJPEG，抓第一張 JPEG frame
        buffer = b""
        start_time = time.time()

        while time.time() - start_time < timeout:
            chunk = resp.read(4096)
            if not chunk:
                break

            buffer += chunk

            jpg_start = buffer.find(b"\xff\xd8")
            jpg_end = buffer.find(b"\xff\xd9")

            if jpg_start != -1 and jpg_end != -1 and jpg_end > jpg_start:
                jpg_data = buffer[jpg_start : jpg_end + 2]
                with open(output_path, "wb") as f:
                    f.write(jpg_data)
                return True

            # 防止 buffer 過大
            if len(buffer) > 8 * 1024 * 1024:
                buffer = buffer[-1024 * 1024 :]

    return False


if __name__ == "__main__":
    if len(sys.argv) < 3:
        print("Usage: python capture_snapshot.py <url> <output_path>", file=sys.stderr)
        sys.exit(1)

    url = sys.argv[1]
    output_path = sys.argv[2]

    ok = save_first_jpeg_frame(url, output_path)

    if ok:
        print(output_path)
        sys.exit(0)
    else:
        print("Failed to capture image", file=sys.stderr)
        sys.exit(2)
