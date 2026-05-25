const express = require("express");
const path = require("path");

const app = express();

// تشغيل ملفات static (css, js, images)
app.use(express.static(path.join(__dirname, "public")));

// 🔥 الصفحة الرئيسية
app.get("/", (req, res) => {
  res.sendFile(
    path.join(__dirname, "public", "لوحة التحكم · صندوق التحكم.html")
  );
});

// 🔥 نظام صفحات ديناميكي (بدون ما تكتب 20 route)
app.get("/:page", (req, res) => {
  const page = req.params.page;

  // إذا admin أو home يرجع الصفحة الرئيسية
  if (page === "admin" || page === "home") {
    return res.sendFile(
      path.join(__dirname, "public", "لوحة التحكم · صندوق التحكم.html")
    );
  }

  // يحاول يفتح باقي الصفحات مثل /2 /3 /10 ...
  const filePath = path.join(
    __dirname,
    "public",
    `لوحة التحكم · صندوق التحكم${page}.html`
  );

  res.sendFile(filePath, (err) => {
    if (err) {
      res.status(404).send("Page Not Found");
    }
  });
});

// 🔥 تشغيل السيرفر على Railway / localhost
const PORT = process.env.PORT || 3000;

app.listen(PORT, () => {
  console.log("System Running 🚀 on port " + PORT);
});
