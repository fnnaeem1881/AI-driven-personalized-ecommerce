<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Maintenance — TechNova</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;900&display=swap" rel="stylesheet">
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family:'Inter',sans-serif; background:#060b14; color:#F1F5F9; min-height:100vh; display:flex; align-items:center; justify-content:center; }
        .card { text-align:center; padding:3rem 2rem; max-width:520px; }
        .icon { font-size:4rem; margin-bottom:1.5rem; }
        h1 { font-size:2.5rem; font-weight:900; margin-bottom:1rem; background:linear-gradient(135deg,#3B82F6,#8B5CF6); -webkit-background-clip:text; -webkit-text-fill-color:transparent; }
        p { color:#64748B; font-size:1.05rem; line-height:1.7; margin-bottom:2rem; }
        .badge { display:inline-block; background:rgba(59,130,246,0.1); border:1px solid rgba(59,130,246,0.3); border-radius:999px; padding:0.5rem 1.5rem; font-size:0.85rem; color:#60A5FA; }
    </style>
</head>
<body>
    <div class="card">
        <div class="icon">🔧</div>
        <h1>Under Maintenance</h1>
        <p>{{ $message }}</p>
        <span class="badge">We'll be back soon!</span>
    </div>
</body>
</html>
