<?php require_once 'classloader.php'; ?>

<html lang="en">
<head>
  <!-- Required meta tags -->
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
  <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
  <style>
     body {
      font-family: "Arial", sans-serif;
      background: linear-gradient(135deg, #f3e5f5 0%, #e1bee7 100%);
      min-height: 100vh;
      line-height: 1.6;
    }

    .card {
      border: none;
      border-radius: 20px;
      background: linear-gradient(135deg, #ffffff 0%, #f5f0fa 100%);
      box-shadow: 0 15px 50px rgba(0,0,0,0.1);
      overflow: hidden;
      transition: transform 0.3s ease, box-shadow 0.3s ease;
      animation: fadeInUp 0.6s ease;
    }

    .card:hover {
      transform: translateY(-10px);
      box-shadow: 0 25px 60px rgba(0,0,0,0.15);
    }

    .card-body {
      padding: 2rem;
    }

    .card-header {
      background: linear-gradient(45deg, #9c27b0, #7b1fa2);
      color: white;
      font-weight: 600;
      font-size: 1.4rem;
      text-align: center;
      padding: 1rem 1.5rem;
      border-bottom: none;
      border-radius: 20px 20px 0 0;
    }

    .form-group {
      margin-bottom: 1.5rem;
    }

    .form-control {
      border: 2px solid #e9ecef;
      border-radius: 12px;
      padding: 1rem 1.25rem;
      font-size: 1rem;
      transition: all 0.3s ease;
      background: linear-gradient(135deg, #f8f9fa, #ffffff);
    }

    .form-control:focus {
      border-color: #9c27b0;
      box-shadow: 0 0 0 0.3rem rgba(156,39,176,0.25);
      transform: scale(1.02);
    }

    label {
      font-weight: 600;
      color: #7b1fa2;
      margin-bottom: 0.5rem;
      display: block;
    }

    .btn {
      border-radius: 25px;
      padding: 0.75rem 1.5rem;
      font-weight: 600;
      transition: all 0.3s ease;
    }

    .btn-primary {
      background: linear-gradient(45deg, #9c27b0, #7b1fa2);
      border: none;
    }

    .btn-primary:hover {
      transform: translateY(-2px);
      box-shadow: 0 5px 15px rgba(156,39,176,0.3);
      background: linear-gradient(45deg, #7b1fa2, #4a148c);
      color: white;
    }

    h1[style*="color: green"] {
      padding: 1rem;
      border-radius: 12px;
      margin-bottom: 1rem;
      font-size: 1.2rem;
      text-align: center;
      font-weight: 600;
      background: linear-gradient(135deg, #d4edda, #c3e6cb);
      border: 2px solid #28a745;
      color: #155724 !important;
    }

    h1[style*="color: red"] {
      padding: 1rem;
      border-radius: 12px;
      margin-bottom: 1rem;
      font-size: 1.2rem;
      text-align: center;
      font-weight: 600;
      background: linear-gradient(135deg, #f8d7da, #f5c6cb);
      border: 2px solid #dc3545;
      color: #721c24 !important;
    }

    a {
      color: #7b1fa2;
      font-weight: 500;
      transition: all 0.3s ease;
    }

    a:hover {
      color: #9c27b0;
      text-decoration: underline;
    }

    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(30px); }
      to { opacity: 1; transform: translateY(0); }
    }

    @media (max-width: 576px) {
      .card-body { padding: 1.25rem; }
      .btn { padding: 0.6rem 1.2rem; font-size: 0.9rem; }
      label { font-size: 0.9rem; }
    }
  </style>
  <title>Hello, world!</title>
</head>
<body>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-md-8 p-5">
        <div class="card shadow">
          <div class="card-header">
            <h2>Welcome to the freelancer panel, Register Now as freelancer!</h2>
          </div>
          <form action="core/handleForms.php" method="POST">
            <div class="card-body">
              <?php  
                if (isset($_SESSION['message']) && isset($_SESSION['status'])) {

                  if ($_SESSION['status'] == "200") {
                    echo "<h1 style='color: green;'>{$_SESSION['message']}</h1>";
                  }

                  else {
                    echo "<h1 style='color: red;'>{$_SESSION['message']}</h1>"; 
                  }

                }
                unset($_SESSION['message']);
                unset($_SESSION['status']);
                ?>
              <div class="form-group">
                <label for="exampleInputEmail1">Username</label>
                <input type="text" class="form-control" name="username" required>
              </div>
              <div class="form-group">
                <label for="exampleInputEmail1">Email</label>
                <input type="email" class="form-control" name="email" required>
              </div>
              <div class="form-group">
                <label for="exampleInputEmail1">Contact Number</label>
                <input type="text" class="form-control" name="contact_number" required>
              </div>
              <div class="form-group">
                <label for="exampleInputEmail1">Password</label>
                <input type="password" class="form-control" name="password" required>
              </div>
              <div class="form-group">
                <label for="exampleInputEmail1">Confirm Password</label>
                <input type="password" class="form-control" name="confirm_password" required>
                <input type="submit" class="btn btn-primary float-right mt-4" name="insertNewUserBtn">
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</body>
</html>