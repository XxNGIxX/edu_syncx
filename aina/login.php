

<!doctype html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="https://fonts.googleapis.com/css2?family=Itim&family=Noto+Serif+Thai:wght@100..900&family=Roboto+Condensed:wght@300&family=Sriracha&display=swap" rel="stylesheet">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">

    <title>LOGIN</title>
    <link href="login.css" rel="stylesheet">
  </head>

  <body class="text-center">
    <main class="form-signin">
      <form action="do_login.php" method="post" onsubmit="return login()">
        <img class="mb-4" src="login.png" alt="" height="300">
        <h1 class="h3 mb-3 fw-normal"><b>EDUSyncX</b> - Login</h1>
    
        <div class="form-floating">
          <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" required>
          <label for="floatingInput">E-mail</label>
        </div>
        
        <div class="form-floating">
          <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
          <label for="floatingPassword">Password</label>
        </div>
    
        <input type="submit" value="LOGIN">
        <p> <a href='ch_pwd.php'>เปลี่ยนรหัสผ่าน</a></p>
        <?php
        if (isset($_GET['error'])) {
            $error = htmlspecialchars($_GET['error']);
            echo "<div style='color: red; text-align: center; font-weight: bold;'>$error</div>";
        }
        ?>
      </form>
    </main>

    <script src="login.js"></script>    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
  </body>
</html>
