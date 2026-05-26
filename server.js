const express = require('express');
const path = require('path');

const app = express();

// يخدم كل الملفات بدون استثناء
app.use(express.static(__dirname));

// أي رابط يرجّع index.html
app.get('*', (req, res) => {
  res.sendFile(path.join(__dirname, 'index.html'));
});

const PORT = process.env.PORT || 3000;
app.listen(PORT, () => console.log('Running'));
