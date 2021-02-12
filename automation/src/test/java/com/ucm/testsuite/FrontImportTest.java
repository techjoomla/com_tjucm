package com.ucm.testsuite;

import org.openqa.selenium.WebElement;
import org.testng.annotations.Test;

import com.ucm.config.BaseClass;
import com.ucm.dataproviders.DataProviderClass;
import com.ucm.pageobjects.FrontImportPage;

public class FrontImportTest extends BaseClass {

	@Test(dataProvider = "frontImport", dataProviderClass = DataProviderClass.class)
	public void frontImport(String testdesc, String fi) throws Exception {

		FrontImportPage fimport = new FrontImportPage(driver);
			fimport.importFlow(fi);		
			
		}

}
