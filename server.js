const express = require("express");
const cors = require("cors");
const app = express();

app.use(cors());
app.use(express.json());

let messages = [];

app.get("/", (req, res) => {
  res.send("Backend is working 🚀");
});

app.get("/messages", (req, res) => {
  res.json(messages);
});

app.post("/messages", (req, res) => {
  messages.push(req.body);
  res.json({ success: true });
});

const PORT = process.env.PORT || 3000;

app.listen(PORT, () => {
  console.log("Server running");
});
