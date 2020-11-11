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
 * This is Page Class for category field creation . It contains all the elements and actions
 * related to category field creation view.
 * 
 */

public class AdminCategoryFieldPage extends BaseClass {

	private WebDriver driver;
	static Logger log = Logger.getLogger(AdminCategoryFieldPage.class);

	public AdminCategoryFieldPage(WebDriver driver) {
		this.driver = driver;
		PageFactory.initElements(driver, this);
		
	}
	/*
	 * Locators for category field creation  
	 */

	@FindBy(how = How.NAME, using = "jform[label]")
	public WebElement field_label;
	@FindBy(how = How.NAME, using = "jform[name]")
	public WebElement field_name;
	@FindBy(how = How.XPATH,using = "//input[@id='jform_params_maxlength']")
	public WebElement max_len;
	@FindBy(how = How.XPATH, using = "//div[@class='chzn-container chzn-container-single']//a[@class='chzn-single']")
	public WebElement click_field_type;
	@FindBy(how = How.XPATH, using = "//ul/li[text()='Item Category']")
	public WebElement select_field;
	@FindBy(how = How.XPATH, using = "//fieldset[@id='jform_required']//label[@class='btn']")
	public WebElement text_required;
	@FindBy(how =How.XPATH, using = "//button[@class='btn btn-small button-save-new']")
	public WebElement text_save_next;
	
	/*
	 * 
	 * Method for category creation
	 * 
	 */
	
	public AdminCategoryFieldPage catFieldCreation(String catl, String catn) {
		enterValue(field_label,catl);
		logger.pass("enter lable of the field");
		enterValue(field_name, catn);
		logger.pass("enter the text filed name");
		click_field_type.click();
		logger.pass("click at field type");
		select_field.click();
		text_required.click();
		logger.pass("save the field");
		text_save_next.click();	
		logger.pass("category field created");
		return new AdminCategoryFieldPage(driver);
		
		
	}
}
