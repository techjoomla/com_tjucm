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
 * This is Page Class for radio button field creation . It contains all the elements and actions
 * related to radio button field creation view.
 * 
 */

public class AdminRadioFieldPage extends BaseClass {

	private WebDriver driver;
	static Logger log = Logger.getLogger(AdminRadioFieldPage.class);

	public AdminRadioFieldPage(WebDriver driver) {
		this.driver = driver;
		PageFactory.initElements(driver, this);
	}
	/*
	 * Locators for radio button  
	 */

	@FindBy(how = How.XPATH, using = "//div[@class='chzn-container chzn-container-single']//a[@class='chzn-single']")
	public WebElement click_field_type;
	@FindBy(how = How.XPATH, using ="//ul/li[text()='Radio']")
	public WebElement select_radio;
	@FindBy(how = How.XPATH, using ="//button[@class='btn btn-small button-apply btn-success']")
	public WebElement saveForValidation;
	@FindBy(how = How.XPATH, using = "//input[@id='jform_label']")
	public WebElement lable;
	@FindBy(how = How.XPATH, using = "//input[@id='jform_name']")
	public WebElement radio_name;
	@FindBy(how = How.XPATH, using = "//input[@name='jform[fieldoption][fieldoption0][name]']")
	public WebElement optionname1;
	@FindBy(how = How.XPATH, using = "//input[@name='jform[fieldoption][fieldoption0][value]']")
	public WebElement optionvalue1;
	@FindBy(how = How.XPATH, using = "//span[@class='icon-plus']")
	public WebElement clickAtPlushIcon; 
	@FindBy(how = How.XPATH, using ="//input[@name='jform[fieldoption][fieldoption1][value]']")
	public WebElement optionvalue2;
	@FindBy(how = How.XPATH, using ="//input[@name='jform[fieldoption][fieldoption1][name]']")
	public WebElement optionname2;
	@FindBy(how =How.XPATH, using = "//button[@class='btn btn-small button-save-new']")
	public WebElement text_save;
	/*
	 * 
	 * Method for radio button creation
	 * 
	 */
	
	public AdminRadioFieldPage radioButtonCreation(String l, String rn, String on1, String ov1, String on2, String ov2) {
		click_field_type.click();
		logger.pass("Click at field type");
		select_radio.click();
		logger.pass("select radiobutton from type");
		saveForValidation.click();
		logger.pass("select validation");
		Alert altpopup = driver.switchTo().alert();
		altpopup.accept();
		enterValue(lable, l);	
		logger.pass("Enter lable name");
		enterValue(radio_name, rn);
		logger.pass("Enter radio button name");
		enterValue(optionname1, on1);
		logger.pass("Enter option name one");
		enterValue(optionvalue1, ov1);
		logger.pass("Enter option value one");
		clickAtPlushIcon.click();
		logger.pass("click at plus icon");
		enterValue(optionname2, on2);
		logger.pass("enter option name two");
		enterValue(optionvalue2,ov2);
		logger.pass("enter option value  two");
		text_save.click();		
		System.out.println("UCMform radio button field");
		return new AdminRadioFieldPage(driver);
	}
}
