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
 * This is Page Class for date field creation . It contains all the elements and actions
 * related to date creation view.
 * 
 */

public class AdminDateFieldPage extends BaseClass {

	private WebDriver driver;
	static Logger log = Logger.getLogger(AdminDateFieldPage.class);

	public AdminDateFieldPage(WebDriver driver) {
		this.driver = driver;
		PageFactory.initElements(driver, this);
		
	}
	/*
	 * Locators for date field creation  
	 */
	@FindBy(how = How.XPATH, using = "//div[@class='chzn-container chzn-container-single']//a[@class='chzn-single']")
	public WebElement click_field_type;
	@FindBy(how = How.XPATH,using = "//ul/li[text()='Calendar']")
	public WebElement select_clender;
	@FindBy(how = How.XPATH, using ="//button[@class='btn btn-small button-apply btn-success']")
	public WebElement saveForValidation;
	@FindBy(how = How.XPATH, using = "//input[@id='jform_label']")
	public WebElement dlable;
	@FindBy(how = How.XPATH,using = "//input[@id='jform_name']")
	public WebElement datename; 
	@FindBy(how = How.XPATH, using = "//fieldset[@id='jform_required']//label[@class='btn']")
	public WebElement text_required;
	@FindBy(how =How.XPATH, using = "//button[@class='btn btn-small button-save-new']")
	public WebElement text_save;
	
	
	/*
	 * 
	 * Method for date field creation
	 * 
	 */
	
	public AdminDateFieldPage dateFieldCreation(String dl, String dn) {
		click_field_type.click();
		select_clender.click();
		saveForValidation.click();
		logger.pass("check the validation");
		Alert altpopup = driver.switchTo().alert();
		altpopup.accept();
		logger.pass("check validation");
		enterValue(dlable,dl);
		logger.pass("enter lable of date the field");
		enterValue(datename, dn);
		logger.pass("enter the name for date field");
		click_field_type.click();
		logger.pass("click at date field");
		select_clender.click();
		logger.pass("select calender date field");
		text_required.click();
		logger.pass("select field as required");
		text_save.click();
		logger.pass("number field created");
		return new AdminDateFieldPage(driver);
			
	}
}
