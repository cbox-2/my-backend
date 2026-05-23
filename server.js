const express = require("express");
const path = require("path");

const app = express();

app.use(express.json());
app.use(express.static(path.join(__dirname, "public")));

// الصفحة الرئيسية
app.get("/", (req, res) => {
  res.sendFile(path.join(__dirname, "public", "index.html"));
});

// 🟢 بيانات تجريبية
let messages = [];

// 🟢 جلب الرسائل
app.get("/messages", (req, res) => {
  res.json(messages);
});

// 🟢 إضافة رسالة
app.post("/messages", (req, res) => {
  const text = req.body.text;

  if (!text) {
    return res.status(400).json({ error: "empty message" });
  }

  messages.push({ text });

  res.json({ success: true });
});

const PORT = process.env.PORT || 3000;

app.listen(PORT, () => {
  console.log("Backend is working 🚀");
});
