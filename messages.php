<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="images/favicon.ico" type="image/x-icon">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/profile.css">
    <link rel="stylesheet" type="text/css" href="css/navigation.css">
    <link rel="stylesheet" type="text/css" href="css/post.css">
    <link rel="stylesheet" type="text/css" href="css/message.css">
    <?php
    include 'config/Database.php';
    include 'models/Login.php';
    include 'models/Post.php';
    include 'models/Notification.php';
    include 'models/Comment.php';
    include 'models/Message.php';
    ?>
    <title>Messages</title>
    <?php
   
    if(isset($_POST['send_message'])){
        if(strlen($_POST['message-body']) > 0){
            Message::sendMessage(md5($_POST['csrf_token']) ,$_POST['message-body'], Login::isUserLoggedIn(), $_GET['reciever']);
        }
    }
    ?>
</head>
<body>
    <?php include "navigation.php"; ?>
    <section class="message-section">
        <aside class="message-section__sidebar">
            <header class="message-section__sidebar--header">
                <h3 class="message-section__sidebar--heading">Messages</h3>
            </header>
            <main class="message-list__container">
                <ul class="message-list">                    
                    <li>
                        <input type="text" id="search-box" placeholder="Search following user">
                        <div id="users-list">
                            
                        </div>
                    </li>
                    <?php
                    foreach(array_unique(Message::recentChatList(Login::isUserLoggedIn())) as $user):
                        $userData = Database::runQuery("SELECT tbl_users.user_name AS username, tbl_messages.message_body AS message, tbl_messages.sender AS msg_sender FROM tbl_users, tbl_messages WHERE tbl_users.user_id=:userid AND tbl_messages.sender=:sender AND tbl_messages.receiver=:userid OR tbl_messages.sender=:userid AND tbl_messages.receiver=:sender ORDER BY tbl_messages.id DESC LIMIT 1",array(":userid" => $user, ":sender" => Login::isUserLoggedIn()))[0];
                        extract($userData);
                    ?>
                    <li class="<?=($user == $_GET['reciever'])? 'active' : '' ; ?>">
                      <a href="messages.php?reciever=<?=$user; ?>" class="recent-chat__link">
                          <strong><?=$username; ?></strong><br>
                          <small>
                              <?=($msg_sender === Login::isUserLoggedIn())? "You: " : "" ; ?><?=$message; ?>
                          </small>
                      </a>  
                    </li>
                    <?php endforeach; ?>
                </ul>
            </main>
        </aside>
        <main class="message-box">
            <?php if(isset($_GET['reciever'])): ?>
            <header class="message-box__header">
                
                <h3 class="message-box__heading">
                    <?=Database::runQuery("SELECT user_name FROM tbl_users WHERE user_id=:userid", array(":userid" => $_GET['reciever']))[0]['user_name']?>
                </h3>
                <small class="message-box__note">
                    &nbsp;
                </small>
            </header>
            <ul class="message-container" id="message-container">
                 <?php
                 foreach (Message::displayMessages(Login::isUserLoggedIn(), $_GET['reciever']) as $message) :
                    extract($message);
                 ?>

                 <?php 
                 if($sender === Login::isUserLoggedIn()):
                 ?>
                 <li class="message send">
                    <p class="message-body"><?=$message_body; ?></p>
                    <small><?=$send_at; ?></small>
                 </li>
                 <?php else: ?>
                 <li class="message recieved">
                    <p class="message-body"><?=$message_body; ?></p>
                    <small><?=$send_at; ?></small>
                 </li>
                 <?php endif; ?>
                 <?php endforeach; ?>                
            </ul>
            <footer class="message-box__footer">
            <form method="post" action="messages.php?reciever=<?=$_GET['reciever'];?>" class="message-form">
                <?php 
                    $cstrong = true;
                    $msg_token   = bin2hex(openssl_random_pseudo_bytes(64, $cstrong));
                    $_SESSION['message_csrf'] = $msg_token;
                ?>
                <input type="hidden" name="csrf_token" value="<?=$msg_token; ?>">
                <textarea class="message-textbox" rows="2" placeholder="Write a message." name="message-body"></textarea>
                <input type="submit" name="send_message" value="&#10148;" class="send-message__btn">
            </form>
            </footer> 
            <?php else: ?>
            <main class="message__division">
                <h1 class="large-icon">&#128488;</h1>
                <h2>Connect with peaple start conversion.</h2>
            </main>
            <?php endif; ?>
        </main>
               
    </section>
</body>
</html>
<script type="text/javascript">
    const show = element => {
        element.classList.add("show")
    }
    const hide = element => {
        element.classList.remove("show")
    }
    document.addEventListener('DOMContentLoaded', () => {
        
        const msgContainer = document.querySelector("#message-container")
        if (msgContainer) { msgContainer.scrollTop = msgContainer.scrollHeight }

        const searchBox = document.querySelector(".message-list #search-box")
        const searchResults = document.querySelector(".message-list #users-list")

        searchBox.addEventListener("input", (event) => {
            event.preventDefault()
            show(searchResults)
            const results = makeServerRequest('search.php?guess=' + searchBox.value, 'GET', matches=>{
               if (searchBox.value.length === 0) { matches = []; hide(searchResults) }
               searchResults.innerHTML = matches.map(match => `
                <li>
                    <a href="messages.php?reciever=${match.user_id}" class="users__profile-link">
                        <img src="${match.profile_image}" alt="${match.username}'s img" class="users__img">
                        <strong class="users__username">${match.user_name}</strong>
                    </a>
                </li>
                `).join("")
            })
        })
    })
</script>