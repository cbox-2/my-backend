const express = require("express");
const path = require("path");
const fs = require("fs");

const app = express();

// =========================
// 🟢 Static files
// =========================
app.use(express.static(path.join(__dirname, "public")));


// =========================
// 🟢 الصفحة الرئيسية
// =========================
app.get("/", (req, res) => {
  return res.sendFile(
    path.join(__dirname, "public", "لوحة التحكم · صندوق التحكم.html")
  );
});


// =========================
// 🟢 /admin system
// =========================
app.get("/admin", (req, res) => {
  const q = req.query;

  const pages = {
    home: "لوحة التحكم · صندوق التحكم.html",
    snippet: "لوحة التحكم · صندوق التحكم2.html",
    acct: "لوحة التحكم · صندوق التحكم3.html",
    users: "لوحة التحكم · صندوق التحكم4.html",
    messages: "لوحة التحكم · صندوق التحكم5.html",
    options: "لوحة التحكم · صندوق التحكم6.html",
    theme: "لوحة التحكم · صندوق التحكم7.html",
  };

  for (let key in pages) {
    if (q[key] !== undefined) {
      return res.sendFile(
        path.join(__dirname, "public", pages[key])
      );
    }
  }

  // default
  return res.sendFile(
    path.join(__dirname, "public", "لوحة التحكم · صندوق التحكم.html")
  );
});


// =========================
// 🟢 صفحات ديناميكية (اختياري)
// =========================
app.get("/:page", (req, res) => {
  const page = req.params.page;

  if (page === "admin") return res.redirect("/");

  const file = `${page}.html`;

  const filePath = path.join(__dirname, "public", file);

  if (fs.existsSync(filePath)) {
    return res.sendFile(filePath);
  }

  return res.sendFile(
    path.join(__dirname, "public", "لوحة التحكم · صندوق التحكم.html")
  );
});


// =========================
// 🟢 تشغيل السيرفر
// =========================
const PORT = process.env.PORT || 3000;

app.listen(PORT, () => {
  console.log("🚀 Server running on port " + PORT);
});
