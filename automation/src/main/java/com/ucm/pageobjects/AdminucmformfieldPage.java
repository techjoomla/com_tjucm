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
 * This is Page Class for single select field creation . It contains all the elements and actions
 * related to single select field creation view.
 * 
 */

public class AdminucmformfieldPage extends BaseClass {

	private WebDriver driver;
	static Logger log = Logger.getLogger(AdminucmformfieldPage.class);

	public AdminucmformfieldPage(WebDriver driver) {
		this.driver = driver;
		PageFactory.initElements(driver, this);
		
	}
	/*
	 * Locators for single select field creation  
	 */
	@FindBy(how = How.XPATH, using = "//input[@id='jform_label']")
	public WebElement lable;
	@FindBy(how = How.XPATH,using = "//input[@id='jform_name']")
	public WebElement subformName;
	@FindBy(how = How.XPATH,using = "//ul/li[text()='UCM Subform']")
	public WebElement select_ucm_subform;
	@FindBy(how = How.XPATH,using = "//div[@id='jform_params_formsource_chzn']//a[@class='chzn-single']")
	public WebElement singlesubForm;
	@FindBy(how = How.XPATH,using = "//ul/li[text()='subform']")
	public WebElement selectsubform;
	@FindBy(how = How.XPATH, using = "//div[@class='chzn-container chzn-container-single']//a[@class='chzn-single']")
	public WebElement click_field_type;
	@FindBy(how = How.XPATH, using = "//fieldset[@id='jform_required']//label[@class='btn']")
	public WebElement text_required;
	@FindBy(how =How.XPATH, using = "//button[@class='btn btn-small button-save-new']")
	public WebElement text_save;
	
	
	
	/*
	 * 
	 * Method for single select field creation
	 * 
	 */
	
	public AdminucmformfieldPage ucmformFieldCreation(String l, String sfn) {
		
		enterValue(lable, l);
		enterValue(subformName, sfn);
		click_field_type.click();
		select_ucm_subform.click();
		singlesubForm.click();
		selectsubform.click();
		text_required.click();
		logger.pass("select field as required");
		text_save.click();	
		logger.pass("ucm filed created");	
		return new AdminucmformfieldPage(driver);
		}
}
