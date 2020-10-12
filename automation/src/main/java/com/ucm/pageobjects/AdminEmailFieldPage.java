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
 * This is Page Class for email field creation . It contains all the elements and actions
 * related to email field creation view.
 * 
 */

public class AdminEmailFieldPage extends BaseClass {

	private WebDriver driver;
	static Logger log = Logger.getLogger(AdminEmailFieldPage.class);

	public AdminEmailFieldPage(WebDriver driver) {
		System.out.print("in textfield page");
		this.driver = driver;
		PageFactory.initElements(driver, this);
		
	}
	/*
	 * Locators for email field creation  
	 */
	
	@FindBy(how = How.XPATH, using = "//div[@class='chzn-container chzn-container-single']//a[@class='chzn-single']")
	public WebElement click_field_type;
	@FindBy(how = How.XPATH,using = "//ul/li[text()='Email']")
	public WebElement select_email;
	@FindBy(how = How.XPATH, using ="//button[@class='btn btn-small button-apply btn-success']")
	public WebElement saveForValidation;
	@FindBy(how = How.XPATH, using = "//input[@id='jform_label']")
	public WebElement elable;
	@FindBy(how = How.XPATH,using = "//input[@id='jform_name']")
	public WebElement emailname;
	@FindBy(how = How.XPATH, using = "//fieldset[@id='jform_required']//label[@class='btn']")
	public WebElement text_required;
	@FindBy(how =How.XPATH, using = "//button[@class='btn btn-small button-save-new']")
	public WebElement text_save;
	
	/*
	 * 
	 * Method for email field creation
	 * 
	 */
	
	public AdminEmailFieldPage emailFieldCreation(String el, String en) {
		click_field_type.click();
		select_email.click();
		saveForValidation.click();
		logger.pass("check the validation");
		Alert altpopup = driver.switchTo().alert();
		altpopup.accept();
		logger.pass("check validation");
		enterValue(elable,el);
		logger.pass("enter lable of date the field");
		enterValue(emailname, en);
		logger.pass("enter the name for date field");
		click_field_type.click();
		logger.pass("click at email field");
		select_email.click();
		logger.pass("select email field");
		text_required.click();
		logger.pass("select field as required");
		text_save.click();
		logger.pass("email field created");
		return new AdminEmailFieldPage(driver);
			
	}
}
