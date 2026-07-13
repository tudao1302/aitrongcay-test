require('dotenv').config();
const admin = require('firebase-admin');
const axios = require('axios');

// 1. Tải file cấu hình Service Account của Firebase
// BẠN CẦN VÀO FIREBASE CONSOLE -> PROJECT SETTINGS -> SERVICE ACCOUNTS
// -> GENERATE NEW PRIVATE KEY VÀ LƯU VÀO FOLDER NÀY VỚI TÊN: serviceAccountKey.json
try {
  const serviceAccount = require('./serviceAccountKey.json');
  
  // KHAI BÁO URL DATABSE CỦA BẠN (VD: https://aitrongcay-robot-default-rtdb.asia-southeast1.firebasedatabase.app)
  const DATABASE_URL = process.env.FIREBASE_DB_URL || 'https://aitrongcay-robot-default-rtdb.asia-southeast1.firebasedatabase.app'; // Lấy từ biến môi trường
  
  admin.initializeApp({
    credential: admin.credential.cert(serviceAccount),
    databaseURL: DATABASE_URL
  });
  
} catch (e) {
  console.error("❌ LỖI: Không tìm thấy file serviceAccountKey.json!");
  console.error("Vui lòng tải file Private Key từ Firebase Console và để vào thư mục robot-listener này.");
  process.exit(1);
}

const db = admin.database();
const commandRef = db.ref('robot/camera_command');

// URL API Backend (WordPress)
// Ưu tiên lấy từ biến môi trường (.env), nếu không có thì fallback về localhost
const WP_API_URL = process.env.WP_API_URL || 'http://localhost/aitrongcay/wp-json/aitrongcay/v1/robot/capture';

console.log("🚀 [AiTrongCay Robot Listener] Đã khởi động! Đang lắng nghe Firebase 24/24...");

// 2. Lắng nghe thay đổi trạng thái từ Robot
commandRef.on('value', async (snapshot) => {
  const data = snapshot.val();
  
  if (!data) return;

  // Chỉ kích hoạt khi status là 'arrived' (Robot báo đã đến nơi)
  if (data.status === 'arrived') {
    const garden_key = data.garden_key;
    const pot_code = data.pot_code;

    console.log(`\n=========================================`);
    console.log(`🎯 [Phát hiện] Robot báo ĐÃ ĐẾN tọa độ: ${data.command}`);

    if (!garden_key || !pot_code) {
      console.error(`❌ [Lỗi] Tín hiệu Firebase thiếu 'garden_key' hoặc 'pot_code'. Không biết chụp cho khay nào!`);
      // Đổi thành error để robot không bị kẹt
      await commandRef.update({ status: 'error_missing_params' });
      return;
    }

    let success = false;
    let attempts = 0;
    const maxRetries = 3;

    while (attempts < maxRetries && !success) {
      attempts++;
      console.log(`📸 [Lần thử ${attempts}/${maxRetries}] Đang gửi lệnh cắt ảnh cho Khay: ${pot_code} (Vườn: ${garden_key})...`);
      
      try {
        // 3. Gọi WordPress API để cắt ảnh và chạy AI
        const response = await axios.post(WP_API_URL, {
          garden_key: garden_key,
          pot_code: pot_code,
          command: data.command || ''
        }, {
          headers: { 'Content-Type': 'application/json' },
          timeout: 10000 // Giới hạn 10s để không bị treo request
        });

        if (response.data && response.data.status === 'success') {
          console.log(`✅ [Thành công] WordPress báo đã lưu ảnh! URL: ${response.data.url}`);
          
          // 4. Cập nhật lại Firebase thành 'captured' để báo robot đi tiếp
          console.log(`🤖 Đang báo lại cho Robot: status = 'captured' ...`);
          await commandRef.update({ status: 'captured' });
          success = true;
        } else {
          throw new Error('API không trả về status success');
        }

      } catch (error) {
        console.error(`❌ [Lỗi API] WordPress phản hồi lỗi:`);
        if (error.response) {
          console.error(error.response.data);
        } else {
          console.error(error.message);
        }
        
        if (attempts < maxRetries) {
          console.log(`⏳ Đang đợi 2 giây để thử lại...`);
          await new Promise(resolve => setTimeout(resolve, 2000));
        } else {
          console.error(`🚨 Đã thử 3 lần nhưng vẫn thất bại. Cập nhật Firebase thành 'capture_failed'.`);
          // Báo lỗi lên Firebase để robot biết
          await commandRef.update({ status: 'capture_failed' });
        }
      }
    }
  }
});
