package com.ucm.testsuite;
import org.testng.annotations.Test;
import com.ucm.config.BaseClass;
import com.ucm.dataproviders.DataProviderClass;
import com.ucm.pageobjects.AdminDateFieldPage;
public class AdminDateFieldTest extends BaseClass{
	@Test(dataProvider = "datefieldcreation", dataProviderClass = DataProviderClass.class)
	public void AdminDateField(String testdesc,String dl, String dn) throws Exception {
		AdminDateFieldPage datefield = new AdminDateFieldPage(driver);
		
		
		if (testdesc.equals("validdata")) {
			datefield.dateFieldCreation(dl,dn);
		}
	}
}