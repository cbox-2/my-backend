const express = require("express");
const path = require("path");

const app = express();

// تشغيل ملفات public
app.use(express.static(path.join(__dirname, "public")));

// الصفحة الرئيسية
app.get("/", (req, res) => {
  res.sendFile(path.join(__dirname, "public", "لوحة التحكم · صندوق التحكم.html"));
});

// الصفحات الأخرى (روابط نظيفة)
app.get("/2", (req, res) => {
  res.sendFile(path.join(__dirname, "public", "لوحة التحكم · صندوق التحكم2.html"));
});

app.get("/3", (req, res) => {
  res.sendFile(path.join(__dirname, "public", "لوحة التحكم · صندوق التحكم3.html"));
});

app.get("/4", (req, res) => {
  res.sendFile(path.join(__dirname, "public", "لوحة التحكم · صندوق التحكم4.html"));
});

app.get("/5", (req, res) => {
  res.sendFile(path.join(__dirname, "public", "لوحة التحكم · صندوق التحكم5.html"));
});

app.get("/6", (req, res) => {
  res.sendFile(path.join(__dirname, "public", "لوحة التحكم · صندوق التحكم6.html"));
});

app.get("/7", (req, res) => {
  res.sendFile(path.join(__dirname, "public", "لوحة التحكم · صندوق التحكم7.html"));
});

app.get("/8", (req, res) => {
  res.sendFile(path.join(__dirname, "public", "لوحة التحكم · صندوق التحكم8.html"));
});

app.get("/9", (req, res) => {
  res.sendFile(path.join(__dirname, "public", "لوحة التحكم · صندوق التحكم9.html"));
});

app.get("/10", (req, res) => {
  res.sendFile(path.join(__dirname, "public", "لوحة التحكم · صندوق التحكم10.html"));
});

app.get("/11", (req, res) => {
  res.sendFile(path.join(__dirname, "public", "لوحة التحكم · صندوق التحكم11.html"));
});

app.get("/12", (req, res) => {
  res.sendFile(path.join(__dirname, "public", "لوحة التحكم · صندوق التحكم12.html"));
});

app.get("/13", (req, res) => {
  res.sendFile(path.join(__dirname, "public", "Control Panel · Cbox 13.html"));
});

app.get("/14", (req, res) => {
  res.sendFile(path.join(__dirname, "public", "Control Panel · Cbox14.html"));
});

app.get("/15", (req, res) => {
  res.sendFile(path.join(__dirname, "public", "لوحة التحكم · صندوق التحكم15.html"));
});

app.get("/16", (req, res) => {
  res.sendFile(path.join(__dirname, "public", "لوحة التحكم · صندوق التحكم16.html"));
});

app.get("/19", (req, res) => {
  res.sendFile(path.join(__dirname, "public", "لوحة التحكم · صندوق التحكم19.html"));
});

// تشغيل السيرفر
const PORT = process.env.PORT || 3000;

app.listen(PORT, () => {
  console.log("System Running 🚀");
});
