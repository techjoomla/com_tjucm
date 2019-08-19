package pageObjects;

import java.util.List;

import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import org.openqa.selenium.support.How;
//import org.testng.annotations.Factory;

//import com.gargoylesoftware.htmlunit.javascript.host.media.webkitMediaStream;

import config.BasicClass;
public class adminObject extends BasicClass {

	//login 
	@FindBy(how = How.ID, using = "mod-login-username")
	public static WebElement username;

	@FindBy(how = How.ID, using = "mod-login-password")
	public static WebElement password;

	@FindBy(how = How.XPATH, using = "//*[@id=\"form-login\"]/fieldset/div[3]/div/div/button")
	public static WebElement login;
	
	//admin form creation
	@FindBy(how = How.XPATH, using ="//*[@id=\"menu\"]/li[5]/a")
	public static WebElement components;
	
	@FindBy(how = How.XPATH, using = "//*[@id=\"menu\"]/li[5]/ul/li[17]/a")
	public static WebElement tjucm ;
	
	@FindBy(how = How.XPATH, using ="//*[@id=\"toolbar-new\"]/button")
	public static WebElement newType;  
	
	@FindBy(how = How.XPATH, using ="//a[text()='Types']")
	public static WebElement Type; 
	
	@FindBy(how = How.NAME, using="jform[title]")
	public static WebElement titleName;
	
	@FindBy(how = How.XPATH, using="//a[text()='Permissions']")
	public static WebElement permission;
	
	@FindBy(how = How.XPATH, using = "//div[@class='tab-pane active'][@id='permission-1']//select[@id='jform_rules_core.type.createitem_1']")
	public static WebElement permissionselect;
	
	@FindBy(how = How.XPATH, using="//a[@href='#permission-2']")
	public static WebElement permission2;
	
	@FindBy(how = How.XPATH, using="//div[@class='tab-pane active'][@id='permission-2']//select[@id='jform_rules_core.type.createitem_2']")
	public static WebElement permission_2;
	
	@FindBy(how = How.XPATH, using="//div[@class='tab-pane active'][@id='permission-2']//select[@id='jform_rules_core.type.viewitem_2']")
	public static WebElement viewAll;
	
	@FindBy(how = How.XPATH, using="//div[@class='tab-pane active'][@id='permission-2']//select[@id='jform_rules_core.type.editownitem_2']")
	public static WebElement EditOwnItem;
	
	@FindBy(how = How.XPATH, using ="//*[@id=\"toolbar-save\"]/button")
	public static WebElement save_closetype; 
	
	@FindBy(how = How.XPATH, using = "//table[@id='typeList']//tr[contains(@class,'row')]//a[text()='Field Group']")
	public static List<WebElement> field_groupcount;
	
	@FindBy(how = How.XPATH, using = "//button[@class='btn btn-small button-new btn-success']")
	public static WebElement save_field_group;
	
	@FindBy(how = How.NAME, using ="jform[name]")
	public static WebElement group_name;
	
	@FindBy(how = How.XPATH, using = "//button[@class='btn btn-small button-save']")
	public static WebElement save_groupname;
	
	@FindBy(how = How.XPATH, using = "//ul[@id='submenu']//a[text()='Types']")
	public static WebElement click_type;
	
	@FindBy(how = How.XPATH, using= "//table[@id='typeList']//tr[contains(@class,'row')]//a[text()='Fields']")
	public static List<WebElement> field_typecount;
	
	@FindBy(how = How.XPATH, using="//button [@class='btn btn-small button-new btn-success']")
	public static WebElement click_field;
	
	@FindBy(how = How.NAME, using = "jform[label]")
	public static WebElement form_label;
	
	@FindBy(how = How.NAME, using = "jform[name]")
	public static WebElement form_name; 
	
	@FindBy(how = How.XPATH, using = "//div[@class='chzn-container chzn-container-single']//a[@class='chzn-single']")
	public static WebElement click_field_type;
	
	@FindBy(how = How.XPATH, using = "//ul/li[text()='Text']")
	public static WebElement select_field;
	
	@FindBy(how = How.XPATH, using = "//fieldset[@id='jform_required']//label[@class='btn']")
	public static WebElement text_required;
	
	@FindBy(how = How.XPATH, using = "//fieldset[@id='jform_showonlist']//label[@class='btn']")
	public static WebElement text_showonlist;
	
	@FindBy(how =How.XPATH, using = "//button[@class='btn btn-small button-save-new']")
	public static WebElement text_save;

	@FindBy(how =How.XPATH, using = "//button[@class='btn btn-small button-save']")
	public static WebElement saveandclose;
	
	@FindBy(how = How.XPATH, using = "//input[@id='jform_label']")
	public static WebElement lable;
	
	@FindBy(how = How .XPATH, using = "//input[@id='jform_name']")
	public static WebElement lastname;
	
	@FindBy(how = How.XPATH, using = "//ul/li[text()='Text']")
	public static WebElement selectfield1;
	
	@FindBy(how = How.XPATH, using = "//ul/li[text()='Video']")
	public static WebElement selectVideo;
	
	@FindBy(how = How.XPATH, using = "//ul/li[text()='Audio']")
	public static WebElement selectAudio;
	
	@FindBy(how = How.XPATH, using = "//ul/li[text()='Audio']")
	public static WebElement selectCluster;
		
	@FindBy(how = How.XPATH, using = "//input[@id='jform_name']")
	public static WebElement radio_name;
	
	@FindBy(how = How.XPATH, using ="//ul/li[text()='Radio']")
	public static WebElement select_radio;

	@FindBy(how = How.XPATH, using = "//button[@id='add']")
	public static WebElement radiobutton;
	
	@FindBy(how = How.XPATH, using = "//input[@id='tjfields_optionname_0']")
	public static WebElement optionvalue1;
	
	@FindBy(how = How.XPATH, using = "//input[@id='tjfields_optionvalue_0']")
	public static WebElement optionname1; 
	
	@FindBy(how = How.XPATH, using ="//input[@id='tjfields_optionname_1']")
	public static WebElement optionvalue2;
	
	@FindBy(how = How.XPATH, using ="//input[@id='tjfields_optionvalue_1']")
	public static WebElement optionname2;
	
	@FindBy(how = How.XPATH, using = "//input[@id='jform_name']")
	public static WebElement numbername; 
	 
	@FindBy(how = How.XPATH, using = "//ul/li[text()='Number']")
	public static WebElement select_number;
	
	@FindBy(how = How.XPATH, using = "//input [@id='jform_params_min']")
	public static WebElement minNumber;
	
	@FindBy(how = How.XPATH,using = "//input[@id='jform_name']")
	public static WebElement emailname;
	
	@FindBy(how = How.XPATH,using = "//ul/li[text()='Email']")
	public static WebElement select_email;
	
	@FindBy(how = How.XPATH,using = "//input[@id='jform_name']")
	public static WebElement datename;
	
	@FindBy(how = How.XPATH,using = "//ul/li[text()='Calendar']")
	public static WebElement select_clender;
	
	@FindBy(how = How.XPATH,using = "//input[@id='jform_name']")
	public static WebElement Nationalityname;	
	
	@FindBy(how = How.XPATH,using =       "//ul/li[text()=\"Single Select (Deprecated. Use Field Type 'List')\"]")
	public static WebElement select_singl;
	
	@FindBy(how = How.XPATH,using = "//input[@id='tjfields_optionname_0']")
	public static WebElement countryname1;	
	
	@FindBy(how = How.XPATH,using = "//input[@id='tjfields_optionvalue_0']")
	public static WebElement countryvalue1;
	
	@FindBy(how = How.XPATH,using = "//input[@id='tjfields_optionname_1']")
	public static WebElement countryname2;
	
	@FindBy(how = How.XPATH,using = "//input[@id='tjfields_optionvalue_1']")
	public static WebElement countryvalue2;
	
	@FindBy(how = How.XPATH,using = "//input[@id='tjfields_optionname_2']")
	public static WebElement countryname3;
	
	@FindBy(how = How.XPATH,using = "//input[@id='tjfields_optionvalue_2']")
	public static WebElement countryvalue3;	
	
	@FindBy(how = How.XPATH,using = "//input[@id='tjfields_optionname_3']")
	public static WebElement countryname4;
	
	@FindBy(how = How.XPATH,using = "//input[@id='tjfields_optionvalue_3']")
	public static WebElement countryvalue4;	
	
	@FindBy(how = How.XPATH,using = "//input[@id='jform_name']")
	public static WebElement languagevalue;	
	
	@FindBy(how = How.XPATH,using = "//ul/li[text()=\"Multi Select  (Deprecated. Use Field Type 'List')\"]")
	public static WebElement select_multiselect;	
	
	@FindBy(how = How.XPATH,using = "//input[@id='tjfields_optionname_0']")
	public static WebElement languageName1;	
	
	@FindBy(how = How.XPATH,using = "//input[@id='tjfields_optionvalue_0']")
	public static WebElement languageValue1 ;	
	
	@FindBy(how = How.XPATH,using = "//input[@id='tjfields_optionname_1']")
	public static WebElement languageName2;	
	
	@FindBy(how = How.XPATH,using = "//input[@id='tjfields_optionvalue_1']")
	public static WebElement languageValue2;	
	
	@FindBy(how = How.XPATH,using = "//input[@id='tjfields_optionname_2']")
	public static WebElement languageName3;	
	
	@FindBy(how = How.XPATH,using = "//input[@id='tjfields_optionvalue_2']")
	public static WebElement languageValue3;	
	
	@FindBy(how = How.XPATH,using = "//input[@id='jform_name']")
	public static WebElement textareaname;
	
	@FindBy(how = How.XPATH,using = "//ul/li[text()='Textarea']")
	public static WebElement selectTextarea;
	
	@FindBy(how = How.XPATH,using = "//input[@id='jform_params_rows']")
	public static WebElement row20;
	
	@FindBy(how = How.XPATH,using = "//input[@id='jform_params_cols']")
	public static WebElement coloum20;

	@FindBy(how = How.XPATH,using = "//input[@id='jform_name']")
	public static WebElement editorname;

	@FindBy(how = How.XPATH,using = "//ul/li[text()='Editor']")
	public static WebElement selectseditor;
	
	@FindBy(how = How.XPATH,using = "//input[@id='jform_name']")
	public static WebElement fileName;
	
	@FindBy(how = How.XPATH,using = "//ul/li[text()='File']")
	public static WebElement selectFile;
	
	@FindBy(how = How.XPATH,using = "//input[@id='jform_params_accept']")
	public static WebElement file_accpted;
	
	@FindBy(how = How.XPATH,using = "//input[@id='jform_name']")
	public static WebElement cctextname;

	@FindBy(how = How.XPATH,using = "//ul/li[text()='Textarea - Character Counter']")
	public static WebElement selecttextareacc;
	
	@FindBy(how = How.XPATH,using = "//input[@id='jform_params_maxlength']")
	public static WebElement max_len;
	
	@FindBy(how = How.XPATH,using = "//input[@id='jform_params_minlength']")
	public static WebElement min_len;
	
	@FindBy(how = How.XPATH,using = "//textarea[@id='jform_params_hint']")
	public static WebElement hint;
	
	@FindBy(how = How.XPATH,using = "//input[@id='jform_name']")
	public static WebElement subformName;
	
	@FindBy(how = How.XPATH,using = "//ul/li[text()='UCM Subform']")
	public static WebElement select_ucm_subform;
	
	@FindBy(how = How.XPATH,using = "//div[@id='jformparamsformsource_chzn']//a[@class='chzn-single']")
	public static WebElement singlesubForm;
	
	@FindBy(how = How.XPATH,using = "//ul/li[text()='Sub form']")
	public static WebElement selectsubform;

	@FindBy(how = How.XPATH,using = "//input[@id='jform_name']")
	public static WebElement sqlname;
	
	@FindBy(how = How.XPATH,using = "//ul/li[text()='SQL']")
	public static WebElement selectSql;
	
	@FindBy(how = How.XPATH,using = "//input[@id='jform_params_query']")
	public static WebElement sendSQL;
	
	@FindBy(how = How.XPATH,using = "//input[@id='jform_name']")
	public static WebElement checkboxname;
	
	@FindBy(how = How.XPATH,using = "//ul/li[text()='Checkbox']")
	public static WebElement selectcheckbox;
	
	//menu creation
	@FindBy(how = How.XPATH, using = "//div[@class='nav-collapse collapse']//a[text()='Menus ']")
	public static WebElement click_menu; 
	 
	@FindBy(how = How.XPATH, using = "//a[@class='no-dropdown menu-allmenu'][text()='All Menu Items']")
	public static WebElement click_allmenu; 
	 
	@FindBy(how = How.XPATH, using = "//button[@class='btn btn-small button-new btn-success']")
	public static WebElement click_newmenu; 
	 
	@FindBy(how = How.XPATH, using = "//input[@id='jform_title']" )
	public static WebElement menu_name;
	
	@FindBy(how = How.XPATH, using = "//div[@id='jform_menutype_chzn']" )
	public static WebElement menu_type;
	
	@FindBy(how = How.XPATH, using = "//ul[@class='chzn-results']/li[text()='Main Menu']" )
	public static WebElement select_mainmenu;

	@FindBy(how = How.XPATH, using = "//input[@id='jform_title']" )
	public static WebElement menu_access;

	@FindBy(how = How.XPATH, using = "//li[@class='active-result'][text()='Registered']")
	public static WebElement select_register;
	
	@FindBy(how = How.XPATH, using = "//button[@class='btn btn-primary']")
	public static WebElement select_button_primary;
		
	@FindBy(how = How.XPATH, using = "//id[@class='btn btn-primary']")
	public static WebElement select_primary;
	
	@FindBy(how = How.XPATH, using = "//*[@id=\"collapseTypes\"]/div[11]/div[1]/strong/a")
	public static WebElement select_header;
		
	@FindBy(how = How.XPATH, using = "//a[@title='Show a form to add or edit a Item']")
	public static WebElement show_edit_text;
	
	@FindBy(how = How.XPATH, using = "//a[text()='UCM Config']")
	public static WebElement ucm_config;
	
	@FindBy(how = How.XPATH, using = "//div[@id='jform_params_ucm_type_chzn']")
	public static WebElement select_ucmtype;
	
	@FindBy(how = How.XPATH, using = "//ul[@class='chzn-results']/li[text()='POM form ']")
	public static WebElement select_typeTitle;
	
	@FindBy(how = How.XPATH, using = "//a[text()='Details']")
	public static WebElement menu_details;
	
	@FindBy(how = How.XPATH, using = "//input[@id='jform_title']")
	public static WebElement create_viewform;

	@FindBy(how = How.XPATH, using = "//div[@id='jform_access_chzn']")
	public static WebElement create_viewformaccess;

	@FindBy(how = How.XPATH, using = "//li[@class='active-result'][text()='Registered']")
	public static WebElement create_activeresult;

	@FindBy(how = How.XPATH, using = "//a[@title='Show a list of Items']")
	public static WebElement create_showlist;

	// create user
	@FindBy(how = How.XPATH, using = "//div[@class='nav-collapse collapse']//a[text()='Users ']")
	public static WebElement select_menuUser;

	@FindBy(how = How.XPATH, using = "//a[@class='dropdown-toggle menu-user'][text()='Manage']")
	public static WebElement select_menuManager;

	@FindBy(how = How.XPATH, using = "//input[@id='jform_username']")
	public static WebElement insert_username;

	@FindBy(how = How.XPATH, using = "//input[@id='jform_password']")
	public static WebElement pwd1;

	@FindBy(how = How.XPATH, using = "//input[@id='jform_password2']")
	public static WebElement pwd2;
	
	@FindBy(how = How.XPATH, using = "//button [@class='btn btn-small button-save']")
	public static WebElement create_user;
	
	@FindBy(how = How.XPATH, using = "//input[@id='jform_email']")
	public static WebElement user_email;
	
	@FindBy(how = How.XPATH, using = "//fieldset[@id='jform_is_subform']//label[@for='jform_is_subform1']")
	public static WebElement is_sub_form;
	
	@FindBy(how = How.XPATH, using = "//input[@id='jform_allowed_count']")
	public static WebElement allow_count;
	
	@FindBy(how = How.XPATH, using = "//*[@id=\"toolbar-apply\"]/button")
	public static WebElement save;
	
	public adminObject(WebDriver driver) {

		this.driver = driver;
	}
	
}
