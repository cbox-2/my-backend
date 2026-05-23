let messages = [];

app.use(express.json());

app.get("/messages", (req, res) => {
  res.json(messages);
});

app.post("/messages", (req, res) => {
  messages.push(req.body);
  res.json({ success: true });
});
