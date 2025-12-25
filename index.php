<?php session_start(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DrivEx - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/auth.css">
</head>
<body>

<div class="auth-card">
    <div >
        
        <h1>Drivex</h1>
    </div>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger text-center">
            <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <ul class="nav nav-pills nav-justified mb-4 auth-tabs" id="authTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="login-tab" data-bs-toggle="pill" data-bs-target="#login" type="button" role="tab">
                Login
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="register-tab" data-bs-toggle="pill" data-bs-target="#register" type="button" role="tab">
                Register
            </button>
        </li>
    </ul>

    <div class="tab-content" id="authTabsContent">

        <!-- LOGIN -->
        <div class="tab-pane fade show active" id="login" role="tabpanel">
            <form action="php/auth_actions.php" method="POST">
                <input type="hidden" name="action" value="login">

                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" autocomplete="username" required>
                </div>

                <div class="mb-3 position-relative">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" id="loginPassword" class="form-control" autocomplete="current-password" required>
                    <span class="see-btn" onclick="togglePassword('loginPassword', this)">SEE</span>
                </div>

                <div class="form-check mb-3 remember-row">
                    <input class="form-check-input" type="checkbox" name="remember" id="rememberMe" value="1">
                    <label class="form-check-label" for="rememberMe">
                        Stay logged in for 2 weeks
                    </label>
                </div>

                <button type="submit" class="btn btn-primary w-100">Sign In</button>
            </form>
        </div>

        <!-- REGISTER -->
        <div class="tab-pane fade" id="register" role="tabpanel">
            <form action="php/auth_actions.php" method="POST">
                <input type="hidden" name="action" value="register">

                <div class="mb-3">
                    <label class="form-label">Username</label>
                    <input type="text" name="username" class="form-control" autocomplete="username" required>
                </div>

                <div class="mb-3">
                    <label class="form-label">Email Address</label>
                    <input type="email" name="email" class="form-control" autocomplete="email" required>
                </div>

                <div class="mb-3 position-relative">
                    <label class="form-label">Password</label>
                    <input type="password" name="password" id="registerPassword" class="form-control" autocomplete="new-password" required>
                    <span class="see-btn" onclick="togglePassword('registerPassword', this)">SEE</span>
                </div>

                <div class="mb-3 position-relative">
                    <label class="form-label">Confirm Password</label>
                    <input type="password" name="confirm_password" id="confirmPassword" class="form-control" autocomplete="new-password" required>
                    <span class="see-btn" onclick="togglePassword('confirmPassword', this)">SEE</span>
                </div>

                <button type="submit" class="btn btn-primary w-100">Create Account</button>
            </form>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
function togglePassword(id, el) {
    const input = document.getElementById(id);
    if (!input) return;

    if (input.type === "password") {
        input.type = "text";
        el.innerText = "HIDE";
    } else {
        input.type = "password";
        el.innerText = "SEE";
    }
}
</script>

</body>
</html>
