package com.ucm.pageobjects;

import org.apache.log4j.Logger;
import org.openqa.selenium.Alert;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import org.openqa.selenium.support.How;
import org.openqa.selenium.support.PageFactory;
import com.mongodb.operation.DropDatabaseOperation;
import com.ucm.config.BaseClass;

/**
 * This is Page Class for menu creation . It contains all the elements and actions
 * related to menu creation view.
 * 
 */

public class AdminViewMenuPage extends BaseClass {

	private WebDriver driver;
	static Logger log = Logger.getLogger(AdminViewMenuPage.class);

	public AdminViewMenuPage(WebDriver driver) {
		this.driver = driver;
		PageFactory.initElements(driver, this);
		
	}
	/*
	 * Locators for menu creation  
	 */
	
	@FindBy(how = How.XPATH, using = "//div[@class='nav-collapse collapse']//a[text()='Menus ']")
	public WebElement click_menu; 
	@FindBy(how = How.XPATH, using = "//a[@class='no-dropdown menu-allmenu'][text()='All Menu Items']")
	public WebElement click_allmenu; 
	@FindBy(how = How.XPATH, using = "//button[@class='btn btn-small button-new btn-success']")
	public WebElement click_newmenu; 
	@FindBy(how = How.XPATH, using = "//input[@id='jform_title']" )
	public WebElement menu_name;
	@FindBy(how = How.XPATH, using = "//input[@id='jform_title']" )
	public WebElement menu_name1;
	@FindBy(how = How.XPATH, using = "//div[@id='jform_menutype_chzn']" )
	public WebElement menu_type;
	@FindBy(how = How.XPATH, using = "//ul[@class='chzn-results']/li[text()='Main Menu']" )
	public WebElement select_mainmenu;
	@FindBy(how = How.XPATH, using = "//input[@id='jform_title']" )
	public WebElement menu_access;
	@FindBy(how = How.XPATH, using = "//li[@class='active-result'][text()='Registered']")
	public WebElement select_register;
	@FindBy(how = How.XPATH, using = "//button[@class='btn btn-primary']")
	public WebElement select_button_primary;
	@FindBy(how = How.XPATH, using = "//id[@class='btn btn-primary']")
	public WebElement select_primary;
	@FindBy(how = How.XPATH, using = "//*[@id=\"collapseTypes\"]/div[10]/div[1]/strong/a")
	public WebElement select_header;
	@FindBy(how = How.XPATH, using = "//a[@title='Show a form to add or edit a Item']")
	public WebElement show_edit_text;
	@FindBy(how = How.XPATH, using = "//a[text()='UCM Config']")
	public WebElement ucm_config;
	@FindBy(how = How.XPATH, using = "//div[@id='jform_params_ucm_type_chzn']")
	public WebElement select_ucmtype;
	@FindBy(how = How.XPATH, using = "//ul[@class='chzn-results']/li[text()='POM form']")
	public WebElement select_typeTitle;
	@FindBy(how = How.XPATH, using = "//a[text()='Details']")
	public WebElement menu_details;
	@FindBy(how = How.XPATH, using = "//input[@id='jform_title']")
	public WebElement create_viewform;
	@FindBy(how = How.XPATH, using = "//div[@id='jform_access_chzn']")
	public WebElement create_viewformaccess;
	@FindBy(how = How.XPATH, using = "//li[@class='active-result'][text()='Registered']")
	public WebElement create_activeresult;
	@FindBy(how = How.XPATH, using = "//a[@title='Show a list of Items']")
	public WebElement create_showlist;
	@FindBy(how =How.XPATH, using = "//button[@class='btn btn-small button-save']")
	public WebElement saveandclose;


	
	/*
	 * 
	 * Method for menu field creation
	 * 
	 */
	
	public AdminViewMenuPage ViewMenuCreation(String mn,String mn1) {
		click_menu.click();
		click_allmenu.click();
		click_newmenu.click();
		enterValue(menu_name,mn);
		menu_type.click();
	    select_mainmenu.click();
		create_viewformaccess.click();
		select_register.click();
		select_button_primary.click();
		driver.switchTo().frame("Menu Item Type"); // switch to iFrame
		select_header.click();
		show_edit_text.click();	
		driver.switchTo().defaultContent();
		ucm_config.click();
		select_ucmtype.click();
		logger.pass("select ucm type for form view");
		select_typeTitle.click();
		saveandclose.click();
		click_newmenu.click();
		logger.pass("Menu for view is created");
		enterValue(menu_name1,mn1);
		menu_type.click();
	    select_mainmenu.click();
		create_viewformaccess.click();
		select_register.click();
		select_button_primary.click();
		driver.switchTo().frame("Menu Item Type"); // switch to iFrame
		select_header.click();
		show_edit_text.click();	
		driver.switchTo().defaultContent();
		ucm_config.click();
		logger.pass("select ucmtype for list view");
		select_ucmtype.click();
		select_typeTitle.click();
		saveandclose.click();
		logger.pass("Created View Menu");		
		return new AdminViewMenuPage(driver);
			
	}
}
