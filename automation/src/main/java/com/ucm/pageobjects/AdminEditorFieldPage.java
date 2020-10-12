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
 * This is Page Class for editor field creation . It contains all the elements and actions
 * related to editor field creation view.
 * 
 */

public class AdminEditorFieldPage extends BaseClass {

	private WebDriver driver;
	static Logger log = Logger.getLogger(AdminEditorFieldPage.class);

	public AdminEditorFieldPage(WebDriver driver) {
		System.out.print("in textfield page");
		this.driver = driver;
		PageFactory.initElements(driver, this);
		
	}
	/*
	 * Locators for editor field creation  
	 */
	
	@FindBy(how = How.XPATH, using = "//div[@class='chzn-container chzn-container-single']//a[@class='chzn-single']")
	public WebElement click_field_type;
	@FindBy(how = How.XPATH,using = "//ul/li[text()='Editor']")
	public WebElement selectseditor;
	@FindBy(how = How.XPATH, using ="//button[@class='btn btn-small button-apply btn-success']")
	public WebElement saveForValidation;
	@FindBy(how = How.XPATH, using = "//input[@id='jform_label']")
	public WebElement edlable;
	@FindBy(how = How.XPATH,using = "//input[@id='jform_name']")
	public WebElement editorname;
	@FindBy(how = How.XPATH, using = "//fieldset[@id='jform_required']//label[@class='btn']")
	public WebElement text_required;
	@FindBy(how =How.XPATH, using = "//button[@class='btn btn-small button-save-new']")
	public WebElement text_save;
	
	/*
	 * 
	 * Method for editor field creation
	 * 
	 */
	
	public AdminEditorFieldPage editorFieldCreation(String edl, String en) {
		click_field_type.click();
		selectseditor.click();
		saveForValidation.click();
		logger.pass("check the validation");
		Alert altpopup = driver.switchTo().alert();
		altpopup.accept();
		logger.pass("check validation");
		enterValue(edlable,edl);
		logger.pass("enter lable of editor the field");
		enterValue(editorname, en);
		logger.pass("enter the name for editor field");
		click_field_type.click();
		logger.pass("click at editor field");
		selectseditor.click();
		logger.pass("select editor field");
		text_required.click();
		logger.pass("select field as required");
		text_save.click();
		logger.pass("editor field created");
		return new AdminEditorFieldPage(driver);
			
	}
}
