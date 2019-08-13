<?php 
include("includes/header.php");



if(isset($_GET['profile_username'])) {
	$username = $_GET['profile_username'];
	$user_details_query = mysqli_query($con, "SELECT * FROM users WHERE username='$username'");
	$user_array = mysqli_fetch_array($user_details_query);

	$num_friends = (substr_count($user_array['friend_array'], ",")) - 1;
}

$logged_in_user_obj = new User($con, $userLoggedIn);
$profile_user_obj = new User($con, $username);
$message_obj = new Message($con, $userLoggedIn);


if(isset($_POST['remove_friend'])) {
	$user = new User($con, $userLoggedIn);
	$user->removeFriend($username);
}

if(isset($_POST['add_friend'])) {
	$user = new User($con, $userLoggedIn);
	$user->sendRequest($username);
}
if(isset($_POST['respond_request'])) {
	header("Location: requests.php");
}

if(isset($_POST['post_message'])){
  if(isset($_POST['message_body'])){
    $body = mysqli_real_escape_string($con, $_POST['message_body']);
    $date = date("Y-m-d H:i:s");
    $message_obj->sendMessage($username, $body, $date);
  }



  $link = '#profileTabs a[href="#messages_div"]';

  echo "<script>
          $(function(){
            $('" . $link . "').tab('show');
          });
        </script>";
}

if(isset($_POST['post'])){

  $uploadOk = 1;
  $imageName = $_FILES['fileToUpload']['name'];
  $errorMessage = "";

  if($imageName != "") {
    $targetDir = "assets/images/posts/";
    $imageName = $targetDir . uniqid() . basename($imageName);
    $imageFileType = pathinfo($imageName, PATHINFO_EXTENSION);

    if($_FILES['fileToUpload']['size'] > 10000000) {
      $errorMessage = "Sorry your file is too large";
      $uploadOk = 0;
    }

    if(strtolower($imageFileType) != "jpeg" && strtolower($imageFileType) != "png" && strtolower($imageFileType) != "jpg") {
      $errorMessage = "Sorry, only jpeg, jpg and png files are allowed";
      $uploadOk = 0;
    }

    if($uploadOk) {
      if(move_uploaded_file($_FILES['fileToUpload']['tmp_name'], $imageName)) {
        //image uploaded okay
      }
      else {
        //image did not upload
        $uploadOk = 0;
      }
    }

  }

  if($uploadOk) {
    $post = new Post($con, $userLoggedIn);
    $post->submitPost($_POST['post_text'], $profile_user_obj->getUsername(), $imageName);
  }
  else {
    echo "<div style='text-align:center;' class='alert alert-danger'>
        $errorMessage
      </div>";
  }

}

  ?>

 	<style type="text/css">
	 	.wrapper {
      margin-left: 0px;
      margin-right: 0px;
      padding-left: 0px;
      padding-right: 0px;
	 	}

 	</style>
	
 	<div class="profile_top">
 		<img src="<?php echo $user_array['profile_pic']; ?>">


 		<form action="<?php echo $username; ?>" method="POST">
 			<?php 
 			 
 			if($profile_user_obj->isClosed()) {
 				header("Location: user_closed.php");
 			}

 			 

 			if($userLoggedIn != $username) {

 				if($logged_in_user_obj->isFriend($username)) {
 					echo '<input type="submit" name="remove_friend" class="danger" value="Remove Friend"><br>';
 				}
 				else if ($logged_in_user_obj->didReceiveRequest($username)) {
 					echo '<input type="submit" name="respond_request" class="warning" value="Respond to Request"><br>';
 				}
 				else if ($logged_in_user_obj->didSendRequest($username)) {
 					echo '<input type="submit" name="" class="default" value="Request Sent"><br>';
 				}
 				else 
 					echo '<input type="submit" name="add_friend" class="success" value="Add Friend"><br>';

 			}

 			?>
 		</form>

    <?php  
    if($userLoggedIn != $username) {
      echo '<div class="profile_info_bottom">';
        echo $logged_in_user_obj->getMutualFriends($username) . " Mutual friends";
      echo '</div>';
    }


    ?>

   </div>

   <div class="nav_bar">
    <ul class="nav nav-pills" role="tablist" id="profileTabs">
        <li class="nav-item">
          <a class="nav-link active" href="#newsfeed_div" aria-controls="newsfeed_div" role="tab" data-toggle="tab">Newsfeed</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="#about_div" aria-controls="about_div" role="tab" data-toggle="tab">About</a>
        </li>

        <?php
        if($logged_in_user_obj->getUsername() != $profile_user_obj->getUsername()){
          echo "<li class='nav-item'>
                  <a class='nav-link' href='#messages_div' aria-controls='messages_div' role='tab' data-toggle='tab'>Messages</a>
                </li>";
        }

        ?>
      </ul>
    </div>


	<div class="profile_main_column column">

    

    <div class="tab-content">

      <!--tab-pane fade show active: allows newsfeed to show as default-->
      <div role="tabpanel" class="tab-pane fade show active" id="newsfeed_div">
        <?php
        if($logged_in_user_obj->isFriend($profile_user_obj->getUsername())){

          echo "<div class='post_div'>
                  <div class='post_form_header'>
                    <p>Create Post</p>
                  </div>
                  <form class='post_form' action='" . $profile_user_obj->getUsername() . "' method='POST' enctype='multipart/form-data'>
                    
                    <input type='file' name='fileToUpload' id='fileToUpload'>
                    <textarea name='post_text' id='post_text' placeholder='Got something to say?'></textarea>
                    <input type='submit' name='post' id='post_button' value='Post'>
                  </form>
                </div>";

        }
        
        ?>

        <div class="posts_area"></div>
        <img id="loading" src="assets/images/icons/loading.gif">
      </div>

      <div role="tabpanel" class="tab-pane fade" id="about_div">
        <p> Relationship Status: <?php echo $profile_user_obj->getRelationshipStatus(); ?></p>
        <hr>
        <p> Email: <?php echo $profile_user_obj->getEmail(); ?></p>
        <hr>
        <p> GamerTags: <?php echo $profile_user_obj->printGamerTags(); ?></p>
      </div>

      <div role="tabpanel" class="tab-pane fade" id="messages_div">
      <?php  
        

          echo "<h4>You and <a href='" . $username ."'>" . $profile_user_obj->getFirstAndLastName() . "</a></h4><hr><br>";

          echo "<div class='loaded_messages' id='scroll_messages'>";
            echo $message_obj->getMessages($username);
          echo "</div>";
        ?>



        <div class="message_post">
          <form action="" method="POST">
              <textarea name='message_body' id='message_textarea' placeholder='Write your message ...'></textarea>
              <input type='submit' name='post_message' class='info' id='message_submit' value='Send'>
          </form>

        </div>

        <script>
          var div = document.getElementById("scroll_messages");
          div.scrollTop = div.scrollHeight;
        </script>
      </div>

    </div>

		


	</div>


<script>
  var userLoggedIn = '<?php echo $userLoggedIn; ?>';
  var profileUsername = '<?php echo $username; ?>';

  $(document).ready(function() {

    $('#loading').show();

    //Original ajax request for loading first posts 
    $.ajax({
      url: "includes/handlers/ajax_load_profile_posts.php",
      type: "POST",
      data: "page=1&userLoggedIn=" + userLoggedIn + "&profileUsername=" + profileUsername,
      cache:false,

      success: function(data) {
        $('#loading').hide();
        $('.posts_area').html(data);
      }
    });

    $(window).scroll(function() {
      var height = $('.posts_area').height(); //Div containing posts
      var scroll_top = $(this).scrollTop();
      var page = $('.posts_area').find('.nextPage').val();
      var noMorePosts = $('.posts_area').find('.noMorePosts').val();

      if ((document.body.scrollHeight == document.body.scrollTop + window.innerHeight) && noMorePosts == 'false') {
        $('#loading').show();

        var ajaxReq = $.ajax({
          url: "includes/handlers/ajax_load_profile_posts.php",
          type: "POST",
          data: "page=" + page + "&userLoggedIn=" + userLoggedIn + "&profileUsername=" + profileUsername,
          cache:false,

          success: function(response) {
            $('.posts_area').find('.nextPage').remove(); //Removes current .nextpage 
            $('.posts_area').find('.noMorePosts').remove(); //Removes current .nextpage 

            $('#loading').hide();
            $('.posts_area').append(response);
          }
        });

      } //End if 

      return false;

    }); //End (window).scroll(function())


  });

  </script>





	</div>
</body>
</html>