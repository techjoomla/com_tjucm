package com.ucm.config;

import java.io.FileInputStream;
import java.io.IOException;
import java.util.Properties;
import java.util.concurrent.TimeUnit;

import org.openqa.selenium.JavascriptExecutor;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.chrome.ChromeDriver;
import org.openqa.selenium.chrome.ChromeOptions;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.Select;
import org.openqa.selenium.support.ui.WebDriverWait;
import org.testng.ITestResult;
import org.testng.annotations.AfterMethod;
import org.testng.annotations.AfterSuite;
import org.testng.annotations.BeforeSuite;

import com.aventstack.extentreports.ExtentReports;
import com.aventstack.extentreports.ExtentTest;
import com.aventstack.extentreports.reporter.ExtentHtmlReporter;
import com.aventstack.extentreports.MediaEntityBuilder;
import com.ucm.utils.Constant;
import com.ucm.utils.ExcelUtils;
import com.ucm.utils.Screenshot;

import io.github.bonigarcia.wdm.WebDriverManager;

/**
 * This is Page Class for BaseClass. It contains all the actions related to
 * BaseClass.
 * 
 */

public class BaseClass {

	public static WebDriver driver;
	public static Properties properties;
	public static ExtentReports extent;
	public static ExtentTest logger;
	public static ExtentHtmlReporter htmlReporter;
	
	public void click(WebElement name){
		name.click();
	}
	
	public void clearValue(WebElement name){
		name.clear();
	}
	
	public void selectDropdown(WebElement name, String value) {
		 
		Select s = new Select(name);
		s.selectByVisibleText(value);
	}
	
	public void enterValue(WebElement name, String value) {
		name.sendKeys(value);
	}
	
	public void clearAndEnterValue(WebElement name, String value) {
		name.clear();
		name.sendKeys(value);
	}
	
	public void enterValueInIframe(WebElement name, String value) {
		driver.switchTo().frame(name);
		WebElement editable = driver.switchTo().activeElement();
		editable.sendKeys(value);
		driver.switchTo().defaultContent();
	}
	
	public void selectDropdownValue(WebElement name1, WebElement name2) {
        WebDriverWait wait = new WebDriverWait(driver,20);
		
		name1.click();
		wait.until(ExpectedConditions.visibilityOf(name2));
		name2.click();
	}
	
	public void scrollDown1() {
		JavascriptExecutor js1 = (JavascriptExecutor) driver;
		js1.executeScript("window.scrollBy(0,90000)");
	}
    
    public void scrollUp1() {
    	JavascriptExecutor jse1 = (JavascriptExecutor)driver;
		jse1.executeScript("window.scrollBy(0,250)", "");
		jse1.executeScript("window.scrollBy(0,-1000)", "");
    }
    
   public void selectRadioButton(WebElement name) {
    	name.click();
    }



	@BeforeSuite(alwaysRun=true)
	//@BeforeSuite()
	public void setup() {

		try {
			WebDriverManager.chromedriver().setup();
			ChromeOptions options = new ChromeOptions();
			System.setProperty("webdriver.chrome.args", "--disable-logging");
			System.setProperty("webdriver.chrome.silentOutput", "true");
			options.addArguments("--headless", "--log-level=3", "--no-sandbox", "--disable-gpu",
					"--window-size=1920,1200", "--ignore-certificate-errors");
			driver = new ChromeDriver(options);
			properties = new Properties();
			FileInputStream fis = new FileInputStream(
					System.getProperty("user.dir") + "/src/main/java/com/ucm/config/properties.properties");
			properties.load(fis);
	
			Screenshot.captureScreenshot(driver, "Browser Started");
			driver.manage().window().maximize();
			driver.manage().deleteAllCookies();
			driver.manage().timeouts().pageLoadTimeout(30, TimeUnit.SECONDS);
			driver.manage().timeouts().implicitlyWait(30, TimeUnit.SECONDS);
			htmlReporter = new ExtentHtmlReporter("extent.html");
			extent = new ExtentReports();
			extent.attachReporter(htmlReporter);
			System.out.println("Site URL is:" + Constant.SITEURL);
			try {
				if (!Constant.SITEURL.isEmpty())
					driver.get(Constant.SITEURL);
			} catch (Exception e) {
				driver.get(properties.getProperty("url"));
			}
			
		} catch (Exception e) {
			e.printStackTrace();
		}

	}

	@AfterMethod
	public void evaluateStatus(ITestResult result) throws IOException {

		if (result.getStatus() == ITestResult.FAILURE) {
			String screenshotPath = Screenshot.getScreenshot(driver);

			logger.fail(result.getThrowable().getMessage(),
					MediaEntityBuilder.createScreenCaptureFromPath(screenshotPath).build());
		}

	}

	@AfterSuite(alwaysRun=true)
	//@AfterSuite()
	public void tearDown() {
		extent.flush();
	}

}
