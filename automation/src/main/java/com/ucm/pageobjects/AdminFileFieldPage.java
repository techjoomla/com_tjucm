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
 * This is Page Class for file field creation . It contains all the elements and actions
 * related to file field creation view.
 * 
 */

public class AdminFileFieldPage extends BaseClass {

	private WebDriver driver;
	static Logger log = Logger.getLogger(AdminFileFieldPage.class);

	public AdminFileFieldPage(WebDriver driver) {
		System.out.print("in textfield page");
		this.driver = driver;
		PageFactory.initElements(driver, this);
		
	}
	/*
	 * Locators for file field creation  
	 */
	
	@FindBy(how = How.XPATH, using = "//div[@class='chzn-container chzn-container-single']//a[@class='chzn-single']")
	public WebElement click_field_type;
	@FindBy(how = How.XPATH,using = "//ul/li[text()='File']")
	public WebElement selectFile;
	@FindBy(how = How.XPATH, using ="//button[@class='btn btn-small button-apply btn-success']")
	public WebElement saveForValidation;
	@FindBy(how = How.XPATH, using = "//input[@id='jform_label']")
	public WebElement filelable;
	@FindBy(how = How.XPATH,using = "//input[@id='jform_name']")
	public WebElement fileName;
	@FindBy(how = How.XPATH, using = "//fieldset[@id='jform_required']//label[@class='btn']")
	public WebElement text_required;
	@FindBy(how =How.XPATH, using = "//button[@class='btn btn-small button-save-new']")
	public WebElement text_save;
	
	/*
	 * 
	 * Method for file field creation
	 * 
	 */
	
	public AdminFileFieldPage fileFieldCreation(String fl, String fn) {
		click_field_type.click();
		selectFile.click();
		saveForValidation.click();
		logger.pass("check the validation");
		Alert altpopup = driver.switchTo().alert();
		altpopup.accept();
		logger.pass("check validation");
		enterValue(filelable,fl);
		logger.pass("enter lable of file the field");
		enterValue(fileName, fn);
		logger.pass("enter the name for file field");
		click_field_type.click();
		logger.pass("click at file field");
		selectFile.click();
		logger.pass("select file field");
		text_required.click();
		logger.pass("select field as required");
		text_save.click();
		logger.pass("file field created");
		return new AdminFileFieldPage(driver);
			
	}
}
