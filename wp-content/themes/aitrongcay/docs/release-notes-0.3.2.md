# Release notes — Ai trồng cây theme 0.3.2

## Điểm mới chính

### 1) WordPress foundation rõ hơn
- ưu tiên page/menu native nếu đã được tạo trong WordPress
- giữ virtual fallback để demo/site không gãy trong giai đoạn seed

### 2) Portal shell được mở rộng
- có đầy đủ các route portal chính
- có sidebar + active state + layout portal rõ hơn

### 3) Starter content native đầu tiên
Đã có bộ starter content importable cho:
- `cach-hoat-dong`
- `cho-que`
- `an-toan-thuc-pham`
- `dang-ky-tu-van`
- `chuyen-nha-nong`
- `faq`

### 4) Funnel `Đăng ký tư vấn` được gom lại
- CTA công khai dồn về `dang-ky-tu-van`
- vòng đầu ngắn gọn hơn
- không hỏi sớm `Quy mô gia đình` hay `Gói quan tâm`

### 5) Form WordPress-native đầu tiên
- submit vào `admin-post.php`
- validate cơ bản
- lưu bản ghi nội bộ vào `Leads tư vấn`
- có notice success/error

## Mục tiêu sử dụng bản 0.3.2
- dùng để đưa lên local/staging WordPress
- import starter content
- smoke test route + funnel
- chốt hoàn tất hướng 1
