package pageObjects;

import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.support.FindBy;
import org.openqa.selenium.support.How;
//import org.testng.annotations.Factory;

//import com.gargoylesoftware.htmlunit.javascript.host.media.webkitMediaStream;

import config.BasicClass;
public class frontEndObject extends BasicClass {
	@FindBy(how = How.ID, using = "modlgn-username")
	public static WebElement username;
	
	@FindBy(how = How.ID, using = "modlgn-passwd")
	public static WebElement password;
	
	@FindBy(how = How.XPATH, using = "//input[@name='Submit']")
	public static WebElement login;
	
	@FindBy(how = How.XPATH, using = "//a[text()='SAMPLE UCM FORM ']")
	public static WebElement formMenu;
	
	@FindBy(how = How.ID, using ="finalSave")
	public static WebElement submit;
	
	@FindBy(how = How.XPATH, using ="//input[@type='number']")
	public static WebElement number_validate;  
	
	@FindBy(how = How.XPATH, using ="//input[@type='email']")
	public static WebElement invalid_email;  
	
	@FindBy(how = How.NAME, using ="jform[com_tjucm_pom-form_Firstname]")
	public static WebElement firstName;
	
	@FindBy(how = How.NAME, using ="jform[com_tjucm_pom-form_LastName]")
	public static WebElement lastName;
	
	@FindBy(how = How.XPATH, using ="//input[@id='jform_com_tjucm_pom_form_Gender1']")
	public static WebElement gender;
	
	@FindBy(how = How.NAME, using ="jform[com_tjucm_pom-form_Enternumber]")
	public static WebElement validNumber;
	
	@FindBy(how = How.NAME, using ="jform[com_tjucm_pom-form_Entertheemailid]")
	public static WebElement validEmail;
	
	@FindBy(how = How.XPATH, using ="//*[@id=\"jform_com_tjucm_pom_form_date\"]")
	public static WebElement validdate;
	
	@FindBy(how = How.XPATH, using ="//*[@id=\"jform_com_tjucm_pom_form_Nationality_chzn\"]/a/span")
	public static WebElement nationality;	
	
	@FindBy(how = How.XPATH, using ="//*[@id=\"jform_com_tjucm_pom_form_Nationality_chzn\"]/div/ul/li[5]")
	public static WebElement selectNationality;	
	
	@FindBy(how = How.ID, using ="jform_com_tjucm_pom_form_Language_chzn")
	public static WebElement language;
	
	@FindBy(how = How.XPATH, using ="//*[@id=\"jform_com_tjucm_pom_form_Language_chzn\"]/div/ul/li[3]")
	public static WebElement selectlanguage1;
	
	@FindBy(how = How.XPATH, using ="//*[@id=\"jform_com_tjucm_pom_form_Language_chzn\"]/div/ul/li[1]")
	public static WebElement selectlanguage2;
	
	@FindBy(how = How.NAME, using ="jform[com_tjucm_pom-form_AboutyourUniversity]")
	public static WebElement EnterUnivers;

	@FindBy(how = How.XPATH, using ="//*[@id=\"item-form\"]/div[2]/div/div[10]/div/div[2]/div/div[2]/div/a")
	public static WebElement selecttoggle;

	@FindBy(how = How.NAME, using ="jform[com_tjucm_pom-form_Aboutyourself]")
	public static WebElement aboutyourself;
	
	@FindBy(how = How.ID, using ="jform_com_tjucm_pom_form_UploadFile")
	public static WebElement uploadimage;
	
	@FindBy(how = How.ID, using ="jform_com_tjucm_pom_form_DescriptionAboutyourExperiences")
	public static WebElement charlimit;
	
	@FindBy(how = How.XPATH, using ="//*[@id=\"jform_com_tjucm_pom_form_selectallusers_chzn\"]")
	public static WebElement clickuser ;
	
	@FindBy(how = How.XPATH, using ="//ul[@class='chzn-results']//li[text()='test@gmail.com']")
	public static WebElement selectUser ;
	
	@FindBy(how = How.NAME, using ="jform[com_tjucm_pom-form_Checkterms]")
	public static WebElement checkbox ;
	
	@FindBy(how = How.ID, using ="jform_com_tjucm_pom_form_Vediolink")
	public static WebElement vedioLink ;
	
	@FindBy(how = How.NAME, using ="jform[com_tjucm_pom-form_Audiolink]")
	public static WebElement AudioLink ;
	
	@FindBy(how = How.XPATH, using ="//div[@class='btn-group']//a[@class='btn btn-mini button btn-success group-add group-add-sr-0']")
	public static WebElement subformClick1;
	
	@FindBy(how = How.ID, using ="jform_com_tjucm_pom_form_AboutyourExperiences__com_tjucm_pom_form_AboutyourExperiencesX__com_tjucm_sub_form_Subform")
	public static WebElement subformValue1;
	
	@FindBy(how = How.XPATH, using ="//*[@id=\"item-form\"]/div[2]/div/div[17]/div/div[2]/div/div/div/div[2]/div[1]/div/a[1]")
	public static WebElement subformClick2;
	
	@FindBy(how = How.XPATH, using ="//*[@id=\"item-form\"]/div[2]/div/div[17]/div/div[2]/div/div/div/div[3]/div[1]/div/a[2]")
	public static WebElement subformClickminus;
	

	//*[@id="jform_com_tjucm_pom_form_AboutyourExperiences__com_tjucm_pom_form_AboutyourExperiencesX__com_tjucm_sub_form_Subform"]
	//*[@id="jform_com_tjucm_pom_form_AboutyourExperiences__com_tjucm_pom_form_AboutyourExperiencesX__com_tjucm_sub_form_Subform"]
	//*[@id="jform_com_tjucm_pom_form_AboutyourExperiences__com_tjucm_pom_form_AboutyourExperiencesX__com_tjucm_sub_form_Subform"]
	
	
	
	
}