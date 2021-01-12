package com.ucm.pageobjects;

import org.apache.log4j.Logger;
import org.apache.tools.ant.taskdefs.Sleep;
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
 * This is Page Class for Admin Login. It contains all the elements and actions
 * related to Admin Login.
 * 
 */

public class FrontImportPage extends BaseClass {

	private WebDriver driver;
	private static Logger log = Logger.getLogger(FrontImportPage.class);

	public FrontImportPage(WebDriver driver) {
		this.driver = driver;
		PageFactory.initElements(driver, this);
	}

	/*
	 * Locators for Login
	 */

	@FindBy(how = How.ID, using = "import-items")
	public WebElement importButton;
	@FindBy(how = How.ID, using = "upload-submit")
	public WebElement uploadSubmit;
	@FindBy(how = How.ID, using = "sbox-btn-close")
	public WebElement importCloseButton;
	@FindBy(how = How.XPATH, using = "//*[@id=\"sbox-content\"]/iframe")
	public WebElement iframeElement;
	@FindBy(how = How.NAME, using = "csv-file-upload")
	public WebElement importfile;
	
	/*
	 * 
	 * Method for import checking
	 * 
	 */

	public FrontImportPage importFlow(String fi) throws Exception {
		importButton.click();
		logger.pass("click at import button");
		driver.switchTo().frame(iframeElement); // switch to iFrame
		enterValue(importfile, Constant.DEFAULTSYSTEMPATH + fi); // Giveback image
		uploadSubmit.click();
		logger.pass("click at upload button without uploading anything");
		driver.switchTo().defaultContent();
		importCloseButton.click();
		logger.pass("click at import close button");
		return new FrontImportPage(driver);
	}
	
	
}
