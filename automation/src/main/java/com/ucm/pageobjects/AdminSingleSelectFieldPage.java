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

public class AdminSingleSelectFieldPage extends BaseClass {

	private WebDriver driver;
	static Logger log = Logger.getLogger(AdminSingleSelectFieldPage.class);

	public AdminSingleSelectFieldPage(WebDriver driver) {
		System.out.print("in textfield page");
		this.driver = driver;
		PageFactory.initElements(driver, this);
		
	}
	/*
	 * Locators for single select field creation  
	 */

	@FindBy(how = How.XPATH, using = "//div[@class='chzn-container chzn-container-single']//a[@class='chzn-single']")
	public WebElement click_field_type;
	@FindBy(how = How.XPATH,using = "//ul/li[text()=\"Single Select (Deprecated. Use Field Type 'List')\"]")
	public WebElement select_singl;
	@FindBy(how = How.XPATH, using ="//button[@class='btn btn-small button-apply btn-success']")
	public WebElement saveForValidation;
	@FindBy(how = How.XPATH, using = "//input[@id='jform_label']")
	public WebElement sslable;
	@FindBy(how = How.XPATH,using = "//input[@id='jform_name']")
	public WebElement Nationalityname;	
	@FindBy(how = How.XPATH,using = "//input[@id='jform_fieldoption__fieldoption0__name']")
	public WebElement countryname1;	
	@FindBy(how = How.XPATH,using = "//input[@id='jform_fieldoption__fieldoption0__value']")
	public WebElement countryvalue1;
	@FindBy(how = How.XPATH, using = "//span[@class='icon-plus']")
	public WebElement clickAtPlushIcon; 
	@FindBy(how = How.XPATH,using = "//input[@id='jform_fieldoption__fieldoption1__name']")
	public WebElement countryname2;
	@FindBy(how = How.XPATH,using = "//input[@id='jform_fieldoption__fieldoption1__value']")
	public WebElement countryvalue2;
	@FindBy(how = How.XPATH,using = "//input[@id='jform_fieldoption__fieldoption2__name']")
	public WebElement countryname3;
	@FindBy(how = How.XPATH,using = "//input[@id='jform_fieldoption__fieldoption2__value']")
	public WebElement countryvalue3;	
	@FindBy(how = How.XPATH,using = "//input[@id='jform_fieldoption__fieldoption3__name']")
	public WebElement countryname4;
	@FindBy(how = How.XPATH,using = "//input[@id='jform_fieldoption__fieldoption3__value']")
	public WebElement countryvalue4;	
	@FindBy(how =How.XPATH, using = "//button[@class='btn btn-small button-save-new']")
	public WebElement text_save;
	
	/*
	 * 
	 * Method for single select field creation
	 * 
	 */
	
	public AdminSingleSelectFieldPage singleselectFieldCreation(String ssl, String nn, String cn1, String cv1, String cn2, String cv2, String cn3, String cv3, String cn4, String cv4) {
		click_field_type.click();
		logger.pass("click at field type");
		select_singl.click();
		saveForValidation.click();
		Alert altpopup = driver.switchTo().alert();
		altpopup.accept();
		enterValue(sslable,ssl);
		logger.pass("enter lable of the field");
		enterValue(Nationalityname, nn);
		logger.pass("enter the field name");
		click_field_type.click();
		logger.pass("click at field type");
		select_singl.click();
		enterValue(countryname1,cn1);
		logger.pass("enter country name 1");
		enterValue(countryvalue1,cv1);
		logger.pass("entercountry value 1");
		clickAtPlushIcon.click();
		enterValue(countryname2,cn2);
		logger.pass("enter country name 2");
		enterValue(countryvalue2,cv2);
		logger.pass("entercountry value 2");
		clickAtPlushIcon.click();
		enterValue(countryname3,cn3);
		logger.pass("enter country name 3");
		enterValue(countryvalue3,cv3);
		logger.pass("entercountry value 3");
		clickAtPlushIcon.click();
		enterValue(countryname4,cn4);
		logger.pass("enter country name 4");
		enterValue(countryvalue4,cv4);
		logger.pass("entercountry value 4");
		text_save.click();	
		System.out.println("text field created");
		return new AdminSingleSelectFieldPage(driver);
		
		
	}
}
