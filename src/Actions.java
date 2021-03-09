package com.tekdi.nfta.test;



import static com.tekdi.nfta.test.NFTADriver.ObjectRepository;
import static io.restassured.RestAssured.*;
import static org.hamcrest.Matchers.*;

import java.io.File;
import java.io.IOException;
import java.util.List;
import java.util.UUID;
import java.util.logging.Logger;

import org.apache.commons.io.FileUtils;
import org.openqa.selenium.Alert;
import org.openqa.selenium.By;
import org.openqa.selenium.JavascriptExecutor;
import org.openqa.selenium.OutputType;
import org.openqa.selenium.TakesScreenshot;
import org.openqa.selenium.WebDriver;
import org.openqa.selenium.WebElement;
import org.openqa.selenium.chrome.ChromeDriver;
import org.openqa.selenium.chrome.ChromeOptions;
import org.openqa.selenium.firefox.FirefoxDriver;
import org.openqa.selenium.support.ui.ExpectedConditions;
import org.openqa.selenium.support.ui.WebDriverWait;

import com.paulhammant.ngwebdriver.ByAngular;
import com.tekdi.nfta.config.Constant;

import io.github.bonigarcia.wdm.WebDriverManager;

public class Actions {

	WebDriver driver;

	Logger logger = Logger.getLogger(Actions.class.getName());

	/**
	 * openBrowser will instantiate a new instance of a specified browser
	 * 
	 * @param locator
	 * @param data
	 * @return
	 */

	public String openBrowser(String locator, String data) {
		try {
			if (data.equalsIgnoreCase("Chrome")) {
				WebDriverManager.chromedriver().setup();
				driver = new ChromeDriver();
			} else if (data.equalsIgnoreCase("Firefox")) {
				WebDriverManager.firefoxdriver().setup();
				driver = new FirefoxDriver();
			} else {
				WebDriverManager.chromedriver().setup();
				ChromeOptions options = new ChromeOptions();
				options.addArguments("--headless");
				options.addArguments("--disable-gpu");
				driver = new ChromeDriver(options);
			}
			driver.manage().window().maximize();
		} catch (Exception e) {
			return Constant.KEYWORD_FAIL.getValue() + " (Cause of Failure >> " + e.getMessage() + " )";

		}
		return Constant.KEYWORD_PASS.getValue();
	}

	/**
	 * navigateTo will navigate to a specified destination
	 * 
	 * @param locator
	 * @param data
	 * @return
	 */

	public String navigateTo(String locator, String data) {
		try {
			driver.navigate().to(data);
		} catch (Exception e) {
			return Constant.KEYWORD_FAIL.getValue() + " (Cause of Failure >> " + e.getMessage() + " )";
		}
		return Constant.KEYWORD_PASS.getValue();
	}

	/**
	 * enterText to a input field using locator as ID
	 * 
	 * @param locator
	 * @param data
	 * @return
	 */

	public String enterTextByID(String locator, String data) {
		try {
			driver.findElement(By.id(ObjectRepository.getProperty(locator))).sendKeys(data);
		} catch (Exception e) {
			return Constant.KEYWORD_FAIL.getValue() + e.getMessage();
		}
		return Constant.KEYWORD_PASS.getValue();
	}

	/**
	 * enterText to a input field using locator as Name
	 * 
	 * @param locator
	 * @param data
	 * @return
	 */

	public String enterTextByName(String locator, String data) {
		try {
			driver.findElement(By.name(ObjectRepository.getProperty(locator))).sendKeys(data);
		} catch (Exception e) {
			return Constant.KEYWORD_FAIL.getValue() + e.getMessage();
		}
		return Constant.KEYWORD_PASS.getValue();
	}

	/**
	 * enterText to a input field using locator as XPath
	 * 
	 * @param locator
	 * @param data
	 * @return
	 */

	public String enterTextByXpath(String locator, String data) {
		try {
			driver.findElement(By.xpath(ObjectRepository.getProperty(locator))).sendKeys(data);
		} catch (Exception e) {
			return Constant.KEYWORD_FAIL.getValue() + e.getMessage();

		}
		return Constant.KEYWORD_PASS.getValue();
	}

	/**
	 * enterText to a input field using locator as CSS Selectors
	 * 
	 * @param locator
	 * @param data
	 * @return
	 */

	public String enterTextByCss(String locator, String data) {

		try {
			if (data.isEmpty()) {
				return "-- No Data is provided --";
			} else {
				driver.findElement(By.cssSelector(ObjectRepository.getProperty(locator))).sendKeys(data);
			}
		} catch (Exception e) {
			return Constant.KEYWORD_FAIL.getValue() + " (Cause of Failure >> " + e.getMessage() + " )";
		}
		return Constant.KEYWORD_PASS.getValue();
	}

	/**
	 * enterText to a input field using locator as Class Name
	 * 
	 * @param locator
	 * @param data
	 * @return
	 */

	public String enterTextByClassName(String locator, String data) {
		try {
			driver.findElement(By.className(ObjectRepository.getProperty(locator))).sendKeys(data);
		} catch (Exception e) {
			return Constant.KEYWORD_FAIL.getValue() + e.getMessage();

		}
		return Constant.KEYWORD_PASS.getValue();
	}
	
	public String elementToBeClickable(String locator, String data) {
		try {
			WebDriverWait wait = new WebDriverWait(driver, 30);
			wait.until(ExpectedConditions.elementToBeClickable(By.xpath(ObjectRepository.getProperty(locator)))).click();
			driver.findElement(By.xpath(ObjectRepository.getProperty(locator))).click();
		} catch (Exception e) {
			return Constant.KEYWORD_FAIL.getValue() + e.getMessage();

		}
		return Constant.KEYWORD_PASS.getValue();
	}

	/**
	 * clickButton using locator as ID
	 * 
	 * @param locator
	 * @param data
	 * @return
	 */

	public String clickElementByID(String locator, String data) {
		try {
			driver.findElement(By.id(ObjectRepository.getProperty(locator))).click();
		} catch (Exception e) {
			return Constant.KEYWORD_FAIL.getValue() + e.getMessage();

		}
		return Constant.KEYWORD_PASS.getValue();
	}

	/**
	 * clickButton using locator as Name
	 * 
	 * @param locator
	 * @param data
	 * @return
	 */

	public String clickElementByName(String locator, String data) {
		try {
			driver.findElement(By.name(ObjectRepository.getProperty(locator))).click();
		} catch (Exception e) {
			return Constant.KEYWORD_FAIL.getValue() + e.getMessage();

		}
		return Constant.KEYWORD_PASS.getValue();
	}

	/**
	 * clickButton using locator as XPath
	 * 
	 * @param locator
	 * @param data
	 * @return
	 */

	public String clickElementByXpath(String locator, String data) {
		try {
			driver.findElement(By.xpath(ObjectRepository.getProperty(locator))).click();
		} catch (Exception e) {
			return Constant.KEYWORD_FAIL.getValue() + " (Cause of Failure >> " + e.getMessage() + " )";
		}
		return Constant.KEYWORD_PASS.getValue();
	}

	/**
	 * clickButton using locator as CSS Selector
	 * 
	 * @param locator
	 * @param data
	 * @return
	 */

	public String clickElementByCss(String locator, String data) {
		try {
			driver.findElement(By.cssSelector(ObjectRepository.getProperty(locator))).click();
		} catch (Exception e) {
			return Constant.KEYWORD_FAIL.getValue() + " " + e.getMessage();
		}
		return Constant.KEYWORD_PASS.getValue();
	}

	public String acceptAlert(String locator, String data) {
		try {
			//driver.switchTo().alert().accept();
			  // Switching to Alert        
	        Alert alert = driver.switchTo().alert();		
	        		
	        // Capturing alert message.    
	        String alertMessage= driver.switchTo().alert().getText();		
	        		
	        // Displaying alert message		
	        System.out.println(alertMessage);	
	        Thread.sleep(5000);
	        		
	        // Accepting alert		
	        alert.accept();	
		} catch (Exception e) {
			return Constant.KEYWORD_FAIL.getValue() + e.getMessage();

		}
		return Constant.KEYWORD_PASS.getValue();

	}

	public String dismissAlert(String locator, String data) {

		try {
			driver.switchTo().alert().dismiss();
		} catch (Exception e) {
			return Constant.KEYWORD_FAIL.getValue() + e.getMessage();

		}
		return Constant.KEYWORD_PASS.getValue();

	}

	/**
	 * clickOnLinkByLinkText allows user to click the link by using the complete
	 * text present in the link
	 * 
	 * @param locator
	 * @param data
	 * @return
	 */

	public String clickOnLinkByLinkText(String locator, String data) {

		try {
			driver.findElement(By.linkText(ObjectRepository.getProperty(locator))).click();
		} catch (Exception e) {
			return Constant.KEYWORD_FAIL.getValue() + e.getMessage();
		}
		return Constant.KEYWORD_PASS.getValue();

	}

	/**
	 * clickOnLinkByPartialLinkText allows user to click the link by using the
	 * partial text present in the link
	 * 
	 * @param locator
	 * @param data
	 * @return
	 */

	public String clickOnLinkByPartialLinkText(String locator, String data) {

		try {
			driver.findElement(By.partialLinkText(ObjectRepository.getProperty(locator))).click();
		} catch (Exception e) {
			return Constant.KEYWORD_FAIL.getValue() + e.getMessage();
		}
		return Constant.KEYWORD_PASS.getValue();

	}

	public String fileupload(String locator, String filePath) {
		try {
			driver.findElement(By.xpath(ObjectRepository.getProperty(locator))).sendKeys(System.getProperty("user.dir") + filePath);
			//driver.findElement(By.id(ObjectRepository.getProperty(locator))).sendKeys(System.getProperty("user.dir") + filePath);
		} catch (Exception e) {
			return Constant.KEYWORD_FAIL.getValue() + e.getStackTrace();
		}
		return Constant.KEYWORD_PASS.getValue();
	}

	public String scorllDown(String locator, String data) {
		try {
			JavascriptExecutor js = (JavascriptExecutor) driver;
			js.executeScript("window.scrollTo(0, document.body.scrollHeight)");

		} catch (Exception e) {
			return Constant.KEYWORD_FAIL.getValue() + e.getMessage();
		}
		return Constant.KEYWORD_PASS.getValue();
	}

	public String scorllUp(String locator, String data) {
		try {
			JavascriptExecutor js = (JavascriptExecutor) driver;
			js.executeScript("window.scrollTo(document.body.scrollHeight, 0)");

		} catch (Exception e) {
			return Constant.KEYWORD_FAIL.getValue() + e.getMessage();
		}
		return Constant.KEYWORD_PASS.getValue();
	}

	public String selectCheckBoxByCss(String locator, String data) {

		try {
			WebElement element = driver.findElement(By.cssSelector(ObjectRepository.getProperty(locator)));
			if (data.equalsIgnoreCase("Yes")) {
				element.click();
			}

		} catch (Exception e) {
			return Constant.KEYWORD_FAIL.getValue() + e.getMessage();
		}
		return Constant.KEYWORD_PASS.getValue();
	}

	/**
	 * Application specific Keywords
	 * 
	 */

	/**
	 * clickButton using button text
	 * 
	 * @param locator
	 * @param data
	 * @return
	 */

	public String clickButtonByText(String locator, String data) {
		try {

			driver.findElement(ByAngular.buttonText(ObjectRepository.getProperty(locator))).click();

		} catch (Exception e) {
			return Constant.KEYWORD_FAIL.getValue() + " (Cause of Failure >> " + e.getMessage() + " )";

		}
		return Constant.KEYWORD_PASS.getValue();
	}

	public String clickDropdownByXpath(String locator, String data) {
		try {

			if (data.isEmpty()) {
				return " -- No Data is provided --";
			} else {
				driver.findElement(By.xpath(ObjectRepository.getProperty(locator))).click();
				List<WebElement> options = driver
						.findElements(By.xpath(ObjectRepository.getProperty(locator) + "/div/ul/li"));
				for (WebElement option : options) {
					if (option.getText().equals(data)) {
						option.click();
						break;
					}
				}
				driver.findElement(By.xpath(ObjectRepository.getProperty(locator))).click();
			}
		} catch (Exception e) {
			return Constant.KEYWORD_FAIL.getValue() + " (Cause of Failure >> " + e.getMessage() + " )";
		}

		return Constant.KEYWORD_PASS.getValue();
	}

	public String verifyPopupMessage(String locator, String data) {
		try {
			WebDriverWait wait = new WebDriverWait(driver, 10);
			WebElement element = wait.until(ExpectedConditions
					.visibilityOfElementLocated(By.cssSelector(ObjectRepository.getProperty(locator))));
			System.out.println(element.getText());
			return Constant.KEYWORD_PASS.getValue() + " " + element.getText();

		} catch (Exception e) {
			return Constant.KEYWORD_FAIL.getValue() + " (Cause of Failure >> " + e.getMessage() + " )";
		}

	}
 public String RandomstringCreate(String locator, String data)
 {
		String uuid = UUID.randomUUID().toString();
		driver.findElement(By.xpath(ObjectRepository.getProperty(locator))).sendKeys(data);
        return Constant.KEYWORD_PASS.getValue();
}
 
 public String CheckElementEist(String locator, String data)
 {
	 try {

			if (locator.getBytes().equals(data)) {
				
				return " -- No Data is provided --";
			} else {
				driver.findElement(By.xpath(ObjectRepository.getProperty(locator))).click();
				List<WebElement> options = driver
						.findElements(By.xpath(ObjectRepository.getProperty(locator) + "//sui-select-option"));
				for (WebElement option : options) {
					if (option.getText().equals(data)) {
						option.click();
						break;
					}
				}
				driver.findElement(By.xpath(ObjectRepository.getProperty(locator))).click();
			}
		} catch (Exception e) {
			return Constant.KEYWORD_FAIL.getValue() + " (Cause of Failure >> " + e.getMessage() + " )"; 

}
	return data;
 }

 public String verifyErrorMessage(String locator, String data) {
		try {
			return driver.findElement(By.xpath(ObjectRepository.getProperty(locator))).getText();


		} catch (Exception e) {
			return Constant.KEYWORD_FAIL.getValue() + " (Cause of Failure >> " + e.getMessage() + " )";
		}

	}

 
	public String pause(String locator, String data) {
		try {
			Thread.sleep(2000);
		} catch (Exception e) {
			return Constant.KEYWORD_FAIL.getValue() + e.getMessage();
		}
		return Constant.KEYWORD_PASS.getValue();

	}

	public String clickOnRadioByCss(String locator, String data) {
		try {
			if (data.equalsIgnoreCase("Yes")) {
				driver.findElement(By.cssSelector(ObjectRepository.getProperty("openfornominationYes"))).click();
			} else if (data.equalsIgnoreCase("No")) {
				driver.findElement(By.cssSelector(ObjectRepository.getProperty("openfornominationNo"))).click();
			}

		} catch (Exception e) {
			return Constant.KEYWORD_FAIL.getValue() + " (Cause of Failure >> " + e.getMessage() + " )";
		}

		return Constant.KEYWORD_PASS.getValue();

	}

	public String VerifyAPICallStatusIs200(String locator, String data) {

		try {
			given().log().all().when().header("Authorization", "Bearer " + ObjectRepository.getProperty("apikey"))
					.get(data).then().log().all().assertThat().statusCode(equalTo(200));

		} catch (Exception e) {
			return Constant.KEYWORD_FAIL.getValue() + " (Cause of Failure >> " + e.getMessage() + " )";
		}

		return Constant.KEYWORD_PASS.getValue();

	}
	
	public String enterClearTextByXpath(String locator, String data) {
		try {

			driver.findElement(By.xpath(ObjectRepository.getProperty(locator))).clear();
			driver.findElement(By.xpath(ObjectRepository.getProperty(locator))).sendKeys(data);
		} catch (Exception e) {
			return Constant.KEYWORD_FAIL.getValue() + e.getMessage();

		}
		return Constant.KEYWORD_PASS.getValue();
	}
	
	public String quitBrowser(String locator, String data) {
		try {
			driver.quit();
		} catch (Exception e) {
			return Constant.KEYWORD_FAIL.getValue() + " (Cause of Failure >> " + e.getMessage() + " )";
		}
		return Constant.KEYWORD_PASS.getValue();
	}

	/**
	 * Not a keyword
	 */

	public void takesScreenshot(String filename, String testStepResult) throws IOException {
		File scrFile = null;
		if (ObjectRepository.getProperty("takescreeshot_all").equals("Y")) {
			try {
				scrFile = ((TakesScreenshot) driver).getScreenshotAs(OutputType.FILE);
				FileUtils.copyFile(scrFile, new File(System.getProperty(Constant.PROJECT_ROOT_DIRECTORY.getValue())
						+ "/screenshots/" + filename + ".png"));
			} catch (Exception e) {
				logger.warning(Constant.ERROR_SCREENSHOT.getValue() + driver);

			}

		} else if (testStepResult.startsWith(Constant.KEYWORD_FAIL.getValue())
				&& ObjectRepository.getProperty("takescreeshot_failure").equals("Y")) {
			try {
				scrFile = ((TakesScreenshot) driver).getScreenshotAs(OutputType.FILE);
				FileUtils.copyFile(scrFile, new File(System.getProperty(Constant.PROJECT_ROOT_DIRECTORY.getValue())
						+ "/screenshots/" + filename + ".png"));
			} catch (Exception e) {
				logger.warning(Constant.ERROR_SCREENSHOT.getValue() + driver);
			}

		}
	}
}
