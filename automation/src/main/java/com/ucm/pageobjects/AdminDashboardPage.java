package com.ucm.pageobjects;

import org.apache.log4j.Logger;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import org.openqa.selenium.support.How;
import org.openqa.selenium.support.PageFactory;

import com.ucm.config.BaseClass;

/**
 * This is Page Class for creating the sub form . It contains all the elements and actions
 * related to subform creation view.
 * 
 */

public class AdminDashboardPage extends BaseClass {

	private WebDriver driver;
	static Logger log = Logger.getLogger(AdminDashboardPage.class);

	public AdminDashboardPage(WebDriver driver) {
		this.driver = driver;
		PageFactory.initElements(driver, this);
	}
	
	/*
	 * Locators for Admin Dashboard
	 */

	@FindBy(how = How.XPATH, using = "//a[contains(text(),'Components')]")
	public WebElement components;

	@FindBy(how = How.XPATH, using = "//*[@id='menu']/li[5]/ul/li/a[text()='TJ - UCM']")
	public WebElement dropdown;
	/*
	 * 
	 * Method for DashboardPage
	 * 
	 */

	public AdminDashboardPage dashboard() {

		components.click(); // Components tab
		logger.pass("User click on components");
		dropdown.click(); // Dropdown list of components
		logger.pass("User select component as ucm");
		System.out.println("Welcome to dashboard page");
		logger.pass("Welcome to dashboard page");
		return new AdminDashboardPage(driver);
	} 

}
