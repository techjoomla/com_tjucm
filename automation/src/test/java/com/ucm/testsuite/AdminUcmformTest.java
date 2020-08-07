package com.ucm.testsuite;

import org.testng.annotations.Test;

//import com.ucm.pageobjects.AdminLoginPage;
import com.ucm.config.BaseClass;
import com.ucm.dataproviders.DataProviderClass;
import com.ucm.pageobjects.AdminUcmFormCreationPage;


public class AdminUcmformTest extends BaseClass{

	@Test(dataProvider = "ucmformcreation", dataProviderClass = DataProviderClass.class)
	public void adminsubformcreation(String testdesc, String tn) throws Exception {

		AdminUcmFormCreationPage ucmformpage = new AdminUcmFormCreationPage(driver);
		driver.get(properties.getProperty("url") + properties.getProperty("admin"));
		logger = extent.createTest(new Object() {
		}.getClass().getEnclosingMethod().getName());
		if (testdesc.equals("validdata")) {
			ucmformpage.ucmForm(tn);

		} 
	}
}
