const express = require("express");
const path = require("path");

const app = express();

// تشغيل كل الملفات والمجلدات
app.use(express.static(__dirname));

// الصفحة الرئيسية
app.get("/", (req, res) => {
  res.sendFile(path.join(__dirname, "index.html"));
});

const PORT = process.env.PORT || 3000;

app.listen(PORT, () => {
  console.log("System Running 🚀");
});
