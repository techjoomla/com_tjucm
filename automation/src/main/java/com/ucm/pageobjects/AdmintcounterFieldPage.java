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
 * This is Page Class for text counter field creation . It contains all the elements and actions
 * related to text counter field creation view.
 * 
 */

public class AdmintcounterFieldPage extends BaseClass {

	private WebDriver driver;
	static Logger log = Logger.getLogger(AdmintcounterFieldPage.class);

	public AdmintcounterFieldPage(WebDriver driver) {
		System.out.print("in textfield page");
		this.driver = driver;
		PageFactory.initElements(driver, this);
		
	}
	/*
	 * Locators for text counter field creation  
	 */
	
	@FindBy(how = How.XPATH, using = "//div[@class='chzn-container chzn-container-single']//a[@class='chzn-single']")
	public WebElement click_field_type;
	@FindBy(how = How.XPATH,using = "//ul/li[text()='Textarea - Character Counter']")
	public WebElement selecttConter;
	@FindBy(how = How.XPATH, using ="//button[@class='btn btn-small button-apply btn-success']")
	public WebElement saveForValidation;
	@FindBy(how = How.XPATH, using = "//input[@id='jform_label']")
	public WebElement tlable;
	@FindBy(how = How.XPATH,using = "//input[@id='jform_name']")
	public WebElement tName;
	@FindBy(how = How.XPATH, using = "//fieldset[@id='jform_required']//label[@class='btn']")
	public WebElement text_required;
	@FindBy(how = How.XPATH,using = "//input[@id='jform_params_rows']")
	public WebElement row20;
	@FindBy(how = How.XPATH,using = "//input[@id='jform_params_cols']")
	public WebElement coloum20;
	@FindBy(how = How.XPATH,using = "//input[@id='jform_params_maxlength']")
	public WebElement max_len;
	@FindBy(how = How.XPATH,using = "//input[@id='jform_params_minlength']")
	public WebElement min_len;
	@FindBy(how = How.XPATH,using = "//textarea[@id='jform_params_hint']")
	public WebElement hint;
	@FindBy(how =How.XPATH, using = "//button[@class='btn btn-small button-save-new']")
	public WebElement text_save;
	
	/*
	 * 
	 * Method for text counter field creation
	 * 
	 */
	
	public AdmintcounterFieldPage tcounterFieldCreation(String tl, String tn, String r, String c, String maxl, String minl, String h) {
		click_field_type.click();
		selecttConter.click();
		saveForValidation.click();
		logger.pass("check the validation");
		Alert altpopup = driver.switchTo().alert();
		altpopup.accept();
		logger.pass("check validation");
		enterValue(tlable,tl);
		logger.pass("enter lable of text counter the field");
		enterValue(tName, tn);
		logger.pass("enter the name for text counter field");
		click_field_type.click();
		logger.pass("click at text counter field");
		selecttConter.click();
		logger.pass("select text counter field");
		enterValue(row20,r);
		logger.pass("enter row");
		enterValue(coloum20, c);
		logger.pass("enter coloum");
		enterValue(max_len, maxl);
		logger.pass("enter max lenght");
		enterValue(min_len, minl);
		logger.pass("enter min lenght");
		enterValue(hint, h);
		logger.pass("placeholder");
		text_required.click();
		logger.pass("select field as required");
		text_save.click();
		logger.pass("text counter field created");
		return new AdmintcounterFieldPage(driver);
			
	}
}
