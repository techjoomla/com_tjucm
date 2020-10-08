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
 * This is Page Class for textarea field creation . It contains all the elements and actions
 * related to textarea field creation view.
 * 
 */

public class AdminTextareaFieldPage extends BaseClass {

	private WebDriver driver;
	static Logger log = Logger.getLogger(AdminTextareaFieldPage.class);

	public AdminTextareaFieldPage(WebDriver driver) {
		System.out.print("in textfield page");
		this.driver = driver;
		PageFactory.initElements(driver, this);
		
	}
	/*
	 * Locators for textarea field creation  
	 */

	@FindBy(how = How.XPATH, using = "//div[@class='chzn-container chzn-container-single']//a[@class='chzn-single']")
	public WebElement click_field_type;
	@FindBy(how = How.XPATH,using = "//ul/li[text()='Textarea']")
	public WebElement selectTextarea;
	@FindBy(how = How.XPATH, using ="//button[@class='btn btn-small button-apply btn-success']")
	public WebElement saveForValidation;
	@FindBy(how = How.XPATH, using = "//input[@id='jform_label']")
	public WebElement talable;
	@FindBy(how = How.XPATH,using = "//input[@id='jform_name']")
	public WebElement langname; 
	@FindBy(how = How.XPATH,using = "//ul/li[text()=\"Multi Select  (Deprecated. Use Field Type 'List')\"]")
	public WebElement select_multiselect;
	@FindBy(how = How.XPATH,using = "//input[@id='jform_name']")
	public WebElement textareaname;
	@FindBy(how = How.XPATH,using = "//input[@id='jform_params_rows']")
	public WebElement row20;
	@FindBy(how = How.XPATH,using = "//input[@id='jform_params_cols']")
	public WebElement coloum20;
	@FindBy(how = How.XPATH, using = "//fieldset[@id='jform_required']//label[@class='btn']")
	public WebElement text_required;
	@FindBy(how =How.XPATH, using = "//button[@class='btn btn-small button-save-new']")
	public WebElement text_save;
	
	
	/*
	 * 
	 * Method for textarea field creation
	 * 
	 */
	
	public AdminTextareaFieldPage textareaFieldCreation(String tl, String tan, String row, String col) {
		click_field_type.click();
		selectTextarea.click();
		saveForValidation.click();
		Alert altpopup = driver.switchTo().alert();
		altpopup.accept();
		enterValue(talable, tl);
		logger.pass("enter lable of the field");
		enterValue(textareaname, tan);
		logger.pass("enter the textarea field name");
		click_field_type.click();
		logger.pass("click at field type");
		selectTextarea.click();
		enterValue(row20, row);
		logger.pass("enter row value");
		enterValue(coloum20, col);
		logger.pass("enter col value");
		text_required.click();
		text_save.click();	
		System.out.println("textarea field created");
		return new AdminTextareaFieldPage(driver);
		
		
	}
}
