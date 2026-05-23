const express = require("express");
const cors = require("cors");
const path = require("path");

const app = express();

// Middleware
app.use(cors());
app.use(express.json());

// تشغيل ملفات الواجهة (public)
app.use(express.static(path.join(__dirname, "public")));

// 👇 هذا مهم جداً: فتح الصفحة الرئيسية
app.get("/", (req, res) => {
  res.sendFile(path.join(__dirname, "public", "index.html"));
});

// تخزين الرسائل (مؤقت)
let messages = [];

// جلب الرسائل
app.get("/messages", (req, res) => {
  res.json(messages);
});

// إرسال رسالة
app.post("/messages", (req, res) => {
  if (!req.body.text) {
    return res.status(400).json({ error: "empty message" });
  }

  messages.push({ text: req.body.text });

  res.json({ success: true });
});

// تشغيل السيرفر
const PORT = process.env.PORT || 3000;

app.listen(PORT, () => {
  console.log("Backend is working 🚀");
});