require('dotenv').config();

const express = require('express');
const cors = require('cors');
const { createDbClient } = require('./db');

const app = express();
const port = Number(process.env.PORT || 3000);
const apiToken = (process.env.API_TOKEN || '').trim();

const db = createDbClient();

app.use(cors());
app.use(express.json({ limit: '10mb' }));

app.use((req, res, next) => {
  if (!apiToken) return next();

  const auth = req.headers.authorization || '';
  if (auth === `Bearer ${apiToken}`) {
    return next();
  }

  return res.status(401).json({ message: 'Unauthorized' });
});

app.get('/health', async (_req, res) => {
  try {
    await db.ping();
    return res.json({ ok: true, db: db.client });
  } catch (error) {
    return res.status(500).json({ ok: false, error: error.message });
  }
});

app.get('/phuocit/data', async (_req, res) => {
  try {
    const payload = await db.getData();
    return res.json({ data: payload.data, updatedAt: payload.updatedAt });
  } catch (error) {
    return res.status(500).json({ message: 'Cannot load data', error: error.message });
  }
});

app.put('/phuocit/data', async (req, res) => {
  const data = req.body?.data;

  if (!data || typeof data !== 'object' || Array.isArray(data)) {
    return res.status(400).json({ message: 'Invalid payload. Body must be { data: object }' });
  }

  try {
    await db.saveData(data);
    return res.json({ message: 'Saved successfully', updatedAt: new Date().toISOString() });
  } catch (error) {
    return res.status(500).json({ message: 'Cannot save data', error: error.message });
  }
});

async function start() {
  try {
    await db.initSchema();
    app.listen(port, () => {
      console.log(`QLBH backend is running on http://localhost:${port}`);
      console.log(`DB client: ${db.client}`);
    });
  } catch (error) {
    console.error('Failed to start backend:', error);
    process.exit(1);
  }
}

start();
