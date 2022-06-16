
<?php $title = "Welcome to Room-EZ";?>


<?php ob_start();?>

<section class="flexColumn">

  <div class="landingHeader">
    <h1>WELCOME TO OUR SUPER WEBSITE. START YOUR ROOM SEARCH TODAY !</h1>
    <br>
    <h3>Lorem ipsum doincididim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</h3>
  </div>
  
  <div class="searchBarContainer">
      <input type="text" name="searchbar" id="searchBar" placeholder="Start your search here">
  </div>

  <div class="slideshow">
    <div id="slide1" class="slide"></div>
  </div>


  <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>
  <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>


  </section>
  
  <?php include('listPropertiesView.php'); ?> 
  
  <!-- Modal section -->
<?php if (empty($_SESSION['email'])) {?>
  <div id="modalBox" class="modal">
    
    <!-- Modal content -->
    <div class="modal-content">
      <div id="banner-container"></div>
      <span class="close">&times;</span>
      <div class="form-container">
        <div id="signIn-container">
          <?php include('view/signInView.php');?>
        </div>
        <div id="signUp-container">
          <?php include('view/signUpView.php');?>
        </div>
      </div>
    </div>
  </div>
<?php } ?>

<?php $content = ob_get_clean(); ?>

<?php require("template.php") ?>

<script src="./public/js/modal.js"></script> 
