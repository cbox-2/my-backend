const express = require('express');
const path = require('path');

const app = express();

// 🔥 يخدم كل ملفات المشروع (CSS, JS, images) بدون تعديل أي مسارات
app.use(express.static(__dirname, {
  setHeaders: (res, filePath) => {
    // تحسين بسيط لأنواع الملفات
    if (filePath.endsWith('.css')) {
      res.setHeader('Content-Type', 'text/css');
    }
    if (filePath.endsWith('.js')) {
      res.setHeader('Content-Type', 'application/javascript');
    }
  }
}));

// 🏠 الصفحة الرئيسية
app.get('/', (req, res) => {
  res.sendFile(path.join(__dirname, 'index.html'));
});

// 🚫 يمنع رجوع HTML بدل الملفات (حل مشكلة MIME)
app.use((req, res, next) => {
  if (!req.path.includes('.') && req.path !== '/') {
    return res.status(404).send('Not Found');
  }
  next();
});

// 🚀 تشغيل السيرفر
const PORT = process.env.PORT || 3000;
app.listen(PORT, () => {
  console.log('Server running on port', PORT);
});
