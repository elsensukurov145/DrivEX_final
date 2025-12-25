<?php
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Drivex — Create account</title>
  <link rel="stylesheet" href="/css/auth.css">
</head>
<body class="auth-page">
  <main class="auth-wrap">
    <section class="auth-card" aria-label="Register">
      <div class="brand">
        <div class="brand-mark" aria-hidden="true"></div>
        <h1 class="brand-name">Drivex</h1>
      </div>
      <p class="brand-subtitle">Create your account</p>

      <form class="auth-form" method="post" action="/php/auth_actions.php">
        <div class="field">
          <label for="name">Full name<span class="req">*</span></label>
          <input id="name" name="name" required placeholder="Your name">
        </div>

        <div class="field">
          <label for="email">Email<span class="req">*</span></label>
          <input id="email" name="email" type="email" required placeholder="you@example.com">
        </div>

        <div class="field">
          <label for="password">Password<span class="req">*</span></label>
          <input id="password" name="password" type="password" required placeholder="••••••••">
        </div>

        <button class="btn" type="submit" name="action" value="register">Create account</button>

        <div class="meta">
          <a class="meta-link" href="/index.php">Back to login</a>
        </div>
      </form>
    </section>
  </main>
</body>
</html>
