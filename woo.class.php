<?php 
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');


class WPLMSCertificated{

	public $plugin_name = 'wplms_cert_ray';    
	public $user_id;  
	public function __construct(){
		@session_start();
		$this->run();	 

	}

	public static function init() {
		$class = __CLASS__;
		new $class;
	}
	public function run(){

	/** -----
	Options Setting Panel 
	------**/ 
	add_action('wp_enqueue_scripts',array( $this, 'plugin_scripts'),0);
	add_action('admin_enqueue_scripts',array( $this, 'plugin_scripts'),0);

	add_action( 'wp_ajax_nopriv_save_sizes', array( $this,'save_fields' ));
	add_action( 'wp_ajax_save_sizes',array( $this, 'save_fields' ));
	add_action('wplms_cert_generated',array( $this, 'wplms_cert_generated' ));

		/**---------------
		Activating a new my sizes tab in the account page
		----------------------**/


		//add_action( 'init',  array($this,'wplms_content_cert') );
		add_action('wplms_evaluate_course',  array($this,'certificate_earned'),1,3); 
		add_action( 'show_certificated',  array($this,'get_certificated'),1  );

		if(!isset($_POST['review']) and empty($_POST['review'])){
			unset($_SESSION['wplms-certificated']);
			add_action( 'bp_before_course_body',  array($this,'get_certificated'),1  );
		}
		
		


	}
 
	public function get_certificated(){

		return $this->check_user_certificated(get_current_user_id(), get_the_id());
	}
	private function encryptIt($string, $key = "uiuyausyuiqwugheghwbehwehwegasiu") {
		$result = '';


		for($i=0; $i<strlen($string); $i++) {
			$char = substr($string, $i, 1);
			$keychar = substr($key, ($i % strlen($key))-1, 1);
			$char = chr(ord($char)+ord($keychar));
			$result.=$char;
		}

		return urlencode(base64_encode($result));
	}

	private function decryptIt($string, $key = "uiuyausyuiqwugheghwbehwehwegasiu") {
		
		$result = '';
		$string = base64_decode(urldecode($string));
		
		for($i=0; $i<strlen($string); $i++) {
			
			$char = substr($string, $i, 1);
			$keychar = substr($key, ($i % strlen($key))-1, 1);
			$char = chr(ord($char)-ord($keychar));
			$result.=$char;

		}

		return $result;
	}

	public function check_user_certificated($user_id, $course_id){
		
		if($_GET['force_del'] == 1){
			delete_user_meta( $user_id, 'certificate-'.$course_id);
		}
		// If is a cretificate page generated no showing


		$user_data_cert = get_user_meta( $user_id, 'certificate-'.$course_id, true);
		if(!empty($user_data_cert) and isset($user_data_cert)){

			$user_data_cert = maybe_unserialize( $user_data_cert);				 
			return do_action(  'wplms_cert_generated' , $user_data_cert  );

		}

	}

	public function certificate_earned($course_id, $califications, $user_id){
		
		if($_SESSION['wplms-certificated'] === true){
			return;
		}
		

		$this->user_data = wp_get_current_user();

		$author_id = get_post_field( 'post_author', $course_id ); 
		
		$user_name = $this->user_data->first_name.' '.$this->user_data->last_name; 
		$course_name = get_the_title( $course_id );
		$date_certificated = 'Certificado el '.current_time('F j, Y, g:i a');

		$author_data = get_userdata($author_id);  
		$author_name = $author_data->first_name.' '.$author_data->last_name; 
		$author_signature = get_field('firma', 'user_'.$author_id);
		$author_occupation = get_field('especialidad', 'user_'.$author_id);
		/*var_dump(attachment_url_to_postid($author_signature));
		die;*/
		$arr = [
			'user_id' => $user_id,
			'student_first_name' => $this->user_data->first_name,
			'student_name' => $user_name,
			'course_name' => $course_name,
			'course_id' => $course_id,
			'code_certificated' => 'Codigo de certificación: kasjnjahsuiywuywqahjshjash',
			'date_certificated' => $date_certificated,
			'instructor_name' => $author_name,
			'instructor_job' => $author_occupation,
			'instructor_signature' => $author_signature,
			'califications'=>$califications.'/100'

		];

		$this->create_certificated($arr);

	}


	private function text_center_certificated($arr){

	//$im = Image Source
		list($im, $txt, $font_size, $font_path, $color, $top_px, $left_px) = array_values($arr);

		$x = $left_px;

		if($left_px === false){

			$dimensions = imagettfbbox( $font_size, 0, $font_path, $txt);
			$textWidth = abs($dimensions[4] - $dimensions[0]);
			$x = (imagesx($im) - $textWidth) / 2;

		}


		return imagettftext($im,  $font_size, 0, $x, $top_px, $color, $font_path, $txt);


	}


	public function create_certificated($arr){

		$img_filepath = WPLMS_CERT_CURRENT_DIR.'assets/img/tpl.jpg';
		$img_signature = $arr['instructor_signature'];
		$im = imagecreatefromjpeg($img_filepath);

		// Create some colors 
		$black = imagecolorallocate($im, 52, 70, 92);

		$fontBold = WPLMS_CERT_CURRENT_DIR.'/fonts/Nunito-Black.ttf';
		$fontSemiBold = WPLMS_CERT_CURRENT_DIR.'/fonts/Nunito-SemiBold.ttf';
		$fontRegular = WPLMS_CERT_CURRENT_DIR.'/fonts/Nunito-Regular.ttf';

		$arr['filename'] = 'certificate-'.sanitize_title($arr['student_name']).'-'.sanitize_title($arr['course_name']).'-'.sanitize_title($arr['date_certificated']).'.jpg';

//Generating text

		$this->text_center_certificated([
			'im'=>$im, 
			'txt'=> $arr['course_name'], 
			'font_size'=> 40, 
			'font_path'=> $fontBold, 
			'color'=> $black, 
			'top_px'=> 320,
			'left_px'=> false,

		]); 


		$this->text_center_certificated([
			'im'=>$im, 
			'txt'=> $arr['student_name'], 
			'font_size'=> 40, 
			'font_path'=> $fontSemiBold, 
			'color'=> $black, 
			'top_px'=> 480, 
			'left_px'=> false,
		]); 

		$this->text_center_certificated([
			'im'=>$im, 
			'txt'=> $arr['date_certificated'], 
			'font_size'=> 25, 
			'font_path'=> $fontRegular, 
			'color'=> $black, 
			'top_px'=> 860, 
			'left_px'=> false,
		]);  
		$this->text_center_certificated([
			'im'=>$im, 
			'txt'=>  $arr['code_certificated'], 
			'font_size'=> 20, 
			'font_path'=> $fontRegular, 
			'color'=> $black, 
			'top_px'=> 960,
			'left_px'=> false,

		]); 
		
		$this->text_center_certificated([
			'im'=>$im, 
			'txt'=> $arr['instructor_name'], 
			'font_size'=> 20, 
			'font_path'=> $fontSemiBold, 
			'color'=> $black, 
			'top_px'=> 1100,
			'left_px'=> 1200,


		]); 

		$this->text_center_certificated([
			'im'=>$im, 
			'txt'=> $arr['instructor_job'], 
			'font_size'=> 20, 
			'font_path'=> $fontRegular, 
			'color'=> $black, 
			'top_px'=> 1135,
			'left_px'=> 1200,

		]); 


		$sign = imagecreatefrompng($img_signature);
		
		list($width, $height) = getimagesize($img_filepath);
		list($newwidth, $newheight) = getimagesize($img_signature);

		$out = imagecreatetruecolor($width, $height);
		imagecopyresampled($out, $im, 0, 0, 0, 0, $width, $height, $width, $height);
		imagecopyresampled($out, $sign, 1200, 970, 0, 0, $newwidth, $newheight, $newwidth, $newheight);

 		$save_img_data = $this->upload_cert($out, $arr); //Uploading certificated to wp folder
 		
 		if(!is_array($save_img_data) and !empty($save_img_data)){
 			throw new Exception('No certificated data provided!',   001 );
 		}

 		delete_user_meta( $arr['user_id'], 'certificate-'.$arr['course_id']);
 		add_user_meta( $arr['user_id'], 'certificate-'.$arr['course_id'], maybe_serialize( $save_img_data )  );
 		
 		$_SESSION['wplms-certificated'] = true;
 		do_action(  'wplms_cert_generated' , $save_img_data  );
 		imagedestroy($im);
 		imagedestroy($out); 
 		

 		


 	}

 	public function wplms_cert_generated($cert_data){

 		$this->wplms_certificated_tpl($cert_data); 
 	}

 	public function upload_cert($im, $arr){

 		$wordpress_upload_dir = wp_upload_dir();
 		$file_cert_name = $arr['filename']; 		
 		$new_file_path = $wordpress_upload_dir['path'] . '/'.$file_cert_name ;


 		if( imagejpeg($im, $new_file_path , 90    ) ) {

 			$upload_id = wp_insert_attachment( array(
 				'guid'           => $new_file_path,  
 				'post_mime_type' => 'image/jpeg',
 				'post_title'     => preg_replace( '/\.[^.]+$/', '', $file_cert_name ), 
 				'post_status'    => 'publish'
 			), $new_file_path );

	// wp_generate_attachment_metadata() won't work if you do not include this file
 			require_once( ABSPATH . 'wp-admin/includes/image.php' );

	// Generate and save the attachment metas into the database
 			wp_update_attachment_metadata( $upload_id, wp_generate_attachment_metadata( $upload_id, $new_file_path ) );

	// Show the uploaded file in browser

 			$arr['img_medium'] = wp_get_attachment_image_src($upload_id, 'large')[0];
 			$arr['img_full'] = wp_get_attachment_image_src($upload_id, 'full')[0];


 			return $arr ;

 		}
 	}


 	public function wplms_certificated_tpl($cert_data){

 		if(!isset($cert_data) and !is_array($cert_data)) {
 			throw new Exception('No certificated data provided',   404 );
 		}
 		extract($cert_data);
 		?>

 		<!-- Certification -->
 		<aside class="wrap-cert">
 			<div class="container">
 				<div class="row d-flex align-items-center">
 					<div class="col-md-7">
 						<div class="wrap-cert-img">
 							<a href="<?php echo $img_full ?>" title="Ver certificado" target="_blank"><img src="<?php echo $img_medium ?>" width="100%" /></a>
 							<div class="graph">
 								<?php include(WPLMS_CERT_CURRENT_DIR.'/assets/img/graph01.svg') ?>
 							</div>  
 						</div>

 					</div>
 					<div class="col-md-5">
 						<div class="wrap-cert-info">
 							<h3>Felicidades <strong class="student-name"><?php echo $student_first_name ?></strong></h3>
 							<div class="desc">
 								Pasaste la certificación de <br>
 								<strong class="course-title"><?php echo $course_name ?></strong> <br>
 								Tu calificación fue <strong class="puntuation"><?php echo $califications ?></strong>
 							</div>
 							<div class="wrap-links">
 								<a href="<?php echo $img_full ?>" class="btn-one btn-download" download>Descargar</a>
 								<a href="https://www.linkedin.com/profile/add?startTask=CERTIFICATION_NAME" id="btn-linkedin-certification" target="_blank" class="btn-one btn-linkedin" >Agregar a Linkedin</a>

 							</div>
 						</div>
 					</div>
 				</div>
 			</div>
 		</aside>
 		<!-- end Certification -->
 		<?php
 	}





 	public function plugin_scripts()
 	{

 		/** CSS **/

 		//wp_register_style( $this->plugin_name.'-css', WPLMS_CERT_CURRENT_URL . 'assets/css/style.css');  

 		/** JS **/ 
/*
 		wp_register_script( $this->plugin_name.'-js', WPLMS_CERT_CURRENT_URL . 'assets/js/main.js', '', '', true);

 		wp_enqueue_script(  $this->plugin_name.'-js' );
 		wp_enqueue_style ( $this->plugin_name.'-css');


 		$args =  array(
 			'ajax_url' => admin_url( 'admin-ajax.php' ),
 			'site_url' => get_site_url(),
 			'title' => get_bloginfo('name'),
 			'ajax_nonce' => wp_create_nonce('save_data'),
 		);


 		wp_localize_script( $this->plugin_name.'-js', 'wplmsCert', $args);*/
 	}




 } 