<nav class="navbar navbar-expand-lg bg-body-tertiary rounded" aria-label="Thirteenth navbar example">
  <div class="container-fluid">
    <a class="navbar-brand d-flex align-items-center me-auto" href="index.php">
      <img src="../images/home.png" alt="Logo" height="30" class="me-2">
      Escapy
    </a>
    
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarsExample11"
      aria-controls="navbarsExample11" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>

    <!-- Collapsible navbar content -->
    <div class="collapse navbar-collapse" id="navbarsExample11">
      <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
        <?php if (isset($_SESSION['user_id'])): ?>

          <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
            <li class="nav-item">
                <!-- Using btn-danger (Red) to make it distinct from normal user buttons -->
                <a class="nav-link btn btn-danger text-white mx-1" href="delete_room.php">Admin Panel</a>
            </li>
          <?php endif; ?>
          
          <?php 
          // Check if current page is manage_account.php
          $current_page = basename($_SERVER['PHP_SELF']);
          $is_manage_account = ($current_page === 'manage_account.php');
          ?>
          
          <li class="nav-item">
            <?php if ($is_manage_account): ?>
              <a class="nav-link btn btn-primary text-white mx-1" href="../index.php">Home</a>
            <?php else: ?>
              <a class="nav-link btn btn-primary text-white mx-1" href="../manage_account.php">My Account</a>
            <?php endif; ?>
          </li>
          
          <li class="nav-item">
            <a class="nav-link btn btn-primary text-white mx-1" href="../logout.php">Logout</a>
          </li>
          
        <?php else: ?>
          
          <li class="nav-item">
            <a class="nav-link btn btn-primary text-white mx-1" href="login.php">Login</a>
          </li>
          
        <?php endif; ?>
      </ul>
    </div>
  </div>
</nav>