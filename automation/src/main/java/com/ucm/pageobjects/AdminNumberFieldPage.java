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
 * This is Page Class for number field creation . It contains all the elements and actions
 * related to text number creation view.
 * 
 */

public class AdminNumberFieldPage extends BaseClass {

	private WebDriver driver;
	static Logger log = Logger.getLogger(AdminNumberFieldPage.class);

	public AdminNumberFieldPage(WebDriver driver) {
		this.driver = driver;
		PageFactory.initElements(driver, this);
		
	}
	/*
	 * Locators for number field creation  
	 */

	@FindBy(how = How.XPATH, using = "//div[@class='chzn-container chzn-container-single']//a[@class='chzn-single']")
	public WebElement click_field_type;
	@FindBy(how = How.XPATH, using = "//ul/li[text()='Number']")
	public WebElement select_number;
	@FindBy(how = How.XPATH, using ="//button[@class='btn btn-small button-apply btn-success']")
	public WebElement saveForValidation;
	@FindBy(how = How.XPATH, using = "//input[@id='jform_label']")
	public WebElement nlable;
	@FindBy(how = How.XPATH, using = "//input[@id='jform_name']")
	public WebElement numbername; 
	@FindBy(how = How.XPATH, using = "//input [@id='jform_params_min']")
	public WebElement minNumber;
	@FindBy(how = How.XPATH, using = "//fieldset[@id='jform_required']//label[@class='btn']")
	public static WebElement text_required;
	@FindBy(how =How.XPATH, using = "//button[@class='btn btn-small button-save-new']")
	public WebElement text_save;
	
	
	/*
	 * 
	 * Method for number field creation
	 * 
	 */
	
	public AdminNumberFieldPage numberFieldCreation(String nl, String nn, String mn) {
		click_field_type.click();
		select_number.click();
		saveForValidation.click();
		logger.pass("check the validation");
		Alert altpopup = driver.switchTo().alert();
		altpopup.accept();
		logger.pass("check validation");
		enterValue(nlable,nl);
		logger.pass("enter lable of the field");
		enterValue(numbername, nn);
		logger.pass("enter the name for number field");
		click_field_type.click();
		select_number.click();
		logger.pass("select number field");
		enterValue(minNumber, mn);
		logger.pass("Enter the min length");
		text_required.click();
		logger.pass("select field as required");
		text_save.click();
		logger.pass("number field created");
		return new AdminNumberFieldPage(driver);
		
		
	}
}
