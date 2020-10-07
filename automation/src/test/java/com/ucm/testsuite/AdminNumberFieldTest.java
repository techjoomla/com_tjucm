package com.ucm.testsuite;
import org.testng.annotations.Test;
import com.ucm.config.BaseClass;
import com.ucm.dataproviders.DataProviderClass;
import com.ucm.pageobjects.AdminNumberFieldPage;
public class AdminNumberFieldTest extends BaseClass{
	@Test(dataProvider = "numberfieldcreation", dataProviderClass = DataProviderClass.class)
	public void AdminNumberField(String testdesc,String nl, String nn, String mn) throws Exception {
		AdminNumberFieldPage numberfield = new AdminNumberFieldPage(driver);
		
		
		if (testdesc.equals("validdata")) {
			numberfield.numberFieldCreation(nl,nn,mn);
		}
	}
}