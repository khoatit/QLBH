# QLBH Backend (Node.js + PostgreSQL/MySQL)

Backend mẫu để chạy với contract frontend đã thêm:

- `GET /maytinhbk/data`
- `PUT /maytinhbk/data` với body: `{ "data": { ... } }`
- Tương thích thêm prefix reverse-proxy: `GET/PUT /api/maytinhbk/data`

---

## ⚡ Checklist 5 phút: cài từ đầu tới chạy được

> Chọn **một** môi trường: PostgreSQL **hoặc** MySQL.
>
> Điều kiện cần: máy đã có `node` + `npm` + `docker`.

### A. PostgreSQL (khuyên dùng)

1. Mở terminal tại thư mục gốc project.
2. Copy-paste **một lần** khối lệnh dưới đây:

```bash
cd backend && \
docker rm -f qlbh-postgres >/dev/null 2>&1 || true && \
docker run -d --name qlbh-postgres \
  -e POSTGRES_USER=postgres \
  -e POSTGRES_PASSWORD=postgres \
  -e POSTGRES_DB=qlbh \
  -p 5432:5432 postgres:16 && \
npm install && \
cp -n .env.example .env && \
cat > .env <<'EOF'
PORT=3000
API_TOKEN=
DB_CLIENT=postgres
PGHOST=127.0.0.1
PGPORT=5432
PGUSER=postgres
PGPASSWORD=postgres
PGDATABASE=qlbh
MYSQL_HOST=127.0.0.1
MYSQL_PORT=3306
MYSQL_USER=root
MYSQL_PASSWORD=123456
MYSQL_DATABASE=qlbh
EOF
npm run start
```

3. Nếu thấy server chạy ở `http://localhost:3000` là xong.

### B. MySQL

1. Mở terminal tại thư mục gốc project.
2. Copy-paste **một lần** khối lệnh dưới đây:

```bash
cd backend && \
docker rm -f qlbh-mysql >/dev/null 2>&1 || true && \
docker run -d --name qlbh-mysql \
  -e MYSQL_ROOT_PASSWORD=123456 \
  -e MYSQL_DATABASE=qlbh \
  -p 3306:3306 mysql:8 && \
npm install && \
cp -n .env.example .env && \
cat > .env <<'EOF'
PORT=3000
API_TOKEN=
DB_CLIENT=mysql
PGHOST=127.0.0.1
PGPORT=5432
PGUSER=postgres
PGPASSWORD=postgres
PGDATABASE=qlbh
MYSQL_HOST=127.0.0.1
MYSQL_PORT=3306
MYSQL_USER=root
MYSQL_PASSWORD=123456
MYSQL_DATABASE=qlbh
EOF
npm run start
```

3. Nếu thấy server chạy ở `http://localhost:3000` là xong.

### C. Kết nối frontend (áp dụng cho cả 2 môi trường)

1. Mở app `index.html`.
2. Vào **Cài đặt → Database thật (API)**.
3. Cấu hình:
   - Bật đồng bộ DB thật.
   - API URL: `http://localhost:3000` (hoặc nhập luôn `http://localhost:3000/maytinhbk/data`)
   - Token: để trống nếu `API_TOKEN=` trong `.env`.
4. Bấm **Kiểm tra kết nối** rồi **Đồng bộ ngay**.

### D. Kiểm tra nhanh bằng curl

```bash
curl http://localhost:3000/health
curl http://localhost:3000/maytinhbk/data
```

---

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
- `CORS_ORIGIN` (optional): domain frontend được phép gọi API. Mặc định `*`. Ví dụ production nên đặt `https://app.maytinhbk.vn`.

## 3) Chạy server

```bash
npm run start
```

Server mặc định chạy ở `http://localhost:3000`.
Backend hỗ trợ cả 2 route health:
- `GET /health`
- `GET /api/health`

## 4) Kết nối frontend

Trong app QLBH > Cài đặt > **Database thật (API)**:

- Bật đồng bộ DB thật
- API URL: `http://localhost:3000`
- Token: giá trị `API_TOKEN` nếu bạn có cấu hình

Sau đó bấm **Kiểm tra kết nối** và **Đồng bộ ngay**.

## Ghi chú schema

Backend tự tạo bảng `app_data` và lưu toàn bộ dữ liệu app trong 1 record `id = 1` dạng JSON/JSONB.

## Lỗi thường gặp: frontend báo sai URL/TOKEN nhưng backend vẫn sống

- Nếu frontend chạy khác domain backend và bạn có dùng `API_TOKEN`, trình duyệt sẽ gửi preflight `OPTIONS`.
- Bản backend hiện tại đã xử lý `OPTIONS` + header `Authorization`; chỉ cần đảm bảo reverse proxy không chặn `OPTIONS`.
- Test nhanh trên server:

```bash
curl -i -X OPTIONS https://your-domain/maytinhbk/data \
  -H "Origin: https://app.maytinhbk.vn" \
  -H "Access-Control-Request-Method: GET" \
  -H "Access-Control-Request-Headers: authorization,content-type"
```
