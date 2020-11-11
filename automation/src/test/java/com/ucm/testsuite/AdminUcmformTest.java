package com.ucm.testsuite;

import org.testng.annotations.Test;

//import com.ucm.pageobjects.AdminLoginPage;
import com.ucm.config.BaseClass;
import com.ucm.dataproviders.DataProviderClass;
import com.ucm.pageobjects.AdminUcmFormCreationPage;


public class AdminUcmformTest extends BaseClass{

	@Test(dataProvider = "ucmformcreation", dataProviderClass = DataProviderClass.class)
	public void adminsubformcreation(String testdesc, String tn, String co, String gn, String ct1, String ct2) throws Exception {

		AdminUcmFormCreationPage ucmformpage = new AdminUcmFormCreationPage(driver);
		logger = extent.createTest(new Object() {}.getClass().getEnclosingMethod().getName());
	
		if (testdesc.equals("validdata")) {
			ucmformpage.ucmForm(tn,co,gn, ct1, ct2);

		} 
	}
}
