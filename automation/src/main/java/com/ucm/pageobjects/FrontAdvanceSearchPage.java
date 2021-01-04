package com.ucm.pageobjects;

import java.io.File;

import org.apache.log4j.Logger;
//import org.apache.tools.ant.taskdefs.Sleep;
import org.openqa.selenium.Alert;
import org.openqa.selenium.By;
import org.openqa.selenium.JavascriptExecutor;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import org.openqa.selenium.support.How;
import org.openqa.selenium.support.PageFactory;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;

import com.mongodb.operation.DropDatabaseOperation;
import com.ucm.config.BaseClass;
import com.ucm.utils.Constant;


/**
 * This is Page Class for AdvanceSearch . It contains all the elements and actions
 * related to AdvanceSearch view.
 * 
 */

public class FrontAdvanceSearchPage extends BaseClass {

	private WebDriver driver;
	static Logger log = Logger.getLogger(FrontAdvanceSearchPage.class);

	public FrontAdvanceSearchPage(WebDriver driver) {
		this.driver = driver;
		PageFactory.initElements(driver, this);
		
	}
	/*
	 * Locators for AdvanceSearch  
	 */
	
	@FindBy(how = How.XPATH, using = "//a[contains(text(),'UCM list view ')]")
	public WebElement listMenu;
	@FindBy(how = How.XPATH, using ="//a[text()='ID']")
	public WebElement sortId;
	@FindBy(how = How.XPATH, using ="//a[text()='Published']")
	public WebElement sortPublish;
	@FindBy(how = How.XPATH, using ="//a[text()='Status']")
	public WebElement sortStatus;
	@FindBy(how = How.XPATH, using ="//a[text()='Name']")
	public WebElement sortName;
	@FindBy(how = How.XPATH, using ="//a[text()='Gender']")
	public WebElement sortGender;
	@FindBy(how = How.XPATH, using ="//a[text()='phone no']")
	public WebElement sortPhone;
	@FindBy(how = How.XPATH, using ="//a[text()='Date of birth']")
	public WebElement sortDOB;
	@FindBy(how = How.XPATH, using ="//a[text()='Email']")
	public WebElement sortEmail;
	@FindBy(how = How.XPATH, using ="//a[text()='Nationality']")
	public WebElement sortNationality;
	@FindBy(how = How.XPATH, using ="//a[text()='Language']")
	public WebElement sortLanguage;
	@FindBy(how = How.XPATH, using ="//a[text()='About your University']")
	public WebElement sortUniversity;
	@FindBy(how = How.XPATH, using ="//a[text()='About you']")
	public WebElement sortAbout;
	@FindBy(how = How.XPATH, using ="//a[text()='Enter your CV']")
	public WebElement sortcv;
	@FindBy(how = How.XPATH, using ="//a[text()='Only pdf file']")
	public WebElement sortpdf;
	@FindBy(how = How.XPATH, using ="//a[text()='Description About your  Experiences']")
	public WebElement sortExperiences;
	@FindBy(how = How.XPATH, using ="//a[text()='Check terms and condition']")
	public WebElement sortTerms;
	@FindBy(how = How.XPATH, using ="//a[text()='select users']")
	public WebElement sortUser;
	@FindBy(how = How.XPATH, using ="//a[text()='About your  Experiences']")
	public WebElement sortAUE;
	@FindBy(how = How.XPATH, using ="//a[text()='Education']")
	public WebElement sortEducation;
	@FindBy(how = How.XPATH, using ="//a[text()='Video link']")
	public WebElement sortVideo;
	@FindBy(how = How.XPATH, using ="//a[text()='Audio link']")
	public WebElement sortAudio;
	@FindBy(how = How.XPATH, using ="//a[span ='Select item status']")
	public WebElement searchStatus;
	@FindBy(how = How.XPATH, using ="//*[@id=\"draft_chzn\"]/div/ul/li[2]")
	public WebElement selectSaved;
	@FindBy(how = How.XPATH, using ="//*[@id=\"draft_chzn\"]/a/span")
	public WebElement clickSaved;
	@FindBy(how = How.XPATH, using ="//*[@id=\"draft_chzn\"]/div/ul/li[1]")
	public WebElement clickselect;
	@FindBy(how = How.XPATH, using ="//input[@name='filter_search']")
	public WebElement clickFilter;
	@FindBy(how = How.XPATH, using ="//input[@name='filter_search']")
	public WebElement EnterFilter;
	@FindBy(how = How.XPATH, using ="//*[@id=\"filter-progress-bar\"]/div[3]/button[1]")
	public WebElement clickAtSearch;
	@FindBy(how = How.XPATH, using ="//*[@id=\"clear-search-button\"]")
	public WebElement clickAtclear;
	/*
	 * 
	 * Method for AdvanceSearch
	 * 
	 */
	
	
	public FrontAdvanceSearchPage aSearch(String ef) throws InterruptedException {
		Thread.sleep(2000);
		listMenu.click();
		logger.pass("click at list menu");
		sortPublish.click();
		logger.pass("click at public for sorting");
		sortId.click();
		logger.pass("click at ID for sorting");
		sortStatus.click();
		logger.pass("click at status for sorting");
		sortName.click();
		logger.pass("click at name for sorting");
		sortGender.click();
		logger.pass("click at gender for sorting");
		sortPhone.click();
		logger.pass("click at phone no for sorting");
		sortDOB.click();
		logger.pass("click at date of birth for sorting");
		sortEmail.click();
		logger.pass("click at email for sorting");
		sortNationality.click();
		logger.pass("click at nationality for sorting");
		sortLanguage.click();
		logger.pass("click at language for sorting");
		sortUniversity.click();
		logger.pass("click at university for sorting");
		sortAbout.click();
		logger.pass("click at about for sorting");
		sortcv.click();
		logger.pass("click at cv for sorting");
		sortpdf.click();
		logger.pass("click at pdf for sorting");
		sortExperiences.click();
		logger.pass("click at experience for sorting");
		sortTerms.click();
		logger.pass("click at term and condition for sorting");
		sortUser.click();
		logger.pass("click at user for sorting");
		sortAUE.click();
		logger.pass("click at about ur education for sorting");
		sortEducation.click();
		logger.pass("click at education for sorting");
		sortVideo.click();
		logger.pass("click at video for sorting");
		sortAudio.click();			
		logger.pass("click at audio for sorting");
		searchStatus.click();
		logger.pass("click at search button");
		selectSaved.click();
		logger.pass("select saved option from dropdown");
		clickSaved.click();
		clickselect.click();
		logger.pass("deselect status");
		clickFilter.click();
		enterValue(clickFilter, ef);
		clickAtSearch.click();
		logger.pass("click at search");
		clickAtclear.click();	
		logger.pass("click at clear");
		return new FrontAdvanceSearchPage(driver);

	}

}
