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
 * This is Page Class for SQL field creation . It contains all the elements and actions
 * related to SQL field creation view.
 * 
 */

public class AdmintSQLFieldPage extends BaseClass {

	private WebDriver driver;
	static Logger log = Logger.getLogger(AdmintSQLFieldPage.class);

	public AdmintSQLFieldPage(WebDriver driver) {
		this.driver = driver;
		PageFactory.initElements(driver, this);
		
	}
	/*
	 * Locators for SQL field creation  
	 */
	
	@FindBy(how = How.XPATH, using = "//div[@class='chzn-container chzn-container-single']//a[@class='chzn-single']")
	public WebElement click_field_type;
	@FindBy(how = How.XPATH, using = "//input[@id='jform_label']")
	public WebElement slable;
	@FindBy(how = How.XPATH,using = "//input[@id='jform_name']")
	public WebElement sqlname;
	@FindBy(how = How.XPATH,using = "//ul/li[text()='SQL']")
	public WebElement selectSql;
	@FindBy(how = How.XPATH, using = "//fieldset[@id='jform_required']//label[@class='btn']")
	public WebElement text_required;
	@FindBy(how =How.XPATH, using = "//button[@class='btn btn-small button-save-new']")
	public WebElement text_save;
	@FindBy(how = How.XPATH,using = "//input[@id='jform_params_query']")
	public WebElement sendSQL;
	
	/*
	 * 
	 * Method for SQL field creation
	 * 
	 */
	
	public AdmintSQLFieldPage sqlFieldCreation(String sl, String sn, String ss) {
		enterValue(slable,sl);
		logger.pass("enter lable of SQL the field");
		enterValue(sqlname, sn);
		logger.pass("enter the name for SQL field");
		click_field_type.click();
		logger.pass("click at SQL field");
		selectSql.click();
		logger.pass("select SQL field");
		enterValue(sendSQL, ss);
		logger.pass("enter sql query");
		text_required.click();
		logger.pass("select field as required");
		text_save.click();
		logger.pass("SQL field created");
		return new AdmintSQLFieldPage(driver);
			
	}
}
