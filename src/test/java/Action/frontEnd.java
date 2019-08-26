package Action;

import org.openqa.selenium.Alert;
import org.openqa.selenium.By;
import org.openqa.selenium.JavascriptExecutor;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.support.PageFactory;
import java.util.List;
import org.openqa.selenium.WebElement;
import Excel.Constant;
import Excel.ExcellPath;
import pageObjects.adminObject;
import pageObjects.frontEndObject;

import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.Select;
import org.openqa.selenium.support.ui.Wait;
import org.openqa.selenium.support.ui.WebDriverWait;

public class frontEnd {
	WebDriver driver;

	public static void frontendLogInWithWrongCredential(WebDriver driver) throws Exception {

		PageFactory.initElements(driver, frontEndObject.class);
		ExcellPath.setExcelFile(Constant.TestData_Path, "Sheet1");
		frontEndObject.username.sendKeys(ExcellPath.getCellData(97, 1));
		frontEndObject.password.sendKeys(ExcellPath.getCellData(98, 1));
		frontEndObject.login.click();
		System.out.println("==> Login fail due to Wrong credential");
	}

	public static void frontLogin(WebDriver driver) throws Exception {

		frontEndObject.username.sendKeys(ExcellPath.getCellData(60, 1));
		frontEndObject.password.sendKeys(ExcellPath.getCellData(61, 1));
		frontEndObject.login.click();
		System.out.println("==> Frontend login successfull");
	}

	public static void formFillnagative(WebDriver driver) throws Exception {
		frontEndObject.formMenu.click();
		frontEndObject.firstName.sendKeys(ExcellPath.getCellData(96, 1));
		frontEndObject.number_validate.sendKeys(ExcellPath.getCellData(78, 1));
		frontEndObject.invalid_email.sendKeys(ExcellPath.getCellData(79, 1));
		frontEndObject.validdate.sendKeys(ExcellPath.getCellData(80, 1));
		frontEndObject.charlimit.sendKeys(ExcellPath.getCellData(94, 1));
		frontEndObject.submit.click();
		Thread.sleep(3000);
		Alert altpopup = driver.switchTo().alert();
		altpopup.accept();
		frontEndObject.charlimit.clear();
		frontEndObject.invalid_email.clear();
		frontEndObject.validdate.clear();
		frontEndObject.charlimit.sendKeys(ExcellPath.getCellData(95, 1));
		System.out.println("==> Front end -ve testing done");
	}

	public static void formFill(WebDriver driver) throws Exception {
		Thread.sleep(2000);
		frontEndObject.formMenu.click();
		Thread.sleep(2000);
		frontEndObject.firstName.sendKeys(ExcellPath.getCellData(81,1));
		frontEndObject.lastName.sendKeys(ExcellPath.getCellData(82,1));
		frontEndObject.gender.click();
		driver.findElement(By.id("jform_com_tjucm_pom_form_Enternumber")).sendKeys("1234567890");
		frontEndObject.validEmail.sendKeys(ExcellPath.getCellData(84, 1));
		frontEndObject.validdate.sendKeys(ExcellPath.getCellData(85, 1));
		driver.findElement(By.id("jform_com_tjucm_pom_form_Nationality-lbl")).click();
		frontEndObject.nationality.click();
		frontEndObject.selectNationality.click();
		frontEndObject.language.click();
		frontEndObject.selectlanguage2.click();	
		WebDriverWait wait  = new WebDriverWait(driver, 30);
		wait.until(ExpectedConditions.visibilityOfElementLocated(By.xpath("//*[@id=\"jform_com_tjucm_pom_form_Language_chzn\"]/ul/li[2]"))).click();
		frontEndObject.selectlanguage1.click();
		Thread.sleep(3000);
		frontEndObject.EnterUnivers.sendKeys(ExcellPath.getCellData(86, 1));
		frontEndObject.selecttoggle.click();
		frontEndObject.aboutyourself.sendKeys(ExcellPath.getCellData(87, 1));
		WebElement UploadImg1 = driver.findElement(By.name("jform[com_tjucm_pom-form_UploadFile]"));
		UploadImg1.sendKeys("/home/ttpllt33/Desktop/screenshort /index.jpeg");
		JavascriptExecutor js3 = (JavascriptExecutor) driver; // for scroll
		js3.executeScript("window.scrollBy(0,10000)");
		frontEndObject.charlimit.sendKeys(ExcellPath.getCellData(89,1));
		frontEndObject.checkbox.click();
		frontEndObject.vedioLink.sendKeys(ExcellPath.getCellData(90, 1));
		frontEndObject.AudioLink.sendKeys(ExcellPath.getCellData(91, 1));
		Thread.sleep(3000);
		frontEndObject.subformClick1.click();
		frontEndObject.subformValue1.sendKeys(ExcellPath.getCellData(92, 1));
		frontEndObject.subformClick2.click();
		frontEndObject.subformClickminus.click();
		
		frontEndObject.submit.click();
			Thread.sleep(3000);
			   Alert altpopup = driver.switchTo().alert();
			   altpopup.accept();
			   System.out.println("==> Front end +ve testing done");		
	}

}
