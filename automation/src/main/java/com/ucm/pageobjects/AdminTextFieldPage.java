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
 * This is Page Class for sub form creation . It contains all the elements and actions
 * related to sub form creation view.
 * 
 */

public class AdminTextFieldPage extends BaseClass {

	private WebDriver driver;
	static Logger log = Logger.getLogger(AdminTextFieldPage.class);

	public AdminTextFieldPage(WebDriver driver) {
		System.out.print("in textfield page");
		this.driver = driver;
		PageFactory.initElements(driver, this);
		
	}
	/*
	 * Locators for radio button  
	 */

	@FindBy(how = How.XPATH, using="//button [@class='btn btn-small button-new btn-success']")
	public WebElement click_newbutton;
	@FindBy(how = How.XPATH, using ="//button[@class='btn btn-small button-apply btn-success']")
	public WebElement saveForValidation;
	@FindBy(how = How.NAME, using = "jform[label]")
	public WebElement field_label;
	@FindBy(how = How.NAME, using = "jform[name]")
	public WebElement field_name;
	@FindBy(how = How.XPATH,using = "//input[@id='jform_params_maxlength']")
	public WebElement max_len;
	@FindBy(how = How.XPATH, using = "//div[@class='chzn-container chzn-container-single']//a[@class='chzn-single']")
	public WebElement click_field_type;
	@FindBy(how = How.XPATH, using = "//ul/li[text()='Text']")
	public WebElement select_field;
	@FindBy(how = How.XPATH, using = "//fieldset[@id='jform_required']//label[@class='btn']")
	public WebElement text_required;
	@FindBy(how =How.XPATH, using = "//button[@class='btn btn-small button-save-new']")
	public WebElement text_save;
	
	/*
	 * 
	 * Method for textfield creation
	 * 
	 */
	
	public AdminTextFieldPage textFieldCreation(String l, String tn, String ml) {
		System.out.print("enter to create filed 1");
		saveForValidation.click();
		logger.pass("Click at field type");
		Alert altpopup = driver.switchTo().alert();
		altpopup.accept();
		enterValue(field_label,l);
		System.out.print("enter to create filed 2");
		logger.pass("enter lable of the field");
		enterValue(field_name, tn);
		logger.pass("enter the text filed name");
		enterValue(max_len, ml);
		logger.pass("Enter the max length");
		click_field_type.click();
		logger.pass("click at field type");
		select_field.click();
		text_required.click();
		text_save.click();	
		System.out.println("text field created");
		
		return new AdminTextFieldPage(driver);
		
		
	}
}
