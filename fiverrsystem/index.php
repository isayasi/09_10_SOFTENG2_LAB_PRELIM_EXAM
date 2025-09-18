<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <!-- Bootstrap CSS -->
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css" integrity="sha384-xOolHFLEh07PJGoPkLv1IbcEPTNtaed2xpHsD9ESMhqIYd0nLMwNLD69Npy4HI+N" crossorigin="anonymous">
  <script src="https://code.jquery.com/jquery-3.7.0.js"></script>
  <style>
    /* Global Styles */
body {
  font-family: "Arial", sans-serif;
  background: linear-gradient(135deg, #f5f5f5 0%, #e0e0f0 100%);
  color: #333;
  line-height: 1.6;
}

/* Navbar */
.navbar {
  background: linear-gradient(90deg, #0077B6 0%, #0096c7 100%);
  box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.navbar-brand {
  font-weight: bold;
  font-size: 1.5rem;
  color: #fff !important;
}

/* Headings */
.display-4 {
  font-weight: 700;
  color: #433878;
  text-shadow: 1px 1px 2px rgba(0,0,0,0.1);
  margin-bottom: 2rem;
}

.row > [class*="col-"] {
  display: flex;
  flex-direction: column;
}

/* Cards */
.card {
  border: none;
  border-radius: 20px;
  overflow: hidden;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  background: linear-gradient(135deg, #ffffff 0%, #f0f0f8 100%);
  flex: 1;
}

.card-body {
  display: flex;
  flex-direction: column;
}

.card:hover {
  transform: translateY(-10px);
  box-shadow: 0 20px 40px rgba(0,0,0,0.2);
}

.card-body h1 {
  font-size: 1.5rem;
  color: #433878;
  margin-bottom: 1rem;
}

.card-body h3 a {
  text-decoration: none;
  color: #0077B6;
  transition: color 0.3s ease;
}

.card-body h3 a:hover {
  color: #005f8a;
}

/* Card Images */
.card img {
  border-radius: 15px;
  margin-bottom: 1rem;
  object-fit: cover;
}

/* Paragraphs */
.card-body p {
  font-size: 0.95rem;
  color: #555;
}

/* Testimonials Section */
.card.h-100 {
  min-height: 100%;
}

.card-title {
  font-weight: 600;
  color: #433878;
}

.card-text {
  font-size: 0.9rem;
  color: #555;
}

/* Responsive adjustments */
@media (max-width: 992px) {
  .card-body h1 {
    font-size: 1.3rem;
  }
}

@media (max-width: 768px) {
  .display-4 {
    font-size: 2rem;
  }

  .card-body h1 {
    font-size: 1.2rem;
  }
}

@media (max-width: 576px) {
  .display-4 {
    font-size: 1.8rem;
  }

  .card-body h1 {
    font-size: 1rem;
  }

  
}

  </style>
</head>
<body>
  <nav class="navbar navbar-expand-lg navbar-dark p-4" style="background-color: #0077B6;">
    <a class="navbar-brand" href="#">Fiverr Clone Homepage</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
  </nav>

  <div class="container-fluid">
    <div class="display-4 text-center mt-4 mb-4">Hello there and welcome to the Fiverr Clone!</div>
    <div class="row">
      <!-- Client Card -->
      <div class="col-md-4">
        <div class="card shadow mb-4">
          <div class="card-body">
            <h1>Are you looking for a talent?</h1>
            <img src="https://images.unsplash.com/photo-1549923746-c502d488b3ea?q=80&w=1171&auto=format&fit=crop&ixlib=rb-4.1.0" class="img-fluid mb-3">
            <p>Content writers create clear, engaging, and informative content that helps businesses communicate their services or products effectively, build brand authority, attract and retain customers, and drive web traffic and conversions.</p>
            <h3><a href="client/index.php">Get started here as client</a></h3>
          </div>
        </div>
      </div>

      <!-- Freelancer Card -->
      <div class="col-md-4">
        <div class="card shadow mb-4">
          <div class="card-body">
            <h1>Are you looking for a job?</h1>
            <img src="https://plus.unsplash.com/premium_photo-1661582394864-ebf82b779eb0?q=80&w=1170&auto=format&fit=crop&ixlib=rb-4.1.0" class="img-fluid mb-3">
            <p>Freelancers play a key role in content team development. They showcase their skills and connect with clients who need their services.</p>
            <h3><a href="freelancer/index.php">Get started here as freelancer</a></h3>
          </div>
        </div>
      </div>

      <!-- Admin Card -->
      <div class="col-md-4">
        <div class="card shadow mb-4">
          <div class="card-body">
            <h1>Are you an admin?</h1>
            <img src="https://erratichour.com/wp-content/uploads/2023/11/10-Essential-Skills-Every-Admin-Pro-Should-Have.jpg" class="img-fluid mb-3">
            <p>Admins manage the platform, oversee users and content, and ensure smooth operation of the system. They have access to all management features.</p>
            <h3><a href="admin/admin_categories.php">Get started here as admin</a></h3>
          </div>
        </div>
      </div>
    </div>
    </div>
  </div>
</body>
</html>
