const express = require('express');
const path = require('path');
const fs = require('fs');

const app = express();

// يخدم كل الملفات مثل ما هي
app.use(express.static(__dirname));

// تحويل أي طلب إلى ملف فعلي (حتى لو عربي)
app.get('*', (req, res) => {
  try {
    const decodedPath = decodeURIComponent(req.path);
    const filePath = path.join(__dirname, decodedPath);

    if (fs.existsSync(filePath)) {
      return res.sendFile(filePath);
    }

    // fallback
    return res.sendFile(path.join(__dirname, 'index.html'));
  } catch (e) {
    return res.sendFile(path.join(__dirname, 'index.html'));
  }
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => console.log('Running'));
