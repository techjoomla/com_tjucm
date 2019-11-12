package config;

import java.util.concurrent.TimeUnit;

import org.openqa.selenium.WebDriver;
import org.openqa.selenium.chrome.ChromeDriver;
//import org.testng.annotations.AfterSuite;
import org.testng.annotations.BeforeSuite;

import Excel.Constant;

public class BasicClass{

	public WebDriver driver;

@BeforeSuite
public void setup(){

String setProperty = System.setProperty("webdriver.chrome.driver", Constant.ChromeDriver);

driver = new ChromeDriver();
// will add for diff browser also 
driver.manage().window().maximize();

driver.manage().timeouts().implicitlyWait(30, TimeUnit.SECONDS);

}

//@AfterSuite
//public void tearDown(){
//
//driver.quit();
//
//}

}
