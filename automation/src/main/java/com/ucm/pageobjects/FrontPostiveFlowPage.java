package com.ucm.pageobjects;

import java.io.File;

import org.apache.log4j.Logger;
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
 * This is Page Class for SQL field creation . It contains all the elements and actions
 * related to SQL field creation view.
 * 
 */

public class FrontPostiveFlowPage extends BaseClass {

	private WebDriver driver;
	static Logger log = Logger.getLogger(FrontPostiveFlowPage.class);

	public FrontPostiveFlowPage(WebDriver driver) {
		this.driver = driver;
		PageFactory.initElements(driver, this);
		
	}
	/*
	 * Locators for SQL field creation  
	 */
	
	@FindBy(how = How.XPATH, using = "//a[contains(text(),'UCM Form ')]")
	public WebElement formMenu;
	@FindBy(how = How.NAME, using ="jform[com_tjucm_pomform_Name]")
	public WebElement firstName;
	@FindBy(how = How.XPATH, using ="//input[@id='jform_com_tjucm_pomform_Gender1']")
	public WebElement gender;
	@FindBy(how = How.NAME, using ="jform[com_tjucm_pomform_phoneno]")
	public WebElement validNumber;
	@FindBy(how = How.NAME, using ="jform[com_tjucm_pomform_Email]")
	public WebElement validEmail;
	@FindBy(how = How.XPATH, using ="//*[@id='jform_com_tjucm_pomform_Dateofbirth']")
	public WebElement validdate;
	@FindBy(how = How.XPATH, using ="//*[@id='jform_com_tjucm_pomform_Nationality_chzn']/a/span")
	public WebElement nationality;	
	@FindBy(how = How.XPATH, using ="//*[@id='jform_com_tjucm_pomform_Nationality_chzn']/div/ul/li[4]")
	public WebElement selectNationality;	
	@FindBy(how = How.ID, using ="jform_com_tjucm_pomform_Language_chzn")
	public WebElement language;
	@FindBy(how = How.XPATH, using ="//*[@id='jform_com_tjucm_pomform_Language_chzn']/div/ul/li[1]")
	public WebElement selectlanguage1;
	@FindBy(how = How.XPATH, using ="//*[@id='jform_com_tjucm_pomform_Language_chzn']/div/ul/li[3]")
	public WebElement selectlanguage2;
	@FindBy(how = How.NAME, using ="jform[com_tjucm_pomform_AboutyourUniversity]")
	public WebElement EnterUnivers;
	@FindBy(how = How.XPATH, using ="//*[@id=\"item-form\"]/div[4]/div/div[9]/div/div[2]/div/div[2]/div/a")
	public WebElement selecttoggle;
	@FindBy(how = How.NAME, using ="jform[com_tjucm_pomform_Aboutyou]")
	public WebElement aboutyourself;
	@FindBy(how = How.ID, using ="jform_com_tjucm_pomform_EnteryourCV")
	public WebElement uploadimage;
	@FindBy(how = How.ID, using ="jform_com_tjucm_pomform_Onlypdffile")
	public WebElement uploadimage1;
	@FindBy(how = How.ID, using ="jform_com_tjucm_pomform_DescriptionAboutyourExperiences")
	public WebElement charlimit;
	@FindBy(how = How.XPATH, using ="//*[@id=\"jform_com_tjucm_pomform_selectallusers_chzn\"]")
	public WebElement clickuser ;
	@FindBy(how = How.XPATH, using ="//ul[@class='chzn-results']//li[text()='test@gmail.com']")
	public WebElement selectUser ;
	@FindBy(how = How.NAME, using ="jform[com_tjucm_pomform_Checktermsandcondition]")
	public WebElement checkbox ;
	@FindBy(how = How.ID, using ="jform_com_tjucm_pomform_itemcategoryitemcategory_chzn")
	public WebElement catclick ;
	@FindBy(how = How.XPATH, using ="//*[@id=\"jform_com_tjucm_pomform_itemcategoryitemcategory_chzn\"]/div/ul/li[2]")
	public WebElement catselect ;	
	@FindBy(how = How.ID, using ="jform_com_tjucm_pomform_Videolink")
	public WebElement vedioLink ;
	@FindBy(how = How.NAME, using ="jform[com_tjucm_pomform_Audiolink]")
	public WebElement AudioLink ;
	@FindBy(how = How.XPATH, using ="//input[@onclick='tjUcmItemForm.saveUcmFormData();'][2]")
	public WebElement finalsubmit;
	@FindBy(how = How.XPATH, using ="//div[@class='btn-group']//a[@class='btn btn-mini button btn-success group-add group-add-sr-0']")
	public WebElement subformClick1;
	@FindBy(how = How.NAME, using ="jform[com_tjucm_pomform_AboutyourExperiences][com_tjucm_pomform_AboutyourExperiences0][com_tjucm_subform_Subformfield]")
	public WebElement subformValue1;
	

	/*
	 * 
	 * Method for SQL field creation
	 * 
	 */
	
	
	
	public FrontPostiveFlowPage NagativeFlow(String fnf, String nf, String ve, String vd,String eu, String ays, String ui, String cl,String sv1,String vl, String al, String ui1) {
		formMenu.click();
		enterValue(firstName,fnf);
		logger.pass("enter 1st name -ve");
		enterValue(validNumber,nf);
		logger.pass("enter phone no -ve");
		enterValue(validEmail,ve);
		logger.pass("enter email -ve");
		enterValue(validdate,vd);
		logger.pass("enter date -ve");
		scrollDown1();
		enterValue(uploadimage1, Constant.DEFAULTSYSTEMPATH + ui1); // Giveback image
		finalsubmit.click();
		firstName.clear();
		validNumber.clear();
		validdate.clear();
		validEmail.clear();
		uploadimage1.clear();
			
		return new FrontPostiveFlowPage(driver);

	}
	
	public FrontPostiveFlowPage PostiveFlow(String fnf, String nf, String ve, String vd, String eu, String ays, String ui, String cl,String sv1,String vl, String al) {
		
		formMenu.click();
		logger.pass("click at form link");
		enterValue(firstName,fnf);
		logger.pass("enter 1st name");
		gender.click();
		logger.pass("select gender");
		validNumber.clear();
		enterValue(validNumber,nf);
		logger.pass("enter phone no");
		enterValue(validEmail,ve);
		logger.pass("enter email");
		enterValue(validdate,vd);
		logger.pass("enter date");
		nationality.click();
		logger.pass("click at nationlity");
		selectNationality.click();
		logger.pass("select nationality");
		language.click();
		selectlanguage2.click();
		logger.pass("select languages");
		WebDriverWait wait  = new WebDriverWait(driver, 30);
		wait.until(ExpectedConditions.visibilityOfElementLocated(By.xpath("//*[@id=\"jform_com_tjucm_pomform_Language_chzn\"]/ul/li[2]"))).click();
		selectlanguage1.click();
		enterValue(EnterUnivers,eu);
		logger.pass("enter your univercity");
		JavascriptExecutor jse = (JavascriptExecutor)driver;
		jse.executeScript("arguments[0].scrollIntoView()", selecttoggle); 
		logger.pass("Scroll down");
		WebDriverWait waitFortoggleClick  = new WebDriverWait(driver, 30);
		waitFortoggleClick.until(ExpectedConditions.elementToBeClickable(selecttoggle)).click();
		logger.pass("enter at toggle button");
		enterValue(aboutyourself,ays);
		logger.pass("enter about youself");
		enterValue(uploadimage, Constant.DEFAULTSYSTEMPATH + ui); // Giveback image	
		JavascriptExecutor js3 = (JavascriptExecutor) driver; // for scroll
		js3.executeScript("window.scrollBy(0,10000)");
		logger.pass("select file name from excell and select");
		scrollDown1();
		enterValue(charlimit, cl);
		logger.pass("enter character limit");
		checkbox.click();
		logger.pass("check the check box");
	    subformClick1.click();
	    enterValue(subformValue1,sv1);
	    logger.pass("enter sub form value");
	    catclick.click();
	    logger.pass("click at category dropdown");
	    catselect.click();	    
		enterValue(vedioLink,vl);    	
	    logger.pass("enter vedio link");
	    enterValue(AudioLink, al);
	    logger.pass("enter audio link");
	    WebDriverWait waitsubmit  = new WebDriverWait(driver, 50);
		waitsubmit.until(ExpectedConditions.elementToBeClickable(finalsubmit)).click();
		finalsubmit.click();
		Alert altpopup = driver.switchTo().alert();
		altpopup.accept();
		return new FrontPostiveFlowPage(driver);
			
	}
}
