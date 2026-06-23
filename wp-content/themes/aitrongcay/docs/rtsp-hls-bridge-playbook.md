# RTSP → HLS bridge playbook for Ai trồng cây

Mục tiêu: lấy luồng RTSP nội bộ trong LAN rồi phát ra HLS để dashboard web nhúng được ổn định.

## 1) Kiến trúc khuyến nghị

- Camera IP trong LAN:
  - `rtsp://admin:Firesmart123@192.168.100.205/h264/ch1/main/av_stream`
- Một máy bridge nằm cùng mạng LAN với camera:
  - mini PC / Raspberry Pi / NUC / máy Windows luôn bật
- Máy bridge chạy ffmpeg để chuyển:
  - `RTSP -> HLS`
- Web `aitrongcay.com` chỉ cần nhúng URL HLS:
  - ví dụ `https://stream.aitrongcay.com/tung-01/P-001/index.m3u8`

## 2) Vì sao chọn HLS trước

- Browser chạy tốt qua `video` + `hls.js`
- Triển khai nhanh, ổn định, dễ debug
- Hợp với dashboard cần xem live ổn định hơn là độ trễ cực thấp

Nếu sau này cần độ trễ thấp hơn nhiều, có thể nâng sang WebRTC.

## 3) Yêu cầu tối thiểu cho máy bridge

- Nhìn thấy camera LAN `192.168.100.205:554`
- Có `ffmpeg`
- Có web server tĩnh hoặc reverse proxy để public thư mục HLS
- Nên có systemd / pm2 / NSSM để auto restart tiến trình

## 4) Lệnh ffmpeg mẫu

Ví dụ tạo stream HLS cho khay `P-001`:

```bash
mkdir -p /srv/aitrongcay-hls/tung-01/P-001

ffmpeg -rtsp_transport tcp \
  -i "rtsp://admin:Firesmart123@192.168.100.205/h264/ch1/main/av_stream" \
  -an \
  -c:v copy \
  -f hls \
  -hls_time 2 \
  -hls_list_size 6 \
  -hls_flags delete_segments+append_list+omit_endlist+independent_segments \
  -hls_segment_filename "/srv/aitrongcay-hls/tung-01/P-001/seg_%05d.ts" \
  "/srv/aitrongcay-hls/tung-01/P-001/index.m3u8"
```

## 5) Nếu camera/codec không tương thích tốt

Dùng transcode nhẹ:

```bash
ffmpeg -rtsp_transport tcp \
  -i "rtsp://admin:Firesmart123@192.168.100.205/h264/ch1/main/av_stream" \
  -an \
  -c:v libx264 \
  -preset veryfast \
  -tune zerolatency \
  -pix_fmt yuv420p \
  -g 50 \
  -sc_threshold 0 \
  -f hls \
  -hls_time 2 \
  -hls_list_size 6 \
  -hls_flags delete_segments+append_list+omit_endlist+independent_segments \
  -hls_segment_filename "/srv/aitrongcay-hls/tung-01/P-001/seg_%05d.ts" \
  "/srv/aitrongcay-hls/tung-01/P-001/index.m3u8"
```

## 6) Public HLS ra internet

Khuyến nghị domain riêng:

- `https://stream.aitrongcay.com/tung-01/P-001/index.m3u8`

Nginx mẫu:

```nginx
server {
    listen 443 ssl http2;
    server_name stream.aitrongcay.com;

    root /srv/aitrongcay-hls;

    location / {
        add_header Cache-Control no-cache;
        add_header Access-Control-Allow-Origin *;
        add_header Access-Control-Allow-Headers *;
        types {
            application/vnd.apple.mpegurl m3u8;
            video/mp2t ts;
        }
        try_files $uri $uri/ =404;
    }
}
```

## 7) Khuyến nghị bảo mật

Không public thẳng RTSP camera.

Nên:
- giữ camera trong LAN
- bridge là lớp trung gian duy nhất
- nếu stream riêng tư, bảo vệ HLS bằng một trong các cách:
  - signed URL ngắn hạn
  - basic auth tại nginx
  - reverse proxy chỉ cho user đã đăng nhập

## 8) Mapping cho dashboard khay cây

Giai đoạn đầu có thể map cứng:

- `tung-01 / P-001` -> `https://stream.aitrongcay.com/tung-01/P-001/index.m3u8`

Sau đó chuyển sang DB / option admin.

## 9) Smoke test

### Từ máy bridge

```bash
ffprobe "rtsp://admin:Firesmart123@192.168.100.205/h264/ch1/main/av_stream"
```

### Từ browser/public side

- mở `https://stream.aitrongcay.com/tung-01/P-001/index.m3u8`
- kiểm tra response 200
- kiểm tra file `.ts` sinh đều
- kiểm tra dashboard phát được video

## 10) Next step cho theme

Theme hiện đã được chuẩn bị để nhận URL HLS theo khay.
Khi có URL thật, chỉ cần nối vào map stream là dashboard sẽ phát thay ảnh tĩnh.
