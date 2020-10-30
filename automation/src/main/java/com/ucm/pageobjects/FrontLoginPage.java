package com.ucm.pageobjects;

import org.apache.log4j.Logger;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import org.openqa.selenium.support.How;
import org.openqa.selenium.support.PageFactory;

import com.ucm.config.BaseClass;
import com.ucm.utils.Constant;
import com.ucm.utils.ExcelUtils;
import com.ucm.utils.Screenshot;

/**
 * This is Page Class for frontend  Login. It contains all the elements and actions
 * related to frontend Login.
 * 
 */

public class FrontLoginPage extends BaseClass {

	private WebDriver driver;
	private static Logger log = Logger.getLogger(FrontLoginPage.class);

	public FrontLoginPage(WebDriver driver) {
		this.driver = driver;
		PageFactory.initElements(driver, this);
	}

	/*
	 * Locators for Login
	 */
	@FindBy(how = How.XPATH, using = "//a[contains(text(),'Login')]")
	public WebElement menuclick;
	@FindBy(how = How.ID, using = "username")
	public WebElement fusername;
	@FindBy(how = How.ID, using = "password")
	public WebElement fpassword;
	@FindBy(how = How.XPATH, using = "//button [@class=\"btn btn-block btn-success\"]")
	public WebElement flogin;
		

	/*
	 * 
	 * Method for valid login
	 * 
	 */

	public DashboardPage validLogin(String fun, String fpw) throws Exception {
		menuclick.click();
		enterValue(fusername, fun); // Login Username
		logger.pass("user name is fetched from excelsheet");
		enterValue(fpassword, fpw); // Login Password
		logger.pass("password fetched from excelsheet");
		click(flogin); // Login Button
		logger.pass("User click on the login button");
		logger.pass("User logged Successfully");
		return new DashboardPage(driver);
	}

	/*
	 * 
	 * Method for invalid login
	 * 
	 */

	public FrontLoginPage invalidLogin(String fun, String fpw) throws Exception {
		menuclick.click();
		enterValue(fusername, fun); // Login Username
		logger.pass("username is fetched from excelsheet");
		enterValue(fpassword, fpw);  // Login Password
		logger.pass("password is fetched from excelsheet");
		click(flogin); // Login Button
		logger.pass(" User click on the login button");
		logger.pass(" User login with invalid Credentials");
		return new FrontLoginPage(driver);

	}
	
}
