const { Pool } = require('pg');
const mysql = require('mysql2/promise');

function buildDefaultData() {
  return {
    customers: [],
    suppliers: [],
    products: [],
    categories: [],
    orders: [],
    sales: [],
    serviceRepairs: [],
    spareParts: [],
    technicians: []
  };
}

function createDbClient() {
  const client = (process.env.DB_CLIENT || 'postgres').toLowerCase();

  if (client === 'mysql') {
    const pool = mysql.createPool({
      host: process.env.MYSQL_HOST || '127.0.0.1',
      port: Number(process.env.MYSQL_PORT || 3306),
      user: process.env.MYSQL_USER || 'root',
      password: process.env.MYSQL_PASSWORD || '',
      database: process.env.MYSQL_DATABASE || 'qlbh',
      waitForConnections: true,
      connectionLimit: 10
    });

    return {
      client,
      async initSchema() {
        await pool.query(`
          CREATE TABLE IF NOT EXISTS app_data (
            id INT PRIMARY KEY,
            data JSON NOT NULL,
            updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
          )
        `);

        const [rows] = await pool.query('SELECT id FROM app_data WHERE id = 1 LIMIT 1');
        if (!rows.length) {
          await pool.query('INSERT INTO app_data (id, data) VALUES (1, ?)', [JSON.stringify(buildDefaultData())]);
        }
      },
      async getData() {
        const [rows] = await pool.query('SELECT data, updated_at FROM app_data WHERE id = 1 LIMIT 1');
        if (!rows.length) {
          return { data: buildDefaultData(), updatedAt: null };
        }

        return {
          data: typeof rows[0].data === 'string' ? JSON.parse(rows[0].data) : rows[0].data,
          updatedAt: rows[0].updated_at
        };
      },
      async saveData(payload) {
        await pool.query(
          'UPDATE app_data SET data = ?, updated_at = CURRENT_TIMESTAMP WHERE id = 1',
          [JSON.stringify(payload)]
        );
      },
      async ping() {
        await pool.query('SELECT 1');
      }
    };
  }

  const pool = new Pool({
    host: process.env.PGHOST || '127.0.0.1',
    port: Number(process.env.PGPORT || 5432),
    user: process.env.PGUSER || 'postgres',
    password: process.env.PGPASSWORD || '',
    database: process.env.PGDATABASE || 'qlbh'
  });

  return {
    client,
    async initSchema() {
      await pool.query(`
        CREATE TABLE IF NOT EXISTS app_data (
          id SMALLINT PRIMARY KEY,
          data JSONB NOT NULL,
          updated_at TIMESTAMPTZ NOT NULL DEFAULT NOW()
        )
      `);

      await pool.query(
        `INSERT INTO app_data (id, data)
         VALUES (1, $1::jsonb)
         ON CONFLICT (id) DO NOTHING`,
        [JSON.stringify(buildDefaultData())]
      );
    },
    async getData() {
      const result = await pool.query('SELECT data, updated_at FROM app_data WHERE id = 1 LIMIT 1');
      if (!result.rows.length) {
        return { data: buildDefaultData(), updatedAt: null };
      }

      return {
        data: result.rows[0].data,
        updatedAt: result.rows[0].updated_at
      };
    },
    async saveData(payload) {
      await pool.query(
        'UPDATE app_data SET data = $1::jsonb, updated_at = NOW() WHERE id = 1',
        [JSON.stringify(payload)]
      );
    },
    async ping() {
      await pool.query('SELECT 1');
    }
  };
}

module.exports = {
  createDbClient,
  buildDefaultData
};
