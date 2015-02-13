<?php
session_start();
include_once "./includes/session-member.php";
include_once "./admin/includes/functions.php";
include_once "./includes/functions.php";

include_once "./includes/Member.class.php";
include_once "./includes/dev.class.php";
//require_once "./includes/Company.class.php";
//include_once "./includes/Blog_post.class.php";
//require_once "./includes/Match.class.php";
//include_once "./includes/Member_image.class.php";
//require_once "./includes/class.connections.php";
include_once "./includes/Community.functions.php";

require_once './php/autoloader.php';

log_page_view();

$open_page = true;

if(!isset($_SESSION['member_id'])) {	
	$hide_header_nav = true;
}

if(!isset($_GET['id']) || $_GET['id'] == '') { //if get or session id are missin then gtfo	
	include_once "./includes/header-ui.php";
	echo '<p class="text-center alert alert-warning"><strong>Member not found. Try again later.</strong></p>';
	echo '<div class="push-footer"></div>';
    include_once "./includes/footer-ui.php";
    exit;
}

//get the id passed in URL
$group_id = $_GET['id']; 

//init Groups
$groups = new Groups($db, $member_id);

$group_members = new Group_members($db);

//build group properties
$groups->fetch_group_by_id($group_id);

  

//need to pass header information before including header
// SEO
$group_meta_title = $groups->get_group_meta_title();	
$group_meta_keywords = $groups->get_group_keywords();
$group_meta_description = $groups->get_group_meta_description();

if($group_meta_title) { $title = $group_meta_title; } else { $title = $group_title; }
if($group_meta_keywords) { $keywords = $group_meta_keywords; }
if($group_meta_description) { $description = $group_meta_description; }
// EO seo

//check if group exists with this id
$sql = "SELECT * FROM `groups` WHERE `id` = ".sql_c(intval($group_id))." AND `approved` = 1";
$result = @mysql_query($sql,$db); check_sql(mysql_error(), $sql, 0);

if( !$result || mysql_num_rows($result) == 0 ) { //if no result or no group found with this id, show appropriate error message
  include_once "./includes/header-ui.php";
  echo '<p class="text-center alert alert-warning"><strong>We could not find the Group you seem to be looking for.</strong></p>';
  echo '<div class="push-footer"></div>';
  include_once "./includes/footer-ui.php";
  exit;           
}

//get group member id array
$group_member_id_array = $group_members->get_member_id_arr($group_id);

//if invited -- this is not reliable ( like if member is not logged in it removes the params ) 
//better way would be to get the array of invited members and if the member visits the page
//to prompt them to join the group instead of insta joining 

$invited_members_array = $group_members->get_invited_member_id_arr($group_id);

/*if($_SESSION['member_id'] == 35669) {
	if(in_array($_SESSION['member_id'], $invited_members_array)) { echo "EXISTS"; } else { echo "NOPE"; }
}*/

if(isset($_GET['invited'])) {
	
	
	$group_members->add_member_to_group($_SESSION['member_id'], $_GET['id']);
	
} 


//put Group properties in vars
$group_title = $groups->get_group_title();
$about_us = $groups->get_group_about();
$join_us = $groups->get_group_join_us();
$contact_us = $groups->get_group_contact();



$group_super_admin_id = $groups->get_group_super_admin();

//check if member is group owner
//$is_group_owner = ($member_id == $groups->get_group_super_admin()) ? true : false;

$is_group_owner = $group_members->is_member_group_owner($member_id, $group_id);

$is_group_admin = $group_members->is_member_group_admin($member_id, $group_id);

//add group_id to session if group owner is viewing
if($is_group_owner || $is_group_admin) {

	$_SESSION['group_id'] = $group_id;	 

}

//check if group is set to private
$group_privacy = $groups->get_group_privacy();

//check if member is a group member
$is_group_member = $group_members->is_member_in_group($member_id, $group_id);

$group_approved = $groups->get_group_approve_status() == 0 ? false : true; //if approved == 0 then group_approved will return false, else will return true



if(!$group_approved) { //if group is not approved show appropriate message and exit the logic below
	
	echo '<div id="right-col-wide"><div class="alert alert-warning text-center"><strong>Please Wait! </strong>This group has not been approved yet. You will be notified by email when the approval process is complete.</div></div>';	
	
	include_once "./includes/footer.php";
	
	exit;
}

$group_logo = img_src("groups", $group_id, "group_logo_1", 'height', 400, 190, 0, 0, 0, 90);
 

$group_badge = img_src("groups", $group_id, "group_badge_1", 'crop', 20, 20, 0, 0, 0, 90);


$title = $group_title . ' ' .  " - " . " Group ";
$description = $group_title . "'s Group";


include_once "./includes/header-ui.php";
?>

<style type="text/css">
.body.profile {
    margin-top: 50px;
}
.g_marker_lable {
     color: black;
     background-color: white;
     font-family: "Lucida Grande", "Arial", sans-serif;
     font-size: 16px;
     text-align: center;
     width: 200px;
   }
   
/*Nav*/   
.navbar-inverse .navbar-nav>li>a:hover {
  background-color: #009EC0;
}

.navbar-inverse .navbar-nav>.active>a, .navbar-inverse .navbar-nav>.active>a:hover, .navbar-inverse .navbar-nav>.active>a:focus {
  color: #FFF;
  background-color: #009EC0;
  border: 0px solid #009EC0;
}

.navbar-inverse .navbar-collapse, .navbar-inverse .navbar-form {
  border-color: #101010;
  background-color: #FFF;
}

.navbar-inverse .navbar-brand:hover{
  color: #000;
}

.navbar-header {
  background-color: #FFF;
}

.navbar-inverse .navbar-toggle .icon-bar {
  background-color: #000;
}

.navbar-inverse .navbar-toggle:hover {
  background-color: #FFF;
}

.navbar-toggle .icon-bar:nth-of-type(2) {
	  top: 1px;
}

.navbar-toggle .icon-bar:nth-of-type(3) {
  	top: 2px;
}

.navbar-toggle .icon-bar {
	  position: relative;
	  transition: all 500ms ease-in-out;
}

.navbar-toggle.active .icon-bar:nth-of-type(1) {
	  top: 6px;
	  transform: rotate(45deg);
}

.navbar-toggle.active .icon-bar:nth-of-type(2) {
	  background-color: transparent;
}

.navbar-toggle.active .icon-bar:nth-of-type(3) {
	  top: -6px;
	  transform: rotate(-45deg);
}

.navbar-inverse{
  border:none;
  background-color: none;
}

.navbar-nav>li {
  margin: 15px;
}

	
	/* Custom, iPhone Retina */ 
	@media only screen and (min-width : 320px) {
	}

	/* Extra Small Devices, Phones */ 
	@media only screen and (min-width : 480px) {
	}

	/* Small Devices, Tablets */
	@media only screen and (min-width : 768px) {
		a.navbar-brand{
			display:none;	
		}
	}

	/* Medium Devices, Desktops */
	@media only screen and (min-width : 992px) {
		a.navbar-brand{
			display:none;	
		}
	}

	/* Large Devices, Wide Screens */
	@media only screen and (min-width : 1200px) {
		a.navbar-brand{
			display:none;	
		}
	}
   
</style>

<div class="body container profile">
	<section class="content">
    	<div class="row">
        	<div class="inner-container">
            	<div class="col-md-6">
                	<img src = "<?php echo $group_logo; ?>" alt = "" style="height:90px; max-width:300px;"/>
                </div>
                
                <div class="col-md-6 text-right" style="padding-top: 15px;">  
                	<?php if(!$is_group_owner && !$is_group_member && $_SESSION['anon_user'] == 'false') { ?><p class="text-center"><a id="group_join_request" class="btn btn-tne" href="#" style="font-size: 16px; padding: 10px;"><i class="fa fa-user-plus"></i>Request to Join</a></p><?php } //Dont show Join button if owner or group member ?>					                  
                </div> 
            </div><!-- / inner container -->
        </div><!-- / row -->
        
        <div class="row business-type">        
            <div class="inner-container">
                <div class="col-md-12"> <img src="/images/ui/business-type.png" /></div>
            </div>    
	    </div> <!-- EO .row -->
        
        <div class="row group">
            <div class="col-md-12">
                <div class="row">
                    <div class="text-center col-md-12">
                        <h2>
                            <?php echo $group_title; ?>
                        </h2>
                    </div>
                </div><!-- /.row -->                                                              
            </div><!-- EO col-md-12 -->
        </div><!-- EO .row -->   
        
        <div class="row">
            <div class="col-md-12"><hr /></div>
        </div>

        <div class="row">
        	<div class="col-md-12">
            	<!--Nav-->
                <div class="navbar navbar-inverse ">
                  <div class="navbar-header">
                    <button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-collapse">
                      <span class="icon-bar"></span>
                      <span class="icon-bar"></span>
                      <span class="icon-bar"></span>
                    </button>
                    <a class="navbar-brand" href="#"><?php echo $group_title; ?></a>
                  </div>
                  <div class="navbar-collapse collapse">
                    <ul class="nav navbar-nav"> 
                      <li class="active"><a class="link" href="#about">ABOUT</a></li>
                      <li class=""><a class="link" href="#join">JOIN</a></li>
                      <li class=""><a class="link" href="#contact">CONTACT</a></li>
                      <li class=""><a class="link" href="#whats_happening">WHAT'S HAPPENING</a></li>
                      <li class=""><a class="link" href="#discussion">DISCUSSION BOARD</a></li>  
                      <li class=""><a class="link" href="#members">MEMBERS</a></li>
                      <?php if($groups->get_group_rss()) { //Only show RSS Tab if RSS URL is added ?>
                      <li class=""><a class="link" href="#news">NEWSFEED</a></li>   
                      <?php } ?>                      
                    </ul>
                  </div>
                </div>
                <!-- / Nav -->
                <style type="text/css">
				.content-box.active{
					display:block;	
				}
				.content-box{
					display:none;
				}
				</style>
                <div class="row col-md-12" style="position:relative; height:auto" id="content">
                	<div class="content-box active" id="about">
                    	<?php echo $about_us; ?>;
                    </div>
                    
                    <div class="content-box" id="join">
                    	<?php echo $join_us; ?>;
                    </div>
                    
                    <div class="content-box" id="contact">
                    	<?php echo $contact_us; ?>;
                    </div>
                    
                    <?php
                      
					  //check group privacy status
					  if ($group_privacy == 1) {
											
						//if group is private, check if member is owner or group_member									
						if($is_group_member || $is_group_owner) {
										 
					 ?>
                    
                    <div class="content-box" id="whats_happening">
						<?php include_once "./group_includes/group_blog.php"; ?>
                    </div>
                    
                    <div class="content-box" id="discussion">
                    	<?php include_once "./group_includes/discussion_board.php"; ?>
                    </div>
                    
                    <div class="content-box" id="members">
                    	<?php include_once "./group_includes/group_members.php"; ?>
                    </div>
                    
                    <div class="content-box" id="news">
                    	<?php include_once "./group_includes/whats_happening.php"; ?>
                    </div>
                    
                    <?php
						//if neither group owner or group member then show appropriate message
						} else {
							
					?>
                    
                    <div class="content-box" id="whats_happening">
                      <p class="text-center"><i class="icon-lock"></i>&nbsp; <strong>This is a private Group</strong></p>
                    </div>
                    
                    <div class="content-box" id="discussion">
                      <p class="text-center"><i class="icon-lock"></i>&nbsp; <strong>This is a private Group</strong></p>
                    </div>
                    
                    <div class="content-box" id="members">
                      <p class="text-center"><i class="icon-lock"></i>&nbsp; <strong>This is a private Group</strong></p>
                    </div>
                    
                    <div class="content-box" id="news">
                      <p class="text-center"><i class="icon-lock"></i>&nbsp; <strong>This is a private Group</strong></p>
                    </div>

					<?php
                    
                        } // end group owner or group member check
                        
                    } else { //group is not set to private, show everything ?>	
            				
                  <div class="content-box" id="whats_happening">
						<?php include_once "./group_includes/group_blog.php"; ?>
                    </div>
                    
                    <div class="content-box" id="discussion">
                    	<?php include_once "./group_includes/discussion_board.php"; ?>
                    </div>
                    
                    <div class="content-box" id="members">
                    	<?php include_once "./group_includes/group_members.php"; ?>
                    </div>
                    
                    <div class="content-box" id="news">
                    	<?php include_once "./group_includes/whats_happening.php"; ?>
                    </div>
                    
                	<?php } // end group privacy check ?>
                    
                </div>
            </div>
        </div>
<script>
$(document).ready(function () {
	$(".navbar-toggle").on("click", function () {
		$(this).toggleClass("active");
	});
	
	$(".nav a").on("click", function(){
		$(".nav").find(".active").removeClass("active");
		$(this).parent().addClass("active");
	});
	
	$(".navbar-inverse .navbar-nav > li > a.link").on("click", function(e){
		
		e.preventDefault();
		
		var get_id = $(this).attr("href");
		
		console.log(get_id);
		
		$("div" + get_id).addClass('active');
		
		//any content box that is not this id - remove active
        $("div.content-box").not(get_id).removeClass('active');
	});
	
});
</script>
    </section>
</div>

<?php include_once "./includes/footer-ui.php"; ?>
