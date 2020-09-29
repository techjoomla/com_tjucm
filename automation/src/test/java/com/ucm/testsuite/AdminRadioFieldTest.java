package com.ucm.testsuite;

import org.testng.annotations.Test;

import com.ucm.config.BaseClass;
import com.ucm.dataproviders.DataProviderClass;
import com.ucm.pageobjects.AdminRadioFieldPage;


public class AdminRadioFieldTest extends BaseClass{

	@Test(dataProvider = "radiobuttoncreation", dataProviderClass = DataProviderClass.class)
	public void adminsubformcreation(String testdesc) throws Exception {

		AdminRadioFieldPage radiobutton = new AdminRadioFieldPage(driver);
		driver.get(properties.getProperty("url") + properties.getProperty("admin"));
		logger = extent.createTest(new Object() {}.getClass().getEnclosingMethod().getName());
		if (testdesc.equals("validdata")) {
			radiobutton.radioButtonCreation();

		} 
	}
}
