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
 * This is Page Class for Video field creation . It contains all the elements and actions
 * related to Video field creation view.
 * 
 */

public class AdmintVideoFieldPage extends BaseClass {

	private WebDriver driver;
	static Logger log = Logger.getLogger(AdmintVideoFieldPage.class);

	public AdmintVideoFieldPage(WebDriver driver) {
		this.driver = driver;
		PageFactory.initElements(driver, this);
		
	}
	/*
	 * Locators for Video field creation  
	 */
	
	@FindBy(how = How.XPATH, using = "//div[@class='chzn-container chzn-container-single']//a[@class='chzn-single']")
	public WebElement click_field_type;
	@FindBy(how = How.XPATH, using = "//input[@id='jform_label']")
	public WebElement vlable;
	@FindBy(how = How.XPATH,using = "//input[@id='jform_name']")
	public WebElement vname;
	@FindBy(how = How.XPATH, using = "//ul/li[text()='Video']")
	public WebElement selectVideo;
	@FindBy(how = How.XPATH, using = "//fieldset[@id='jform_required']//label[@class='btn']")
	public WebElement text_required;
	@FindBy(how =How.XPATH, using = "//button[@class='btn btn-small button-save-new']")
	public WebElement text_save;
	@FindBy(how = How.XPATH,using = "//input[@id='jform_params_query']")
	public WebElement sendSQL;
	
	/*
	 * 
	 * Method for Video field creation
	 * 
	 */
	
	public AdmintVideoFieldPage videoFieldCreation(String vl, String vn) {
		enterValue(vlable,vl);
		logger.pass("enter lable of video the field");
		enterValue(vname, vn);
		logger.pass("enter the name for video field");
		click_field_type.click();
		logger.pass("click at video field");
		selectVideo.click();
		logger.pass("select video field");
		text_required.click();
		logger.pass("select field as required");
		text_save.click();
		logger.pass("video field created");
		return new AdmintVideoFieldPage(driver);
			
	}
}
