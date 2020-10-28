package com.ucm.testsuite;

import org.testng.AssertJUnit;
import org.testng.annotations.Test;

import com.ucm.config.BaseClass;
import com.ucm.dataproviders.DataProviderClass;
import com.ucm.pageobjects.FrontLoginPage;

public class FrontLoginTest extends BaseClass {

	@Test(dataProvider = "frontlogin", dataProviderClass = DataProviderClass.class)
	public void frontlogin(String testdesc, String fun, String fpw) throws Exception {

		FrontLoginPage flogin = new FrontLoginPage(driver);
		driver.get(properties.getProperty("url"));
		logger = extent.createTest(new Object() {
		}.getClass().getEnclosingMethod().getName());
		
		if (testdesc.equals("negative")) {
			flogin.invalidLogin(fun, fpw);
		}
		if (testdesc.equals("positive")) {
			flogin.validLogin(fun, fpw);
		}

	}

}
