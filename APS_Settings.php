<?php
# Check if class already exits
if(!class_exists("APS_Settings")){
	/**
	* Class : APS_Settings
	*/
	class APS_Settings
	{
		
		public function __construct(array $args=null)
		{
			#Access Facebook Function
			$this->facebook= $args['facebook'];
			
			# Register actions
			add_action("admin_init",array(&$this,"admin_init"));
			add_action("admin_menu",array(&$this,"add_menu"));
		} # END Function : __construct

		public function admin_init(){
			# Register Settings
			register_setting("APS_Setting-group","APS_post_types");
			register_setting("APS_Setting-group","APS_FB_app_id");
			register_setting("APS_Setting-group","APS_FB_secret_key");

			if(isset($_GET['code']) and $_GET['code']!=""){
					$user_data = $this->facebook->get_user();
					add_option("APS_FB_profile_ids",array($user_data['id']));
					$get_APS_FB_profile_data = get_option("APS_FB_profile_data");
					add_option("APS_FB_profile_data",$user_data);
					add_action('admin_notices',array(&$this, 'showAdminMessages')); # Added on V 1.2
			}

			# Add Settings Sections
			$get_APS_FB_profile_ids = get_option("APS_FB_profile_ids");
			if(""!=get_option("APS_FB_secret_key") and ""!=get_option("APS_FB_app_id") and !$get_APS_FB_profile_ids){
				add_settings_section(
					"APS_Setting-FB-connect-section",
					"Connect To Facebook",
					array(&$this,"APS_settings_FB_section"),
					"aps-setting"
				);				
			}

			add_settings_section(
				"APS_Setting-section",
				"Automatic Post Share Settings",
				array(&$this,"APS_settings_section"),
				"aps-setting"
			);			

			# Add Settings Fields
			add_settings_field(
				"APS_post_types-field",
				"Post Types",
				array(&$this,"settings_field_select_post_type"),
				"aps-setting",
				"APS_Setting-section",
				array(
					"field"=>"APS_post_types",
					"label_for"=>"APS_post_types"
				)
			);

			add_settings_field(
				"APS_FB_app_id-field",
				"Facebook App Id",
				array(&$this,"settings_field_input_text"),
				"aps-setting",
				"APS_Setting-section",
				array(
					"field"=>"APS_FB_app_id",
					"label_for"=>"APS_FB_app_id"
				)
			);

			add_settings_field(
				"APS_FB_secret_key-field",
				"Facebook Secret Key",
				array(&$this,"settings_field_input_text"),
				"aps-setting",
				"APS_Setting-section",
				array(
					"field"=>"APS_FB_secret_key",
					"label_for"=>"APS_FB_secret_key"
				)
			);
		} # END Function : admin_init
		public function showAdminMessages()
		{
			echo  '<div id="setting-error-settings_updated" class="updated settings-error"> 
<p><strong>Connected to Facebook.</strong></p></div>';
		}  # END Function : showAdminMessages # Added on V 1.2
		public function APS_settings_section(){
			if(!$this->facebook->check_status())
                echo "<a href=''>Error: Please Reload The Page.</a>";
		} # END Function : APS_settings_section

		public function APS_settings_FB_section(){
			echo "<a href='".$this->facebook->login_url()."'>Connect To facebook</a>";
		} # END Function : APS_settings_FB_section

		public function settings_field_input_text($args){
			$field = $args['field'];
			$value= get_option($field);			

			echo sprintf('<input type="text" name="%s" id="%s" value="%s" />', $field, $field, $value);
		} # END Function : settings_field_input_text

		public function settings_field_select_post_type($args){
			$field = $args['field'];
			$value= ((!get_option($field))?array():get_option($field));
			$post_types=get_post_types(array("public"=>1));

			echo sprintf('<select name="%s[]" id="%s" multiple>', $field, $field);
			foreach ($post_types as $key => $post_type) {
				if($post_type!="attachment")
					echo '<option value="'.$key.'" '.((in_array($key,$value))?"selected":"").'>'.ucfirst($post_type).'</option>';
			}
			echo "</select>";
		} # END Function : settings_field_select_post_type

		public function add_menu(){
			add_options_page(
				"Automatic Post Share",
				"APS Settings",
				"manage_options",
				"aps-setting",
				array(&$this,"load_setting_form")
			);
		} # END Function : add_menu

		public function load_setting_form(){
			# Check if current user have permission
			if(!current_user_can("manage_options"))
				wp_die(__('You do not have sufficient permissions to access this page.'));

			include(sprintf("%s/templates/template-setting.php",dirname(__FILE__)));
		} # END Function : load_setting_form
	} # END Class : APS_Settings
} # END : if(!class_exists("APS_Settings"))