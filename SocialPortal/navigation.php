<link rel="stylesheet" href="css/navigation.css">
<nav class="navigation">
    <img src="images/logo.svg" alt="sp_logo" class="navigation__logo">
    <form action="" method="post" class="navigation__search-bar">
        <div class="form-group-element">
            <input type="text"
            name="search_query"
            id="search_field"
            placeholder="&#128269; Search"
            class="form__text-field"
            data-result_field="#result-div">
            <input type="submit" value="&#128269;" class="form-group-element__button">
        </div>
    </form>
<?php if (Login::isUserLoggedIn()): 
$user = Database::runQuery("SELECT user_name FROM tbl_users WHERE user_id = :userid", array(":userid" => Login::isUserLoggedIn()))[0]["user_name"];
?>        <ul class="navigation__links">
            <li class="navigation__links_item"><a href="profile.php?username=<?=$user;?>">&#127968;</a></li>
            <li class="navigation__links_item"><a href="messages.php">&#128172;</a></li>
            <li class="navigation__links_item"><a href="explore.php">&#129517;</a></li>
            <li class="navigation__links_item"><a href="notification.php">&#129293;</a></li>
        </ul>
        <details class="navigation__dropdown-menu">
            <summary>Porfile</summary>
            <ul class="navigation__dropdown-menu--links">
                <li class="dropdown-menu--links__items"><a href="edit-profile.php">&#128100; Profile</a></li>

                <li class="dropdown-menu--links__items"><a href="write-post.php?username=<?=$user;?>">&#9997; Create a post</a></li>
                <li class="dropdown-menu--links__items"><a href="settings.php">&#128295; Settings</a></li>
                <li class="dropdown-menu--links__items"><hr></li>
                <li class="dropdown-menu--links__items"><a href="#logoutModal" id="toggleBtn" class="toggleBtn" data-target="#logoutModal">Logout</a></li>
            </ul>
        </details>
    <?php else: ?>
        <ul class="navigation___userconf">
            <li class="navigation__userconf--link">
                <a href="login.html">Log in</a>
            </li>
            <li class="navigation__userconf--link">
                <a href="signup.html" class="blue">Sign up</a>
            </li>
        </ul>
    <?php endif;?>
</nav>

<div class="modal" id="logoutModal">
    <h2>Logout of your account.</h2>
    <h4>Are sure logout of your account?</h4>
    <form action="logout.php" method="post">
        <input type="checkbox" name="allDevices">Logout of all devices?
    <footer>
        <input type="submit" value="Logout" name="Confirm" class="btn link-btn">
        <input type="reset" value="Cancel" class="btn blue" id="closeModal">
    </form>
    </footer>
</div>

<script>
const buttonList = document.querySelectorAll('#toggleBtn');
const closeBtnList = document.querySelectorAll('#closeModal');
Array.from(buttonList).forEach(button => {
  button.addEventListener('click', function(e) {
    e.preventDefault();

    const modalName = button.dataset.target;
    const modal = document.querySelector(modalName);

    modal.style.display = "flex";
  });
});

Array.from(closeBtnList).forEach(closeBtn => {
  closeBtn.addEventListener('click', function(){
    this.closest('.modal').style.display = "none";
  });
});
document.addEventListener('DOMContentLoaded', () => {
    buttonList.dataset.target.style.display = "none";
})
</script>