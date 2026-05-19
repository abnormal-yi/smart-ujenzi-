import express from 'express';
import { createServer as createViteServer } from 'vite';
import Database from 'better-sqlite3';
import cors from 'cors';
import bcrypt from 'bcryptjs';
import jwt from 'jsonwebtoken';
import path from 'path';

// Setup SQLite database
const db = new Database(':memory:');
console.log('Database connected');
initDb();

// Since we are using an in-memory DB for this AI Studio environment, we initialize the schema on startup.
// In a real production deployment, you would use persistent storage (e.g. a file-based SQLite db or MySQL).
function initDb() {
  // 1. Users
  db.exec(`CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT UNIQUE NOT NULL,
    password TEXT NOT NULL,
    role TEXT NOT NULL
  )`);

  // 2. Projects
  db.exec(`CREATE TABLE IF NOT EXISTS projects (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    description TEXT,
    status TEXT DEFAULT 'Pending',
    manager_id INTEGER,
    start_date TEXT,
    end_date TEXT,
    FOREIGN KEY(manager_id) REFERENCES users(id)
  )`);

  // 3. Tasks
  db.exec(`CREATE TABLE IF NOT EXISTS tasks (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    project_id INTEGER NOT NULL,
    name TEXT NOT NULL,
    description TEXT,
    status TEXT DEFAULT 'Not Started',
    supervisor_id INTEGER,
    deadline TEXT,
    FOREIGN KEY(project_id) REFERENCES projects(id),
    FOREIGN KEY(supervisor_id) REFERENCES users(id)
  )`);

  // 4. Resources
  db.exec(`CREATE TABLE IF NOT EXISTS resources (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    type TEXT NOT NULL, -- 'labor' or 'equipment'
    name TEXT NOT NULL,
    details TEXT
  )`);

  // 5. Materials
  db.exec(`CREATE TABLE IF NOT EXISTS materials (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    quantity INTEGER DEFAULT 0,
    unit TEXT NOT NULL,
    low_stock_threshold INTEGER DEFAULT 10
  )`);

  // 6. Allocations (Assigning resources/materials to projects)
  db.exec(`CREATE TABLE IF NOT EXISTS allocations (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    project_id INTEGER NOT NULL,
    type TEXT NOT NULL, -- 'resource' or 'material'
    item_id INTEGER NOT NULL,
    quantity INTEGER DEFAULT 1
  )`);

  // 7. Notifications
  db.exec(`CREATE TABLE IF NOT EXISTS notifications (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    message TEXT NOT NULL,
    is_read INTEGER DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
  )`);

  // 8. Messages
  db.exec(`CREATE TABLE IF NOT EXISTS messages (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    project_id INTEGER NOT NULL,
    sender_id INTEGER NOT NULL,
    message TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY(project_id) REFERENCES projects(id),
    FOREIGN KEY(sender_id) REFERENCES users(id)
  )`);

  // Seed Initial Admin User
  const adminPassword = bcrypt.hashSync('admin123', 10);
  const managerPassword = bcrypt.hashSync('pass123', 10);
  db.prepare(`INSERT OR IGNORE INTO users (id, name, email, password, role) VALUES (1, 'Admin', 'admin@example.com', ?, 'admin')`).run(adminPassword);

  const seedCount = (db.prepare('SELECT COUNT(*) as c FROM users').get() as any).c;
  if (seedCount === 1) { // Only admin exists
    console.log('Seeding initial data...');
    db.prepare(`INSERT OR IGNORE INTO users (id, name, email, password, role) VALUES (2, 'Stephen Massawe', 'steve@example.com', ?, 'manager')`).run(managerPassword);
    db.prepare(`INSERT OR IGNORE INTO users (id, name, email, password, role) VALUES (3, 'Teleza Mkomwa', 'teleza@example.com', ?, 'supervisor')`).run(managerPassword);
    db.prepare(`INSERT OR IGNORE INTO users (id, name, email, password, role) VALUES (4, 'Ali Fundi', 'ali@example.com', ?, 'supervisor')`).run(managerPassword);
    db.prepare(`INSERT OR IGNORE INTO users (id, name, email, password, role) VALUES (5, 'Zainab Contractor', 'zainab@example.com', ?, 'manager')`).run(managerPassword);

    db.prepare(`INSERT INTO projects (id, name, description, status, manager_id, start_date, end_date) VALUES (1, 'IAA New Library', 'Construction of a modern library', 'Ongoing', 2, '2025-01-01', '2026-05-01')`).run();
    db.prepare(`INSERT INTO projects (id, name, description, status, manager_id, start_date, end_date) VALUES (2, 'Hostel Block B', 'Expansion of students hostel', 'Pending', 2, '2025-08-01', '2026-12-01')`).run();
    db.prepare(`INSERT INTO projects (id, name, description, status, manager_id, start_date, end_date) VALUES (3, 'City Mall Extension', 'Adding a new wing to City Mall', 'In Progress', 5, '2025-10-10', '2027-02-15')`).run();
    db.prepare(`INSERT INTO projects (id, name, description, status, manager_id, start_date, end_date) VALUES (4, 'Mwanza Hospital Block', 'New maternity ward', 'Completed', 2, '2023-01-05', '2024-12-20')`).run();

    db.prepare(`INSERT INTO tasks (project_id, name, description, status, supervisor_id, deadline) VALUES (1, 'Foundation Laying', 'Excavation and foundation laying', 'Completed', 3, '2025-02-15')`).run();
    db.prepare(`INSERT INTO tasks (project_id, name, description, status, supervisor_id, deadline) VALUES (1, 'Brickwork Phase 1', 'Ground floor brickwork', 'In Progress', 3, '2025-06-30')`).run();
    db.prepare(`INSERT INTO tasks (project_id, name, description, status, supervisor_id, deadline) VALUES (2, 'Site Clearance', 'Clearing the bush and trees', 'Completed', 4, '2025-08-15')`).run();
    db.prepare(`INSERT INTO tasks (project_id, name, description, status, supervisor_id, deadline) VALUES (2, 'Foundation Excavation', 'Digging trenches', 'Not Started', 4, '2025-09-01')`).run();
    db.prepare(`INSERT INTO tasks (project_id, name, description, status, supervisor_id, deadline) VALUES (3, 'Structural Framing', 'Putting up steel columns', 'In Progress', 3, '2026-01-20')`).run();

    db.prepare(`INSERT INTO resources (id, type, name, details) VALUES (1, 'labor', 'Fundi Juma (Mason)', 'Senior Mason, 10 yrs exp')`).run();
    db.prepare(`INSERT INTO resources (id, type, name, details) VALUES (2, 'labor', 'Fundi Asha (Electrician)', 'Electrical installations')`).run();
    db.prepare(`INSERT INTO resources (id, type, name, details) VALUES (3, 'labor', 'Fundi Baraka (Plumber)', 'Plumbing and piping')`).run();
    db.prepare(`INSERT INTO resources (id, type, name, details) VALUES (4, 'equipment', 'Concrete Mixer', 'Small mixer, 500L')`).run();
    db.prepare(`INSERT INTO resources (id, type, name, details) VALUES (5, 'equipment', 'Excavator (JCB)', 'Heavy excavation')`).run();
    db.prepare(`INSERT INTO resources (id, type, name, details) VALUES (6, 'equipment', 'Crane (Tower)', 'Tower crane for City Mall')`).run();
    db.prepare(`INSERT INTO resources (id, type, name, details) VALUES (7, 'labor', 'Fundi Daud (Welder)', 'Certified Welder')`).run();

    db.prepare(`INSERT INTO materials (id, name, quantity, unit, low_stock_threshold) VALUES (1, 'Cement (Simba)', 150, 'Bags', 20)`).run();
    db.prepare(`INSERT INTO materials (id, name, quantity, unit, low_stock_threshold) VALUES (2, 'Bricks', 5000, 'Pieces', 1000)`).run();
    db.prepare(`INSERT INTO materials (id, name, quantity, unit, low_stock_threshold) VALUES (3, 'Iron Bars (Y12)', 300, 'Pieces', 50)`).run();
    db.prepare(`INSERT INTO materials (id, name, quantity, unit, low_stock_threshold) VALUES (4, 'Sand', 25, 'Tons', 10)`).run();
    db.prepare(`INSERT INTO materials (id, name, quantity, unit, low_stock_threshold) VALUES (5, 'Gravel', 18, 'Tons', 15)`).run();
    db.prepare(`INSERT INTO materials (id, name, quantity, unit, low_stock_threshold) VALUES (6, 'Paint (White)', 8, 'Buckets', 10)`).run();

    db.prepare(`INSERT INTO allocations (project_id, type, item_id, quantity) VALUES (1, 'resource', 1, 1)`).run();
    db.prepare(`INSERT INTO allocations (project_id, type, item_id, quantity) VALUES (1, 'resource', 5, 1)`).run();
    db.prepare(`INSERT INTO allocations (project_id, type, item_id, quantity) VALUES (3, 'resource', 6, 1)`).run();
    db.prepare(`INSERT INTO allocations (project_id, type, item_id, quantity) VALUES (3, 'resource', 7, 1)`).run();
    db.prepare(`INSERT INTO allocations (project_id, type, item_id, quantity) VALUES (1, 'material', 1, 50)`).run();
    db.prepare(`INSERT INTO allocations (project_id, type, item_id, quantity) VALUES (1, 'material', 2, 2000)`).run();
    
    // Seed dummy notification
    db.prepare(`INSERT INTO notifications (user_id, message, is_read) VALUES (1, 'Low stock alert: Cement (Simba) is running low.', 0)`).run();
    db.prepare(`INSERT INTO notifications (user_id, message, is_read) VALUES (1, 'Task Foundation Laying has been marked as Completed.', 0)`).run();
    db.prepare(`INSERT INTO notifications (user_id, message, is_read) VALUES (1, 'Low stock alert: Paint (White) is running low.', 0)`).run();
    db.prepare(`INSERT INTO notifications (user_id, message, is_read) VALUES (2, 'New task added: Structural Framing', 0)`).run();
    db.prepare(`INSERT INTO notifications (user_id, message, is_read) VALUES (3, 'You have been assigned to Foundation Laying', 1)`).run();

    // Messages
    db.prepare(`INSERT INTO messages (project_id, sender_id, message, created_at) VALUES (1, 2, 'Has the cement arrived on site?', '2026-05-01 10:00:00')`).run();
    db.prepare(`INSERT INTO messages (project_id, sender_id, message, created_at) VALUES (1, 3, 'Yes sir, we just received 150 bags.', '2026-05-01 10:15:00')`).run();
    db.prepare(`INSERT INTO messages (project_id, sender_id, message, created_at) VALUES (1, 2, 'Great, start the foundation laying immediately.', '2026-05-01 10:20:00')`).run();
    db.prepare(`INSERT INTO messages (project_id, sender_id, message, created_at) VALUES (2, 4, 'Site clearance is done. Ready for excavation.', '2025-08-15 16:00:00')`).run();
  }
}

function runQuery(sql: string, params: any[] = []): Promise<any[]> {
  return new Promise((resolve) => {
    resolve(db.prepare(sql).all(params));
  });
}

function executeQuery(sql: string, params: any[] = []): Promise<{ id: number, changes: number }> {
  return new Promise((resolve) => {
    const result = db.prepare(sql).run(params);
    resolve({ id: result.lastInsertRowid as number, changes: result.changes });
  });
}

const JWT_SECRET = 'super_secret_construction_key';

// Middleware to verify JWT token
const authenticateToken = (req: any, res: any, next: any) => {
  const authHeader = req.headers['authorization'];
  const token = authHeader && authHeader.split(' ')[1];
  if (token == null) return res.sendStatus(401);

  jwt.verify(token, JWT_SECRET, (err: any, user: any) => {
    if (err) return res.sendStatus(403);
    req.user = user;
    next();
  });
};

async function startServer() {
  const app = express();
  const PORT = 3000;

  app.use(cors());
  app.use(express.json());

  // --- API ROUTES ---

  // Auth: Login
  app.post('/api/auth/login', async (req, res) => {
    const { email, password } = req.body;
    try {
      const users = await runQuery('SELECT * FROM users WHERE email = ?', [email]);
      const user = users[0];
      if (!user) {
        return res.status(400).json({ error: 'User not found' });
      }
      const validPassword = await bcrypt.compare(password, user.password);
      if (!validPassword) {
        return res.status(400).json({ error: 'Invalid password' });
      }
      const token = jwt.sign({ id: user.id, role: user.role, name: user.name }, JWT_SECRET, { expiresIn: '1h' });
      res.json({ token, user: { id: user.id, name: user.name, role: user.role } });
    } catch (err: any) {
      res.status(500).json({ error: err.message });
    }
  });

  // Auth: Get Current User
  app.get('/api/auth/me', authenticateToken, (req: any, res) => {
    res.json({ user: req.user });
  });

  // Users: List users (for assignments)
  app.get('/api/users', authenticateToken, async (req, res) => {
    try {
      const users = await runQuery('SELECT id, name, email, role FROM users');
      res.json(users);
    } catch (err: any) {
      res.status(500).json({ error: err.message });
    }
  });

  // Dashboard Data
  app.get('/api/dashboard', authenticateToken, async (req, res) => {
    try {
      const projectsTotal = await runQuery('SELECT COUNT(*) as c FROM projects');
      const projectsStats = await runQuery('SELECT status, COUNT(*) as c FROM projects GROUP BY status');
      const tasksTotal = await runQuery('SELECT COUNT(*) as c FROM tasks');
      const tasksStats = await runQuery('SELECT status, COUNT(*) as c FROM tasks GROUP BY status');
      
      res.json({
        totalProjects: projectsTotal[0].c,
        projectsByStatus: projectsStats.map(r => ({ name: r.status, value: r.c })),
        totalTasks: tasksTotal[0].c,
        tasksByStatus: tasksStats.map(r => ({ name: r.status, value: r.c }))
      });
    } catch (err: any) {
      res.status(500).json({ error: err.message });
    }
  });

  // Projects API
  app.get('/api/projects', authenticateToken, async (req, res) => {
    try {
      const projects = await runQuery(`
        SELECT p.*, u.name as manager_name 
        FROM projects p 
        LEFT JOIN users u ON p.manager_id = u.id
      `);
      res.json(projects);
    } catch (err: any) {
      res.status(500).json({ error: err.message });
    }
  });

  app.post('/api/projects', authenticateToken, async (req: any, res) => {
    const { name, description, manager_id, start_date, end_date } = req.body;
    try {
      const result = await executeQuery(
        'INSERT INTO projects (name, description, manager_id, start_date, end_date) VALUES (?, ?, ?, ?, ?)',
        [name, description, manager_id, start_date, end_date]
      );
      res.json({ id: result.id });
    } catch (err: any) {
      res.status(500).json({ error: err.message });
    }
  });

  app.put('/api/projects/:id/status', authenticateToken, async (req, res) => {
    const { status } = req.body;
    try {
      await executeQuery('UPDATE projects SET status = ? WHERE id = ?', [status, req.params.id]);
      res.json({ success: true });
    } catch (err: any) {
      res.status(500).json({ error: err.message });
    }
  });

  // Tasks API
  app.get('/api/tasks', authenticateToken, async (req, res) => {
    try {
      const tasks = await runQuery(`
        SELECT t.*, p.name as project_name, u.name as supervisor_name 
        FROM tasks t 
        LEFT JOIN projects p ON t.project_id = p.id
        LEFT JOIN users u ON t.supervisor_id = u.id
      `);
      res.json(tasks);
    } catch (err: any) {
      res.status(500).json({ error: err.message });
    }
  });

  app.post('/api/tasks', authenticateToken, async (req: any, res) => {
    const { project_id, name, description, supervisor_id, deadline } = req.body;
    try {
      const result = await executeQuery(
        'INSERT INTO tasks (project_id, name, description, supervisor_id, deadline) VALUES (?, ?, ?, ?, ?)',
        [project_id, name, description, supervisor_id, deadline]
      );
      res.json({ id: result.id });
    } catch (err: any) {
      res.status(500).json({ error: err.message });
    }
  });

  app.put('/api/tasks/:id/status', authenticateToken, async (req, res) => {
    const { status } = req.body;
    try {
      await executeQuery('UPDATE tasks SET status = ? WHERE id = ?', [status, req.params.id]);
      
      // Auto-notification
      const taskArr = await runQuery('SELECT * FROM tasks WHERE id = ?', [req.params.id]);
      if (taskArr.length > 0) {
        const task = taskArr[0];
        // notify admin and manager
        await executeQuery('INSERT INTO notifications (user_id, message, is_read) SELECT id, ?, 0 FROM users WHERE role IN ("admin", "manager")', [`Task '${task.name}' status updated to ${status}`]);
      }
      res.json({ success: true });
    } catch (err: any) {
      res.status(500).json({ error: err.message });
    }
  });

  // Materials API
  app.get('/api/materials', authenticateToken, async (req, res) => {
    try {
      const materials = await runQuery('SELECT * FROM materials');
      res.json(materials);
    } catch (err: any) {
      res.status(500).json({ error: err.message });
    }
  });

  app.post('/api/materials', authenticateToken, async (req, res) => {
    const { name, quantity, unit, low_stock_threshold } = req.body;
    try {
      const result = await executeQuery(
        'INSERT INTO materials (name, quantity, unit, low_stock_threshold) VALUES (?, ?, ?, ?)',
        [name, quantity, unit, low_stock_threshold]
      );
      res.json({ id: result.id });
    } catch (err: any) {
      res.status(500).json({ error: err.message });
    }
  });

  app.put('/api/materials/:id/stock', authenticateToken, async (req, res) => {
    const { amount } = req.body; // Can be positive (add) or negative (use)
    try {
      await executeQuery('UPDATE materials SET quantity = quantity + ? WHERE id = ?', [amount, req.params.id]);
      
      // Check low stock
      const mat = await runQuery('SELECT * FROM materials WHERE id = ?', [req.params.id]);
      if (mat[0].quantity <= mat[0].low_stock_threshold) {
        // Send notification to admin (user id 1 for simplicity)
        await executeQuery('INSERT INTO notifications (user_id, message, is_read) SELECT id, ?, 0 FROM users WHERE role IN ("admin", "manager")', 
          [`Low stock alert for material: ${mat[0].name} (Qty: ${mat[0].quantity})`]);
      }
      
      res.json({ success: true });
    } catch (err: any) {
      res.status(500).json({ error: err.message });
    }
  });

  // Resources API
  app.get('/api/resources', authenticateToken, async (req, res) => {
    try {
      const resources = await runQuery('SELECT * FROM resources');
      res.json(resources);
    } catch (err: any) {
      res.status(500).json({ error: err.message });
    }
  });

  app.post('/api/resources', authenticateToken, async (req, res) => {
    const { type, name, details } = req.body;
    try {
      const result = await executeQuery(
        'INSERT INTO resources (type, name, details) VALUES (?, ?, ?)',
        [type, name, details]
      );
      res.json({ id: result.id });
    } catch (err: any) {
      res.status(500).json({ error: err.message });
    }
  });

  // Allocations API
  app.get('/api/allocations', authenticateToken, async (req, res) => {
    try {
      const allocs = await runQuery(`
        SELECT a.*, r.name as resource_name, r.type as resource_type, p.name as project_name 
        FROM allocations a 
        JOIN resources r ON a.item_id = r.id AND a.type = 'resource'
        JOIN projects p ON a.project_id = p.id
      `);
      res.json(allocs);
    } catch (err: any) {
      res.status(500).json({ error: err.message });
    }
  });

  app.post('/api/allocations', authenticateToken, async (req, res) => {
    const { project_id, item_id, quantity } = req.body;
    try {
      const result = await executeQuery(
        'INSERT INTO allocations (project_id, type, item_id, quantity) VALUES (?, ?, ?, ?)',
        [project_id, 'resource', item_id, quantity || 1]
      );
      res.json({ id: result.id });
    } catch (err: any) {
      res.status(500).json({ error: err.message });
    }
  });

  // Notifications
  app.get('/api/notifications', authenticateToken, async (req: any, res) => {
    try {
      const notifications = await runQuery('SELECT * FROM notifications WHERE user_id = ? ORDER BY created_at DESC', [req.user.id]);
      res.json(notifications);
    } catch (err: any) {
      res.status(500).json({ error: err.message });
    }
  });

  app.put('/api/notifications/:id/read', authenticateToken, async (req, res) => {
    try {
      await executeQuery('UPDATE notifications SET is_read = 1 WHERE id = ?', [req.params.id]);
      res.json({ success: true });
    } catch (err: any) {
      res.status(500).json({ error: err.message });
    }
  });

  // Messages API
  app.get('/api/projects/:id/messages', authenticateToken, async (req, res) => {
    try {
      const messages = await runQuery(`
        SELECT m.*, u.name as sender_name, u.role as sender_role 
        FROM messages m
        JOIN users u ON m.sender_id = u.id
        WHERE m.project_id = ?
        ORDER BY m.created_at ASC
      `, [req.params.id]);
      res.json(messages);
    } catch (err: any) {
      res.status(500).json({ error: err.message });
    }
  });

  app.post('/api/projects/:id/messages', authenticateToken, async (req: any, res) => {
    const { message } = req.body;
    try {
      const result = await executeQuery(
        'INSERT INTO messages (project_id, sender_id, message) VALUES (?, ?, ?)',
        [req.params.id, req.user.id, message]
      );
      res.json({ id: result.id, success: true });
    } catch (err: any) {
      res.status(500).json({ error: err.message });
    }
  });

  // --- VITE MIDDLEWARE ---
  if (process.env.NODE_ENV !== 'production') {
    const vite = await createViteServer({
      server: { middlewareMode: true },
      appType: 'spa',
    });
    app.use(vite.middlewares);
  } else {
    // In production, serve the dist folder
    const distPath = path.join(process.cwd(), 'dist');
    app.use(express.static(distPath));
    app.get('*', (req, res) => {
      res.sendFile(path.join(distPath, 'index.html'));
    });
  }

  app.listen(PORT, '0.0.0.0', () => {
    console.log(`Server running on http://localhost:${PORT}`);
  });
}

startServer();
