package com.ucm.pageobjects;

import org.apache.log4j.Logger;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import org.openqa.selenium.support.How;
import org.openqa.selenium.support.PageFactory;

import com.ucm.config.BaseClass;

/**
 * This is Page Class for creating the dashboard contains all the elements and actions
 * related to dashboard.
 * 
 */

public class DashboardPage extends BaseClass {

	private WebDriver driver;
	static Logger log = Logger.getLogger(DashboardPage.class);

	public DashboardPage(WebDriver driver) {
		this.driver = driver;
		PageFactory.initElements(driver, this);
	}
	
	/*
	 * Locators for Dashboard
	 */

	@FindBy(how = How.XPATH, using = "//a[contains(text(),'UCM Form ')]")
	public WebElement ucmlist;

	/*
	 * 
	 * Method for DashboardPage
	 * 
	 */

	public DashboardPage fdashboard() {

		ucmlist.click(); // Components tab
		logger.pass("User click on ucm list link");
		
		logger.pass("Welcome to ucm form");
		return new DashboardPage(driver);
	} 

}
