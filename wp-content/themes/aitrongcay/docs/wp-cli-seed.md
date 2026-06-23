# WP-CLI seed nhanh cho theme Ai trồng cây

Nếu đang có một site WordPress local và đã active theme `aitrongcay`, có thể seed nhanh các trang cơ bản bằng WP-CLI.

## 1) Tạo trang chủ + các slug chính

```bash
wp post create --post_type=page --post_status=publish --post_title='Trang chủ'
wp post create --post_type=page --post_status=publish --post_title='Cách hoạt động' --post_name='cach-hoat-dong'
wp post create --post_type=page --post_status=publish --post_title='Chợ quê' --post_name='cho-que'
wp post create --post_type=page --post_status=publish --post_title='An toàn thực phẩm' --post_name='an-toan-thuc-pham'
wp post create --post_type=page --post_status=publish --post_title='Chuyện nhà nông' --post_name='chuyen-nha-nong'
wp post create --post_type=page --post_status=publish --post_title='FAQ' --post_name='faq'
wp post create --post_type=page --post_status=publish --post_title='Đăng ký tư vấn' --post_name='dang-ky-tu-van'
wp post create --post_type=page --post_status=publish --post_title='Onboarding' --post_name='onboarding'
wp post create --post_type=page --post_status=publish --post_title='Đăng nhập' --post_name='dang-nhap'
wp post create --post_type=page --post_status=publish --post_title='Portal' --post_name='portal'
```

## 1.1) Bơm starter content native đầu tiên

Trong repo đã có sẵn nội dung importable tại `docs/starter-content/`.

Ví dụ:

```bash
wp post update $(wp post list --post_type=page --name=cach-hoat-dong --field=ID) --post_content="$(cat wp-content/themes/aitrongcay/docs/starter-content/cach-hoat-dong.html)"
wp post update $(wp post list --post_type=page --name=cho-que --field=ID) --post_content="$(cat wp-content/themes/aitrongcay/docs/starter-content/cho-que.html)"
wp post update $(wp post list --post_type=page --name=an-toan-thuc-pham --field=ID) --post_content="$(cat wp-content/themes/aitrongcay/docs/starter-content/an-toan-thuc-pham.html)"
wp post update $(wp post list --post_type=page --name=chuyen-nha-nong --field=ID) --post_content="$(cat wp-content/themes/aitrongcay/docs/starter-content/chuyen-nha-nong.html)"
wp post update $(wp post list --post_type=page --name=faq --field=ID) --post_content="$(cat wp-content/themes/aitrongcay/docs/starter-content/faq.html)"
wp post update $(wp post list --post_type=page --name=dang-ky-tu-van --field=ID) --post_content="$(cat wp-content/themes/aitrongcay/docs/starter-content/dang-ky-tu-van.html)"
```

## 2) Đặt trang chủ tĩnh

Lấy ID của `Trang chủ` rồi set:

```bash
wp option update show_on_front page
wp option update page_on_front <HOME_PAGE_ID>
```

## 3) Tạo menu primary nhanh

```bash
wp menu create "Primary Menu"
wp menu item add-custom "Primary Menu" "Cách hoạt động" "/cach-hoat-dong/"
wp menu item add-custom "Primary Menu" "Chợ quê" "/cho-que/"
wp menu item add-custom "Primary Menu" "An toàn thực phẩm" "/an-toan-thuc-pham/"
wp menu item add-custom "Primary Menu" "Chuyện nhà nông" "/chuyen-nha-nong/"
wp menu item add-custom "Primary Menu" "FAQ" "/faq/"
wp menu location assign "Primary Menu" primary
```

## 4) Ghi chú kiến trúc hiện tại

- Nếu **chưa tạo page thật** trong admin, theme sẽ dùng **virtual page fallback** để site vẫn chạy.
- Nếu **đã tạo page thật** đúng slug, theme sẽ ưu tiên page WordPress native.
- Form `Đăng ký tư vấn` hiện đã có bản WordPress-native, gửi vào `admin-post.php` và lưu về post type nội bộ `Leads tư vấn`.
- Cách này giúp vừa chạy demo nhanh, vừa chuyển dần sang CMS thật mà không phải sửa route lại từ đầu.
