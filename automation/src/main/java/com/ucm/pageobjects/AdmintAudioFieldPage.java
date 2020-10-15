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
 * This is Page Class for Audio field creation . It contains all the elements and actions
 * related to Audio field creation view.
 * 
 */

public class AdmintAudioFieldPage extends BaseClass {

	private WebDriver driver;
	static Logger log = Logger.getLogger(AdmintAudioFieldPage.class);

	public AdmintAudioFieldPage(WebDriver driver) {
		this.driver = driver;
		PageFactory.initElements(driver, this);
		
	}
	/*
	 * Locators for Audio field creation  
	 */
	
	@FindBy(how = How.XPATH, using = "//div[@class='chzn-container chzn-container-single']//a[@class='chzn-single']")
	public WebElement click_field_type;
	@FindBy(how = How.XPATH, using = "//input[@id='jform_label']")
	public WebElement alable;
	@FindBy(how = How.XPATH,using = "//input[@id='jform_name']")
	public WebElement aname;
	@FindBy(how = How.XPATH, using = "//ul/li[text()='Audio']")
	public WebElement selectAudio;
	@FindBy(how = How.XPATH, using = "//fieldset[@id='jform_required']//label[@class='btn']")
	public WebElement text_required;
	@FindBy(how =How.XPATH, using = "//button[@class='btn btn-small button-save-new']")
	public WebElement text_save;
	@FindBy(how = How.XPATH,using = "//input[@id='jform_params_query']")
	public WebElement sendSQL;
	
	/*
	 * 
	 * Method for Audio field creation
	 * 
	 */
	
	public AdmintAudioFieldPage audioFieldCreation(String al, String an) {
		enterValue(alable,al);
		logger.pass("enter lable of Audio the field");
		enterValue(aname, an);
		logger.pass("enter the name for Audio field");
		click_field_type.click();
		logger.pass("click at Audio field");
		selectAudio.click();
		logger.pass("select Audio field");
		text_required.click();
		logger.pass("select field as required");
		text_save.click();
		logger.pass("Audio field created");
		return new AdmintAudioFieldPage(driver);
			
	}
}
