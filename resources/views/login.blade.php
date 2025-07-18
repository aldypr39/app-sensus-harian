<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="csrf-token" content="{{ csrf_token() }}"> <title>Login - Sensus Harian</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Login - Sensus Harian</title>
  <link rel="stylesheet" href="css/base.css">
  <link rel="stylesheet" href="css/login.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body class="login-page-body">

  <div class="login-container">
    <div class="login-box glass-effect">
      <div class="login-header">
        <i class="fas fa-hospital-user"></i>
        <h1>Sensus Harian</h1>
        <p>Silakan masuk untuk melanjutkan</p>
      </div>

      <form id="form-login">
        <div class="input-group">
          <i class="fas fa-user"></i>
          <input id="login-username" type="text" placeholder="Username" required>
        </div>
        <div class="input-group">
          <i class="fas fa-lock"></i>
          <input id="login-password" type="password" placeholder="Password" required>
        </div>
        <div class="options">
          <label class="remember-me">
            <input type="checkbox" name="remember"> Ingat Saya
          </label>
          <a href="#" class="forgot-password">Lupa Password?</a>
        </div>
        <button type="submit" class="login-btn">Login</button>
      </form>
    </div>
  </div>

  <!-- Script login handler -->
  <script type="module" src="js/login.js"></script>
</body>
</html>
