package com.ucm.pageobjects;

import java.util.List;

import org.apache.log4j.Logger;
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

public class AdminUcmFormCreationPage extends BaseClass {

	private WebDriver driver;
	static Logger log = Logger.getLogger(AdminUcmFormCreationPage.class);

	public AdminUcmFormCreationPage(WebDriver driver) {
		this.driver = driver;
		PageFactory.initElements(driver, this);
	}
	/*
	 * Locators for 
	 */

	@FindBy(how = How.XPATH, using ="//a[text()='Types']")
	public WebElement Type; 
	@FindBy(how = How.XPATH, using ="//*[@id='toolbar-new']")
	public WebElement newType;
	@FindBy(how = How.NAME, using="jform[title]")
	public WebElement titleName;
	
	/*
	 * 
	 * Method for formcreation
	 * 
	 */

	public AdminUcmFormCreationPage ucmForm(String tn) {
		
		Type.click(); 
		logger.pass("Click at type button");
		newType.click();
		logger.pass("click at newtype link");
		enterValue(titleName, tn);
		return new AdminUcmFormCreationPage(driver);
	}
}
