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
 * This is Page Class for checkbox field creation . It contains all the elements and actions
 * related to checkbox field creation view.
 * 
 */

public class AdmintCheckboxFieldPage extends BaseClass {

	private WebDriver driver;
	static Logger log = Logger.getLogger(AdmintCheckboxFieldPage.class);

	public AdmintCheckboxFieldPage(WebDriver driver) {
		System.out.print("in textfield page");
		this.driver = driver;
		PageFactory.initElements(driver, this);
		
	}
	/*
	 * Locators for checkbox field creation  
	 */
	
	@FindBy(how = How.XPATH, using = "//div[@class='chzn-container chzn-container-single']//a[@class='chzn-single']")
	public WebElement click_field_type;
	@FindBy(how = How.XPATH,using = "//ul/li[text()='Textarea - Character Counter']")
	public WebElement selecttConter;
	@FindBy(how = How.XPATH, using ="//button[@class='btn btn-small button-apply btn-success']")
	public WebElement saveForValidation;
	@FindBy(how = How.XPATH, using = "//input[@id='jform_label']")
	public WebElement bblable;
	@FindBy(how = How.XPATH,using = "//input[@id='jform_name']")
	public WebElement checkboxname;
	@FindBy(how = How.XPATH,using = "//ul/li[text()='Checkbox']")
	public WebElement selectcheckbox;
	@FindBy(how = How.XPATH, using = "//fieldset[@id='jform_required']//label[@class='btn']")
	public WebElement text_required;
	@FindBy(how =How.XPATH, using = "//button[@class='btn btn-small button-save-new']")
	public WebElement text_save;
	
	/*
	 * 
	 * Method for checkbox field creation
	 * 
	 */
	
	public AdmintCheckboxFieldPage checkboxFieldCreation(String bbl, String cb) {
		click_field_type.click();
		selecttConter.click();
		saveForValidation.click();
		Alert altpopup = driver.switchTo().alert();
		altpopup.accept();
		logger.pass("check validation");
		enterValue(bblable,bbl);
		logger.pass("enter lable of checkbox the field");
		enterValue(checkboxname, cb);
		logger.pass("enter the name for checkbox field");
		click_field_type.click();
		logger.pass("click at checkbox field");
		selectcheckbox.click();
		logger.pass("select checkbox field");
		text_required.click();
		logger.pass("select field as required");
		text_save.click();
		logger.pass("Checkbox field created");
		return new AdmintCheckboxFieldPage(driver);
			
	}
}
