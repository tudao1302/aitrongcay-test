# Ai trồng cây WordPress page map

Checkpoint này chốt trước một bản map khá sát với site tĩnh hiện tại để lúc dựng WordPress local không phải nghĩ lại tên trang.

## Public pages

| Static file | WordPress slug | Gợi ý template | Ghi chú |
| --- | --- | --- | --- |
| `index.html` | `/` | `front-page.php` | Trang chủ public, giữ hook sang Chợ quê + portal |
| `how-it-works.html` | `/cach-hoat-dong/` | `page.php` | Có thể tách riêng template sau nếu cần timeline chi tiết |
| `packages.html` | `/cho-que/` | `page.php` hoặc CPT archive | Giữ đúng brand `Chợ quê` |
| `food-safety.html` | `/an-toan-thuc-pham/` | `page.php` | Trust page |
| `your-garden-story.html` | `/chuyen-nha-nong/` | `page.php` | Brand story / community |
| `faq.html` | `/faq/` | `page.php` | FAQ dùng accordion block hoặc pattern |
| `signup/register.html` | `/dang-ky-tu-van/` | `page.php` | Sau này gắn Gravity Forms / Fluent Forms |
| `signup/onboarding.html` | `/onboarding/` | `page.php` | Trang quy trình kích hoạt |
| `auth/login.html` | `/dang-nhap/` | `page.php` | Có thể chuyển thành handoff tới app/auth service |

## Portal pages

| Static file | WordPress slug | Trạng thái giai đoạn này |
| --- | --- | --- |
| `portal/dashboard.html` | `/portal/dashboard/` | Đã map slug |
| `portal/webcam.html` | `/portal/webcam/` | Đã map slug |
| `portal/status.html` | `/portal/tinh-trang-vuon/` | Đã map slug |
| `portal/care-log.html` | `/portal/nhat-ky-cham-soc/` | Đã map slug |
| `portal/quality-safety.html` | `/portal/chat-luong-an-toan/` | Đã map slug |
| `portal/ai-gardener.html` | `/portal/tro-ly-ai/` | Đã map slug |
| `portal/tools-warehouse.html` | `/portal/kho-nong-cu/` | Đã map slug, giữ brand `Kho nông cụ` |

## Khuyến nghị nhập nội dung theo thứ tự

1. Trang chủ (`front-page.php`) — đã có skeleton thật.
2. `Chợ quê` — ảnh hưởng chuyển đổi nhiều nhất.
3. `Cách hoạt động` + `An toàn thực phẩm` — chốt niềm tin và cơ chế bán hàng.
4. `Chuyện nhà nông` + `FAQ`.
5. `Đăng ký tư vấn` — gắn form thật, giữ form vòng đầu thật ngắn (không hỏi `Quy mô gia đình` / `Gói quan tâm`).
6. Portal pages — quyết định tách theme hay app shell riêng ở vòng sau.

## Trạng thái checkpoint hiện tại

- route public và portal đã có virtual fallback
- CTA công khai đã được gom nhiều hơn về `dang-ky-tu-van`
- bộ starter content native đầu tiên đã có trong `docs/starter-content/`
- phần còn thiếu để coi là native hơn hẳn: import content thật vào WordPress admin và thay `data-fake-submit` bằng form thật
