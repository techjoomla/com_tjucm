package com.ucm.testsuite;

import org.testng.annotations.Test;

//import com.ucm.pageobjects.AdminLoginPage;
import com.ucm.config.BaseClass;
import com.ucm.dataproviders.DataProviderClass;
import com.ucm.pageobjects.AdminSubFormCreationPage;

public class AdminCreateSubFormTest extends BaseClass {

	@Test(dataProvider = "subformcreation", dataProviderClass = DataProviderClass.class)
	public void adminsubformcreation(String testdesc, String tn, String ac, String gn, String fl, String fn) throws Exception {

		AdminSubFormCreationPage subformpage = new AdminSubFormCreationPage(driver);
		driver.get(properties.getProperty("url") + properties.getProperty("admin"));
		logger = extent.createTest(new Object() {
		}.getClass().getEnclosingMethod().getName());
		if (testdesc.equals("positive")) {
			subformpage.subform(tn, ac, gn, fl, fn);

		} 
	}
}
