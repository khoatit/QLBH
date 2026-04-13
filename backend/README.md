# QLBH Backend (Node.js + PostgreSQL/MySQL)

Backend mẫu để chạy với contract frontend đã thêm:

- `GET /phuocit/data`
- `PUT /phuocit/data` với body: `{ "data": { ... } }`

## 1) Cài đặt

```bash
cd backend
npm install
cp .env.example .env
```

## 2) Cấu hình DB

Trong `.env`:

- `DB_CLIENT=postgres` hoặc `DB_CLIENT=mysql`
- Điền thông tin kết nối tương ứng.
- `API_TOKEN` (optional): nếu có, frontend phải gửi `Authorization: Bearer <token>`.

## 3) Chạy server

```bash
npm run start
```

Server mặc định chạy ở `http://localhost:3000`.

## 4) Kết nối frontend

Trong app QLBH > Cài đặt > **Database thật (API)**:

- Bật đồng bộ DB thật
- API URL: `http://localhost:3000`
- Token: giá trị `API_TOKEN` nếu bạn có cấu hình

Sau đó bấm **Kiểm tra kết nối** và **Đồng bộ ngay**.

## Ghi chú schema

Backend tự tạo bảng `app_data` và lưu toàn bộ dữ liệu app trong 1 record `id = 1` dạng JSON/JSONB.
