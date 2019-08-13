<?php 
include("includes/header.php");



if(isset($_GET['g'])){
	$group_name = $_GET['g'];
	$group_details_query = mysqli_query($con, "SELECT * FROM groups WHERE name='$group_name'");
	$group = mysqli_fetch_array($group_details_query);

  $num_members = (substr_count($group['member_array'], ",")) - 1;
  
  $group_obj = new Group($con, $group_name);
  $logged_in_user_obj = new User($con, $userLoggedIn);

  $group_message_obj = new GroupMessage($con, $group_name, $userLoggedIn);
}




if(isset($_POST['remove_user'])) {
	$user = new User($con, $userLoggedIn);
	$user->removeMember($username);
}

if(isset($_POST['add_user'])) {
	$user = new User($con, $userLoggedIn);
	$user->sendRequest($group);
}
if(isset($_POST['respond_request'])) {
	header("Location: requests.php");
}

if(isset($_POST['post_message'])){
  if(isset($_POST['message_body'])){
    $body = mysqli_real_escape_string($con, $_POST['message_body']);
    $date = date("Y-m-d H:i:s");
    $group_message_obj->sendMessage($body, $date);
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
    $post->submitPost($_POST['post_text'], $userLoggedIn, $imageName);
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
 		<form action="<?php echo $group_name; ?>" method="POST">
 			<?php 
 			 
 			if($group_obj->isClosed()) {
 				header("Location: user_closed.php");
 			}

 			

            if($group_obj->isMember($userLoggedIn)) {
                echo '<input type="submit" name="remove_friend" class="danger" value="Remove Friend"><br>';
            }
            else if ($group_obj->didReceiveRequest($userLoggedIn)) {
                echo '<input type="submit" name="respond_request" class="warning" value="Respond to Request"><br>';
            }
            else if ($group_obj->didSendRequest($userLoggedIn)) {
                echo '<input type="submit" name="" class="default" value="Request Sent"><br>';
            }
            else 
                echo '<input type="submit" name="add_friend" class="success" value="Add Friend"><br>';

 			

 			?>
 		</form>

    <?php  
    
    echo '<div class="profile_info_bottom">';
        echo $group_obj->getMutualFriends($userLoggedIn) . " Mutual friends";
    echo '</div>';
    


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
        
        echo "<li class='nav-item'>
                <a class='nav-link' href='#messages_div' aria-controls='messages_div' role='tab' data-toggle='tab'>Messages</a>
            </li>";
        

        ?>
    </ul>
  </div>


	<div class="profile_main_column column">

    

    <div class="tab-content">

      <!--tab-pane fade show active: allows newsfeed to show as default-->
      <div role="tabpanel" class="tab-pane fade show active" id="newsfeed_div">
        <?php
        if($group_obj->isMember($logged_in_user_obj->getUsername())){

          echo "<div class='post_div'>
                  <div class='post_form_header'>
                    <p>Create Post</p>
                  </div>
                  <form class='post_form' action='" . $group_obj->getName() . "' method='POST' enctype='multipart/form-data'>
                    
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
        <p> Game Name: <?php echo $group_obj->getGameName(); ?></p>
        <hr>
        <p> Email: <?php ?></p>
        <hr>
        <p> GamerTags: <?php  ?></p>
      </div>

      <div role="tabpanel" class="tab-pane fade" id="messages_div">
      <?php  
        

          echo "<h4>" . $group_name . " Chat</h4><hr><br>";

          echo "<div class='loaded_messages' id='scroll_messages'>";
            echo $group_message_obj->getMessages();
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
      url: "includes/handlers/ajax_load_group_posts.php",
      type: "POST",
      data: "page=1&userLoggedIn=" + userLoggedIn + "&groupName=" + groupName,
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
          url: "includes/handlers/ajax_load_group_posts.php",
          type: "POST",
          data: "page=" + page + "&userLoggedIn=" + userLoggedIn + "&groupName=" + groupName,
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