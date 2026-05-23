const express = require("express");
const path = require("path");

const app = express();

// ?? åÐÇ Ãåã ÓØÑ áÊÔÛíá ÇáãáÝÇÊ ßáåÇ (CSS + JS + ÕæÑ)
app.use(express.static(path.join(__dirname, "public")));

// ?? ÇáÕÝÍÉ ÇáÑÆíÓíÉ
app.get("/", (req, res) => {
  res.sendFile(path.join(__dirname, "public", "index.html"));
});

const PORT = process.env.PORT || 3000;

app.listen(PORT, () => {
  console.log("Backend is working ??");
});