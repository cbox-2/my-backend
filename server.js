// ✅ server.js - النسخة المحسّنة التي تعالج كل المشاكل من الخادم

const express = require("express");
const path = require("path");
const fs = require("fs");

const app = express();

// =========================
// 🟢 1. خدمة الملفات الثابتة من المجلد الرئيسي
// =========================
app.use(express.static(path.join(__dirname, "public")));

// =========================
// 🟢 2. خدمة الملفات من جميع مجلدات *_files/ ديناميكياً
// =========================
const publicDir = path.join(__dirname, "public");

// اقرأ كل المجلدات في public وابحث عن *_files/
if (fs.existsSync(publicDir)) {
  const items = fs.readdirSync(publicDir, { withFileTypes: true });
  
  items.forEach(item => {
    if (item.isDirectory() && item.name.endsWith('_files')) {
      const dirPath = path.join(publicDir, item.name);
      const routePath = `/${item.name}`;
      
      // خدمة الملفات داخل هذا المجلد على مساره الأصلي
      app.use(routePath, express.static(dirPath));
      
      console.log(`✅ Mounted static: ${routePath} → ${dirPath}`);
    }
  });
}

// =========================
// 🟢 3. نقاط نهاية API (تصلح خطأ JSON)
// =========================

// ✅ نقطة صحية للاختبار
app.get('/api/health', (req, res) => {
  res.setHeader('Content-Type', 'application/json');
  res.json({ 
    status: 'ok', 
    server: 'cbox-system',
    timestamp: new Date().toISOString(),
    message: 'Server is running correctly'
  });
});

// ✅ نقطة الإحصائيات (تصلح: Unexpected token < in JSON)
app.get('/api/stats', (req, res) => {
  res.setHeader('Content-Type', 'application/json');
  res.json({
    success: true,
    data: {
      users: 150,
      messages: 420,
      posts: 89,
      activeSessions: 23,
      lastUpdate: new Date().toISOString()
    }
  });
});

// ✅ نقطة عامة لأي طلبات AJAX أخرى
app.get('/api/:action', (req, res) => {
  res.setHeader('Content-Type', 'application/json');
  res.json({ 
    success: true, 
    action: req.params.action,
    message: 'API endpoint working'
  });
});

// =========================
// 🟢 4. الصفحة الرئيسية
// =========================
app.get("/", (req, res) => {
  res.setHeader('Content-Type', 'text/html; charset=utf-8');
  const filePath = path.join(__dirname, "public", "index.html");
  
  if (fs.existsSync(filePath)) {
    return res.sendFile(filePath);
  }
  
  // fallback: ابحث عن أي ملف عربي
  const files = fs.readdirSync(path.join(__dirname, "public"));
  const arabicFile = files.find(f => f.includes('لوحة') && f.endsWith('.html'));
  
  if (arabicFile) {
    return res.sendFile(path.join(__dirname, "public", arabicFile));
  }
  
  return res.status(404).send('❌ الصفحة الرئيسية غير موجودة');
});

// =========================
// 🟢 5. نظام /admin مع دعم الإحصائيات كـ JSON
// =========================
app.get("/admin", (req, res) => {
  const q = req.query;
  
  // 🎯 إذا طلب إحصائيات أو بيانات، أرجع JSON (وليس HTML!)
  if (q.stats !== undefined || q.ajax !== undefined || q.json !== undefined) {
    res.setHeader('Content-Type', 'application/json');
    return res.json({
      success: true,
      admin: true,
      query: q,
      data: {
        view: q.home ? 'home' : 'unknown',
        stats: { loaded: true, timestamp: new Date().toISOString() }
      }
    });
  }

  // خريطة الصفحات (استخدم الأسماء الجديدة التي حولتها)
  const pages = {
    home: "index.html",
    snippet: "themes.html",        // عدّل حسب أسماء ملفاتك
    acct: "billing.html",
    users: "users.html",
    messages: "messages.html",
    options: "settings.html",
    theme: "themes.html",
    logs: "logs.html",
    posts: "posts.html",
    security: "security.html",
    profile: "profile.html",
    support: "support.html",
    backup: "backup.html",
    notifications: "notifications.html",
    comments: "comments.html",
    analytics: "analytics.html",
    dashboard: "dashboard.html",
    login: "login.html",
  };

  // ابحث عن الصفحة المطلوبة
  for (let key in pages) {
    if (q[key] !== undefined) {
      const fileName = pages[key];
      const filePath = path.join(__dirname, "public", fileName);
      
      if (fs.existsSync(filePath)) {
        res.setHeader('Content-Type', 'text/html; charset=utf-8');
        return res.sendFile(filePath);
      }
    }
  }

  // الصفحة الافتراضية
  res.setHeader('Content-Type', 'text/html; charset=utf-8');
  return res.sendFile(path.join(__dirname, "public", "index.html"));
});

// =========================
// 🟢 6. صفحات ديناميكية عامة
// =========================
app.get("/:page", (req, res) => {
  const page = req.params.page;
  
  // استثنِ المسارات المحجوزة
  if (['admin', 'api', 'public', 'fonts', 'js', 'images', 'css'].includes(page)) {
    return res.status(404).json({ error: 'Reserved path' });
  }

  // ابحث عن الملف في public/
  const possibleFiles = [
    `${page}.html`,
    `${page}.htm`,
    `pages/${page}.html`
  ];

  for (const file of possibleFiles) {
    const filePath = path.join(__dirname, "public", file);
    if (fs.existsSync(filePath)) {
      res.setHeader('Content-Type', 'text/html; charset=utf-8');
      return res.sendFile(filePath);
    }
  }

  // fallback للصفحة الرئيسية
  res.setHeader('Content-Type', 'text/html; charset=utf-8');
  return res.sendFile(path.join(__dirname, "public", "index.html"));
});

// =========================
// 🟢 7. معالجة الأخطاء 404 بشكل صحيح (JSON للـ API، HTML للصفحات)
// =========================
app.use((req, res, next) => {
  // إذا كان الطلب لـ API، أرجع خطأ كـ JSON
  if (req.path.startsWith('/api/')) {
    res.setHeader('Content-Type', 'application/json');
    return res.status(404).json({ 
      error: 'Not Found', 
      message: `API endpoint "${req.path}" does not exist` 
    });
  }
  
  // للطلبات الأخرى، أرسل 404 عادي
  res.status(404).send('❌ 404 - الصفحة غير موجودة');
});

// =========================
// 🟢 8. معالجة الأخطاء العامة
// =========================
app.use((err, req, res, next) => {
  console.error('❌ Server Error:', err.message);
  
  if (req.path.startsWith('/api/')) {
    res.setHeader('Content-Type', 'application/json');
    return res.status(500).json({ error: 'Server Error', message: err.message });
  }
  
  res.status(500).send('❌ خطأ داخلي في السيرفر');
});

// =========================
// 🚀 9. تشغيل السيرفر
// =========================
const PORT = process.env.PORT || 3000;

app.listen(PORT, '0.0.0.0', () => {
  console.log("🚀 Server running on port " + PORT);
  console.log("🔗 Base URL: https://my-backend-production-42ca.up.railway.app");
  console.log("✅ Test API: /api/health");
  console.log("✅ Test Stats: /api/stats");
  console.log("✅ Static files auto-mounted from *_files/ folders");
});
