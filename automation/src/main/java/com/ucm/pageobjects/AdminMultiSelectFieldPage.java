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
 * This is Page Class for multi select field creation . It contains all the elements and actions
 * related to multi select field creation view.
 * 
 */

public class AdminMultiSelectFieldPage extends BaseClass {

	private WebDriver driver;
	static Logger log = Logger.getLogger(AdminMultiSelectFieldPage.class);

	public AdminMultiSelectFieldPage(WebDriver driver) {
		System.out.print("in textfield page");
		this.driver = driver;
		PageFactory.initElements(driver, this);
		
	}
	/*
	 * Locators for multi select field creation  
	 */

	@FindBy(how = How.XPATH, using = "//div[@class='chzn-container chzn-container-single']//a[@class='chzn-single']")
	public WebElement click_field_type;
	@FindBy(how = How.XPATH, using = "//input[@id='jform_label']")
	public WebElement mslable;
	@FindBy(how = How.XPATH,using = "//input[@id='jform_name']")
	public WebElement langname; 
	@FindBy(how = How.XPATH,using = "//ul/li[text()=\"Multi Select  (Deprecated. Use Field Type 'List')\"]")
	public WebElement select_multiselect;
	@FindBy(how = How.XPATH,using = "//input[@name='jform[fieldoption][fieldoption0][name]']")
	public WebElement languageName1;	
	@FindBy(how = How.XPATH,using = "//input[@name='jform[fieldoption][fieldoption0][value]']")
	public WebElement languageValue1 ;	
	@FindBy(how = How.XPATH,using = "//input[@name='jform[fieldoption][fieldoption1][name]']")
	public WebElement languageName2;	
	@FindBy(how = How.XPATH,using = "//input[@name='jform[fieldoption][fieldoption1][value]']")
	public WebElement languageValue2;	
	@FindBy(how = How.XPATH,using = "//input[@name='jform[fieldoption][fieldoption2][name]']")
	public WebElement languageName3;	
	@FindBy(how = How.XPATH,using = "//input[@name='jform[fieldoption][fieldoption2][value]']")
	public WebElement languageValue3;
	@FindBy(how =How.XPATH, using = "//button[@class='btn btn-small button-save-new']")
	public static WebElement text_save;
	@FindBy(how = How.XPATH, using = "//span[@class='icon-plus']")
	public WebElement clickAtPlushIcon; 
	
	/*
	 * 
	 * Method for multi select field creation
	 * 
	 */
	
	public AdminMultiSelectFieldPage multiselectFieldCreation(String ssl, String ln, String ln1, String lv1, String ln2, String lv2, String ln3, String lv3 ) {
		enterValue(mslable,ssl);
		logger.pass("enter lable of the field");
		enterValue(langname, ln);
		logger.pass("enter the multi select field name");
		click_field_type.click();
		logger.pass("click at field type");
		select_multiselect.click();
		enterValue(languageName1, ln1);
		logger.pass("enter lang name 1");
		enterValue(languageValue1, lv1);
		logger.pass("enter lang value 1");
		clickAtPlushIcon.click();
		enterValue(languageName2, ln2);
		logger.pass("enter lang name 2");
		enterValue(languageValue2, lv2);
		logger.pass("enter lang value 2");
		clickAtPlushIcon.click();
		enterValue(languageName3, ln3);
		logger.pass("enter lang name 3");
		enterValue(languageValue3, lv3);
		logger.pass("enter lang value 3");
		text_save.click();	
		logger.pass("multi select field created");
		return new AdminMultiSelectFieldPage(driver);
		
		
	}
}
