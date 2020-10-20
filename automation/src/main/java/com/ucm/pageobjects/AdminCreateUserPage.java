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
 * This is Page Class for CreateUser . It contains all the elements and actions
 * related to CreateUser view.
 * 
 */

public class AdminCreateUserPage extends BaseClass {

	private WebDriver driver;
	static Logger log = Logger.getLogger(AdminCreateUserPage.class);

	public AdminCreateUserPage(WebDriver driver) {
		this.driver = driver;
		PageFactory.initElements(driver, this);
		
	}
	/*
	 * Locators for CreateUser creation  
	 */
	
	@FindBy(how = How.XPATH, using = "//div[@class='nav-collapse collapse']//a[text()='Users ']")
	public WebElement select_menuUser;
	@FindBy(how = How.XPATH, using = "//a[@class='dropdown-toggle menu-user'][text()='Manage']")
	public WebElement select_menuManager;
	@FindBy(how = How.XPATH, using = "//input[@id='jform_username']")
	public WebElement insert_username;
	@FindBy(how = How.XPATH, using = "//input[@id='jform_password']")
	public WebElement pwd1;
	@FindBy(how = How.XPATH, using = "//input[@id='jform_password2']")
	public WebElement pwd2;
	@FindBy(how = How.XPATH, using = "//button [@class='btn btn-small button-save']")
	public WebElement create_user;
	@FindBy(how = How.XPATH, using = "//input[@id='jform_email']")
	public WebElement user_email;
	@FindBy(how = How.XPATH, using = "//*[@id=\"jform_params_is_subform\"]/label[2]")
	public WebElement is_sub_form;
	@FindBy(how = How.XPATH, using = "//input[@id='jform_params_allowed_count']")
	public WebElement allow_count;
	@FindBy(how = How.XPATH, using = "//*[@id=\"toolbar-apply\"]/button")
	public WebElement save;
	@FindBy(how = How.XPATH, using ="//button[@class='btn btn-small button-apply btn-success']")
	public WebElement saveForValidation;
	@FindBy(how = How.XPATH, using ="//button[@class='//input[@id='jform_params_size']")
	public WebElement allowmax;
	@FindBy(how = How.XPATH, using = "//button[@class='btn btn-small button-new btn-success']")
	public WebElement save_field_group;
	@FindBy(how = How.XPATH, using = "//input[@id='jform_name']")
	public WebElement username; 
	
	/*
	 * 
	 * Method for CreateUser creation
	 * 
	 */
	
	public AdminCreateUserPage ViewCreateUser(String un,String iu, String ue, String pone, String ptwo) {
		select_menuUser.click();
		select_menuManager.click();
		save_field_group.click();
		enterValue(username, un);
		enterValue(insert_username, iu);
		enterValue(user_email, ue);
		enterValue(pwd1, pone);
		enterValue(pwd2, ptwo);
		create_user.click();
		return new AdminCreateUserPage(driver);
			
	}
}
