# Deploy smoke test — Ai trồng cây theme

Dùng tài liệu này ngay sau khi đưa theme `aitrongcay` lên site WordPress local/staging.

## 1) Kiểm tra nhanh trong 5 phút

### A. Theme
- [ ] theme `Ai trồng cây` đã active
- [ ] version đang thấy là `0.3.2`

### B. Trang public
- [ ] `/`
- [ ] `/cach-hoat-dong/`
- [ ] `/cho-que/`
- [ ] `/an-toan-thuc-pham/`
- [ ] `/chuyen-nha-nong/`
- [ ] `/faq/`
- [ ] `/dang-ky-tu-van/`
- [ ] `/onboarding/`
- [ ] `/dang-nhap/`
- [ ] `/portal/`

### C. Trang portal
- [ ] `/portal/dashboard/`
- [ ] `/portal/webcam/`
- [ ] `/portal/tinh-trang-vuon/`
- [ ] `/portal/nhat-ky-cham-soc/`
- [ ] `/portal/chat-luong-an-toan/`
- [ ] `/portal/tro-ly-ai/`
- [ ] `/portal/kho-nong-cu/`

## 2) Kiểm tra lead form

Tại `/dang-ky-tu-van/`:
- [ ] submit thiếu tên hoặc số điện thoại → báo lỗi hợp lệ
- [ ] submit đủ tên + số điện thoại → báo thành công
- [ ] admin có lead mới trong `Leads tư vấn`

Tại CTA homepage:
- [ ] submit thành công
- [ ] lead được lưu đúng
- [ ] `funnel_source` có giá trị phù hợp

## 3) Kiểm tra admin

- [ ] menu `Leads tư vấn` xuất hiện
- [ ] mở được từng lead
- [ ] thấy các meta cơ bản: tên, phone, email, goal, start_window, focus, funnel_stage, funnel_source

## 4) Nếu có lỗi route

Làm lần lượt:
1. Settings → Permalinks
2. Save lại `Post name`
3. refresh route bị lỗi
4. kiểm tra xem có page native cùng slug hay không

## 5) Nếu có lỗi form

Kiểm tra lần lượt:
1. theme đã activate đúng bản chưa
2. request đang post vào `admin-post.php` chưa
3. nonce có render trong form chưa
4. admin có quyền tạo post type `aitr_consultation` chưa
5. có plugin/security nào chặn `admin-post.php` không
