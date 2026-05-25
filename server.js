const express = require("express");
const path = require("path");

const app = express();

// تشغيل الملفات الثابتة (CSS, JS, images)
app.use(express.static(path.join(__dirname, "public")));

// =========================
// 🟢 الصفحة الرئيسية
// =========================
app.get("/", (req, res) => {
  res.sendFile(
    path.join(__dirname, "public", "لوحة التحكم · صندوق التحكم.html")
  );
});

// =========================
// 🟢 نظام /admin?home & snippet & acct
// =========================
app.get("/admin", (req, res) => {
  const { home, snippet, acct } = req.query;

  let page = null;

  if (home !== undefined) page = "";
  if (snippet !== undefined) page = "2";   // عدل حسب ملفاتك
  if (acct !== undefined) page = "3";      // عدل حسب ملفاتك

  // إذا ماكو باراميتر يرجع الصفحة الرئيسية
  if (page === null) {
    return res.sendFile(
      path.join(__dirname, "public", "لوحة التحكم · صندوق التحكم.html")
    );
  }

  // إذا home بدون رقم ملف
  if (page === "") {
    return res.sendFile(
      path.join(__dirname, "public", "لوحة التحكم · صندوق التحكم.html")
    );
  }

  // باقي الصفحات
  const filePath = path.join(
    __dirname,
    "public",
    `لوحة التحكم · صندوق التحكم${page}.html`
  );

  return res.sendFile(filePath);
});

// =========================
// 🟢 نظام الصفحات المباشر (/2 /3 /4 ...)
// =========================
app.get("/:page", (req, res) => {
  const page = req.params.page;

  // يمنع دخول admin هنا
  if (page === "admin") return res.redirect("/");

  const filePath = path.join(
    __dirname,
    "public",
    `لوحة التحكم · صندوق التحكم${page}.html`
  );

  res.sendFile(filePath);
});

// =========================
// 🟢 تشغيل السيرفر
// =========================
const PORT = process.env.PORT || 3000;

app.listen(PORT, () => {
  console.log("🚀 System Running on port " + PORT);
});
