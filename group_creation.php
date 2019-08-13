<?php 
include("includes/header.php");
?>

<div class="group_creation_form">
    <form action="group_creation.php" method="POST">

        <p>Name your group</p>
        <input type="text" name="group_name" placeholder="Group Name" value="<?php     
            if(isset($_SESSION['group_name'])) {
                echo $_SESSION['group_name'];
            }
        ?>" required>

        <p>Invite People</p>
        <input type="text" name="group_name" placeholder="Group Name" value="<?php     
            if(isset($_SESSION['group_name'])) {
                echo $_SESSION['group_name'];
            }
        ?>" required>
        
    </form>
</div>

</div>
</body>
</html>