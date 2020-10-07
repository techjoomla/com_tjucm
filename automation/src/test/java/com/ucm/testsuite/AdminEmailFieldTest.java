package com.ucm.testsuite;
import org.testng.annotations.Test;
import com.ucm.config.BaseClass;
import com.ucm.dataproviders.DataProviderClass;
import com.ucm.pageobjects.AdminEmailFieldPage;
public class AdminEmailFieldTest extends BaseClass{
	@Test(dataProvider = "emailfieldcreation", dataProviderClass = DataProviderClass.class)
	public void AdminEmailField(String testdesc,String el, String dn) throws Exception {
		AdminEmailFieldPage emailfield = new AdminEmailFieldPage(driver);
		
		
		if (testdesc.equals("validdata")) {
			emailfield.emailFieldCreation(el,dn);
		}
	}
}