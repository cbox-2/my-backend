const express = require("express");
const path = require("path");

const app = express();

// تشغيل الملفات الثابتة
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
// 🟢 /admin?home & snippet & acct
// =========================
app.get("/admin", (req, res) => {
  const { home, snippet, acct, users, messages, options, theme } = req.query;

  // 🟢 home
  if (home !== undefined) {
    return res.sendFile(
      path.join(__dirname, "public", "لوحة التحكم · صندوق التحكم.html")
    );
  }

  // 🟢 snippet (انشر)
  if (snippet !== undefined) {
    return res.sendFile(
      path.join(__dirname, "public", "لوحة التحكم · صندوق التحكم2.html")
    );
  }

  // 🟢 الحساب
  if (acct !== undefined) {
    return res.sendFile(
      path.join(__dirname, "public", "لوحة التحكم · صندوق التحكم3.html")
    );
  }

  // 🟢 مستخدمين
  if (users !== undefined) {
    return res.sendFile(
      path.join(__dirname, "public", "لوحة التحكم · صندوق التحكم4.html")
    );
  }

  // 🟢 رسائل
  if (messages !== undefined) {
    return res.sendFile(
      path.join(__dirname, "public", "لوحة التحكم · صندوق التحكم5.html")
    );
  }

  // 🟢 خيارات
  if (options !== undefined) {
    return res.sendFile(
      path.join(__dirname, "public", "لوحة التحكم · صندوق التحكم6.html")
    );
  }

  // 🟢 مظهر
  if (theme !== undefined) {
    return res.sendFile(
      path.join(__dirname, "public", "لوحة التحكم · صندوق التحكم7.html")
    );
  }

  // الافتراضي
  return res.sendFile(
    path.join(__dirname, "public", "لوحة التحكم · صندوق التحكم.html")
  );
});


// =========================
// 🟢 الصفحات المباشرة /2 /3 /4
// =========================
app.get("/:page", (req, res) => {
  const page = req.params.page;

  // منع تضارب admin
  if (page === "admin") {
    return res.redirect("/");
  }

  const filePath = path.join(
    __dirname,
    "public",
    `لوحة التحكم · صندوق التحكم${page}.html`
  );

  return res.sendFile(filePath);
});


// =========================
// 🟢 تشغيل السيرفر
// =========================
const PORT = process.env.PORT || 3000;

app.listen(PORT, () => {
  console.log("🚀 System Running on port " + PORT);
});
